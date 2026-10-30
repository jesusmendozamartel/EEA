<?php

namespace App\Libraries;

use App\Models\GeneraModel;
use DOMDocument;
use DOMElement;
use DOMNode;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\StringHelper;
use RuntimeException;
use ZipArchive;

/**
 * Updates a template XLSX by streaming cell records and worksheet XML to disk.
 * This keeps PhpSpreadsheet from holding the complete high-volume workbook.
 */
class StreamingXlsxGenerator
{
    private const MAIN_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const PACKAGE_REL_NS = 'http://schemas.openxmlformats.org/package/2006/relationships';
    private const CONTENT_TYPES_NS = 'http://schemas.openxmlformats.org/package/2006/content-types';

    /**
     * @param array{anio: string, formato: string, dnivel: string} $params
     * @param array{activ?: mixed, descrip_activ?: mixed} $descripcionActividad
     * @return array<string, int>
     */
    public function generate(
        string $templatePath,
        string $outputPath,
        array $params,
        array $descripcionActividad,
        GeneraModel $model
    ): array {
        // El directorio temporal del sistema tuvo mejor rendimiento en la
        // generación completa. La limpieza es de mejor esfuerzo más abajo.
        $temporaryBase = sys_get_temp_dir();
        $temporaryRoot = tempnam($temporaryBase, 'eea_xml_');
        if ($temporaryRoot === false) {
            throw new RuntimeException('PHP no pudo crear un archivo temporal en ' . $temporaryBase . '.');
        }
        @unlink($temporaryRoot);
        if (!mkdir($temporaryRoot)) {
            throw new RuntimeException('No se pudo preparar el directorio temporal de generación.');
        }

        $sourceDirectory = $temporaryRoot . DIRECTORY_SEPARATOR . 'source';
        $spoolDirectory  = $temporaryRoot . DIRECTORY_SEPARATOR . 'spool';
        if (!mkdir($sourceDirectory) || !mkdir($spoolDirectory)) {
            $this->removeDirectory($temporaryRoot);
            throw new RuntimeException('No se pudieron preparar los archivos temporales del Excel.');
        }

        try {
            $templateFiles = $this->extractTemplate($templatePath, $sourceDirectory);
            $sheetParts = $templateFiles['sheets'];
            $rowMaps    = $this->getAccountRowMaps($templatePath);

            $consolidado = new StreamingXlsxCellSpool($spoolDirectory . DIRECTORY_SEPARATOR . 'Consolidado');
            $consolidado->append(2, 2, 's', $this->asString($descripcionActividad['activ'] ?? ''));
            $consolidado->append(2, 3, 's', $this->asString($descripcionActividad['descrip_activ'] ?? ''));
            $this->rewriteWorksheet(
                $this->sheetPath($sourceDirectory, $sheetParts, 'Consolidado'),
                $consolidado,
                0
            );
            unset($consolidado);
            log_message('info', 'Genera_SI: hoja Consolidado actualizada mediante XML incremental.');

            $counts = [];
            for ($modeNumber = 1; $modeNumber <= 7; $modeNumber++) {
                $mode  = 'M' . $modeNumber;
                $spool = new StreamingXlsxCellSpool($spoolDirectory . DIRECTORY_SEPARATOR . $mode);
                $companies = 0;
                $columnIndex = 11;
                $modeStarted = microtime(true);
                log_message('info', 'Genera_SI: iniciando lectura incremental de {hoja}.', [
                    'hoja' => $mode,
                ]);
                $rows = $model->getDataMPStream($params, $mode);
                log_message('info', 'Genera_SI: cursor de {hoja} abierto en {segundos}s.', [
                    'hoja'     => $mode,
                    'segundos' => number_format(microtime(true) - $modeStarted, 3, '.', ''),
                ]);

                foreach ($rows as $company) {
                    if ($columnIndex > 16384) {
                        throw new RuntimeException('La cantidad de empresas excede las columnas disponibles en Excel.');
                    }

                    $column = $columnIndex++;
                    $values = array_values($company);
                    $this->appendHeader($spool, $column, 1, $company['RUC'] ?? '');
                    $this->appendHeader($spool, $column, 2, $company['RAZSOCIAL'] ?? '');
                    $this->appendHeader($spool, $column, 3, $company['Nro_Estab'] ?? '000');
                    for ($offset = 4; $offset <= 8; $offset++) {
                        $this->appendHeader($spool, $column, $offset, $values[$offset] ?? '');
                    }
                    $this->appendHeader($spool, $column, 9, $company['Sector'] ?? '');
                    $spool->append(10, $column, 's', $this->asString('Año ' . $params['anio']));

                    foreach ($company as $field => $value) {
                        $code = trim((string) $field);
                        if (!isset($rowMaps[$mode][$code])) {
                            continue;
                        }

                        if ($value === null || $value === '') {
                            $spool->append($rowMaps[$mode][$code], $column, 'n', '0');
                        } elseif (is_numeric($value) || is_bool($value)) {
                            $spool->append($rowMaps[$mode][$code], $column, 'n', $this->asNumber((float) $value));
                        } else {
                            $spool->append($rowMaps[$mode][$code], $column, 's', $this->asString((string) $value));
                        }
                    }

                    $companies++;
                }

                $spool->append(11, 8, 'n', (string) $companies);
                $lastDataColumn = $companies > 0 ? 10 + $companies : 0;
                $this->rewriteWorksheet(
                    $this->sheetPath($sourceDirectory, $sheetParts, $mode),
                    $spool,
                    $lastDataColumn
                );
                $counts[$mode] = $companies;
                log_message('info', 'Genera_SI: hoja {hoja} actualizada con {registros} empresas en {segundos}s.', [
                    'hoja'      => $mode,
                    'registros' => (string) $companies,
                    'segundos'  => number_format(microtime(true) - $modeStarted, 3, '.', ''),
                ]);
                unset($spool);
            }

            $this->enableFormulaRecalculation($sourceDirectory);
            log_message('info', 'Genera_SI: iniciando empaquetado del XLSX único.');
            $this->zipFiles($sourceDirectory, $templateFiles['entries'], $outputPath);

            return $counts;
        } finally {
            $this->removeDirectory($temporaryRoot);
        }
    }

    /** @return array{sheets: array<string, string>, entries: list<string>} */
    private function extractTemplate(string $templatePath, string $sourceDirectory): array
    {
        $archive = new ZipArchive();
        if ($archive->open($templatePath) !== true) {
            throw new RuntimeException('No se pudo abrir el archivo XLSX de plantilla.');
        }

        try {
            $workbookXml = $archive->getFromName('xl/workbook.xml');
            $relationshipsXml = $archive->getFromName('xl/_rels/workbook.xml.rels');
            if ($workbookXml === false || $relationshipsXml === false || !$archive->extractTo($sourceDirectory)) {
                throw new RuntimeException('La plantilla XLSX no contiene sus archivos internos esperados.');
            }
            $entries = [];
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $entryName = $archive->getNameIndex($index);
                if (is_string($entryName) && !str_ends_with($entryName, '/')) {
                    $entries[] = $entryName;
                }
            }
        } finally {
            $archive->close();
        }

        $workbook = $this->loadXml($workbookXml);
        $relationships = $this->loadXml($relationshipsXml);
        $xpath = new \DOMXPath($workbook);
        $xpath->registerNamespace('m', self::MAIN_NS);
        $xpath->registerNamespace('r', self::REL_NS);
        $relationshipNodes = $relationships->getElementsByTagNameNS(self::PACKAGE_REL_NS, 'Relationship');
        $targets = [];
        foreach ($relationshipNodes as $relationship) {
            if ($relationship instanceof DOMElement) {
                $targets[$relationship->getAttribute('Id')] = $relationship->getAttribute('Target');
            }
        }

        $parts = [];
        foreach ($xpath->query('/m:workbook/m:sheets/m:sheet') ?: [] as $sheet) {
            if (!$sheet instanceof DOMElement) {
                continue;
            }
            $id = $sheet->getAttributeNS(self::REL_NS, 'id');
            if (!isset($targets[$id])) {
                continue;
            }

            $target = str_replace('\\', '/', $targets[$id]);
            $parts[$sheet->getAttribute('name')] = str_starts_with($target, '/')
                ? ltrim($target, '/')
                : 'xl/' . ltrim($target, '/');
        }

        return [
            'sheets'  => $parts,
            'entries' => $entries,
        ];
    }

    /** @return array<string, array<string, int>> */
    private function getAccountRowMaps(string $templatePath): array
    {
        $spreadsheet = (new Xlsx())->load($templatePath);
        $maps = [];
        try {
            for ($modeNumber = 1; $modeNumber <= 5; $modeNumber++) {
                $mode = 'M' . $modeNumber;
                $sheet = $spreadsheet->getSheetByName($mode);
                if ($sheet === null) {
                    throw new RuntimeException('No existe la hoja ' . $mode . ' en la plantilla.');
                }

                $map = [];
                for ($row = 11, $lastRow = $sheet->getHighestRow(); $row <= $lastRow; $row++) {
                    $code = trim((string) $sheet->getCell('G' . $row)->getValue());
                    if ($code !== '') {
                        $map[$code] = $row;
                    }
                }
                $maps[$mode] = $map;
            }
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        return $maps;
    }

    private function sheetPath(string $sourceDirectory, array $sheetParts, string $sheetName): string
    {
        if (!isset($sheetParts[$sheetName])) {
            throw new RuntimeException('No existe la hoja ' . $sheetName . ' en la plantilla.');
        }

        $path = $sourceDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sheetParts[$sheetName]);
        if (!is_file($path)) {
            throw new RuntimeException('No se encontró el XML de la hoja ' . $sheetName . '.');
        }

        return $path;
    }

    private function rewriteWorksheet(string $path, StreamingXlsxCellSpool $spool, int $lastDataColumn): void
    {
        $xml = file_get_contents($path);
        if ($xml === false) {
            throw new RuntimeException('No se pudo leer el XML de una hoja Excel.');
        }

        $document = $this->loadXml($xml);
        $sheetData = $document->getElementsByTagNameNS(self::MAIN_NS, 'sheetData')->item(0);
        if ($sheetData === null) {
            throw new RuntimeException('La hoja Excel no contiene sheetData.');
        }

        $templateRows = [];
        $maxRow = 1;
        foreach ($sheetData->childNodes as $child) {
            if ($child instanceof DOMElement && $child->localName === 'row') {
                $rowNumber = (int) $child->getAttribute('r');
                $templateRows[$rowNumber] = $child;
                $maxRow = max($maxRow, $rowNumber);
            }
        }

        $maxRow = max($maxRow, 11);
        $maxColumn = max($lastDataColumn, $this->getDimensionMaxColumn($document));
        $maxColumnName = Coordinate::stringFromColumnIndex(max(1, $maxColumn));

        if (!preg_match('/<sheetData\b[^>]*>/i', $xml, $openingMatch, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException('No se encontró el inicio de sheetData en una hoja Excel.');
        }
        $openingTag = $openingMatch[0][0];
        $openingOffset = $openingMatch[0][1];
        $closingOffset = strpos($xml, '</sheetData>', $openingOffset + strlen($openingTag));
        if ($closingOffset === false) {
            throw new RuntimeException('No se encontró el cierre de sheetData en una hoja Excel.');
        }

        $prefix = substr($xml, 0, $openingOffset);
        $suffix = substr($xml, $closingOffset + strlen('</sheetData>'));
        $prefix = preg_replace_callback(
            '/(<dimension\b[^>]*\bref=")[^"]+("[^>]*>)/i',
            static fn (array $match): string => $match[1] . 'A1:' . $maxColumnName . $maxRow . $match[2],
            $prefix,
            1
        ) ?? $prefix;

        $temporaryPath = $path . '.new';
        $output = fopen($temporaryPath, 'wb');
        if ($output === false) {
            throw new RuntimeException('No se pudo crear el XML temporal de una hoja.');
        }

        try {
            $this->writeAll($output, $prefix . $openingTag);
            for ($rowNumber = 1; $rowNumber <= $maxRow; $rowNumber++) {
                $generatedCells = $spool->readRow($rowNumber);
                $templateRow = $templateRows[$rowNumber] ?? null;
                if ($generatedCells === [] && $templateRow === null) {
                    continue;
                }

                $rowXml = $this->buildRowXml($document, $templateRow, $generatedCells, $rowNumber);
                $this->writeAll($output, $rowXml);
            }
            $this->writeAll($output, '</sheetData>' . $suffix);
        } finally {
            fclose($output);
        }

        if (!unlink($path) || !rename($temporaryPath, $path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('No se pudo actualizar el XML de una hoja.');
        }
    }

    private function buildRowXml(DOMDocument $sourceDocument, ?DOMElement $templateRow, array $generatedCells, int $rowNumber): string
    {
        $row = $templateRow !== null
            ? $templateRow->cloneNode(true)
            : $sourceDocument->createElementNS(self::MAIN_NS, 'row');
        if (!$row instanceof DOMElement) {
            throw new RuntimeException('No se pudo preparar una fila del Excel.');
        }
        $row->setAttribute('r', (string) $rowNumber);
        $row->removeAttribute('spans');

        $existingCells = [];
        $otherChildren = [];
        $children = [];
        foreach ($row->childNodes as $child) {
            $children[] = $child;
        }
        foreach ($children as $child) {
            if ($child instanceof DOMElement && $child->localName === 'c') {
                $reference = $child->getAttribute('r');
                if (preg_match('/^([A-Z]+)/', $reference, $matches)) {
                    $column = Coordinate::columnIndexFromString($matches[1]);
                    $existingCells[$column] = $child;
                }
            } else {
                $otherChildren[] = $child;
            }
            $row->removeChild($child);
        }

        $columns = array_unique(array_merge(array_keys($existingCells), array_keys($generatedCells)));
        sort($columns, SORT_NUMERIC);
        foreach ($columns as $column) {
            if (isset($generatedCells[$column])) {
                $cell = $existingCells[$column] ?? $sourceDocument->createElementNS(self::MAIN_NS, 'c');
                $this->replaceCellValue(
                    $sourceDocument,
                    $cell,
                    Coordinate::stringFromColumnIndex((int) $column) . $rowNumber,
                    $generatedCells[$column]['type'],
                    $generatedCells[$column]['value']
                );
                $row->appendChild($cell);
            } elseif (isset($existingCells[$column])) {
                $row->appendChild($existingCells[$column]);
            }
        }

        foreach ($otherChildren as $child) {
            $row->appendChild($child);
        }

        $rowDocument = new DOMDocument('1.0', 'UTF-8');
        $rowDocument->appendChild($rowDocument->importNode($row, true));
        $serialized = $rowDocument->saveXML($rowDocument->documentElement);
        if ($serialized === false) {
            throw new RuntimeException('No se pudo serializar una fila del Excel.');
        }

        return $serialized;
    }

    private function replaceCellValue(DOMDocument $document, DOMElement $cell, string $reference, string $type, string $value): void
    {
        while ($cell->firstChild !== null) {
            $cell->removeChild($cell->firstChild);
        }
        $cell->setAttribute('r', $reference);

        if ($type === 'n' || $type === 'b') {
            $cell->setAttribute('t', $type);
            $valueElement = $document->createElementNS(self::MAIN_NS, 'v');
            $valueElement->appendChild($document->createTextNode($value));
            $cell->appendChild($valueElement);

            return;
        }

        $cell->setAttribute('t', 'inlineStr');
        $inline = $document->createElementNS(self::MAIN_NS, 'is');
        $text = $document->createElementNS(self::MAIN_NS, 't');
        $value = StringHelper::controlCharacterPHP2OOXML(StringHelper::sanitizeUTF8($value));
        if ($value !== trim($value)) {
            $text->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }
        $text->appendChild($document->createTextNode($value));
        $inline->appendChild($text);
        $cell->appendChild($inline);
    }

    private function appendHeader(StreamingXlsxCellSpool $spool, int $column, int $row, mixed $value): void
    {
        $type = DefaultValueBinder::dataTypeForValue($value);
        if ($type === DataType::TYPE_NUMERIC) {
            $spool->append($row, $column, 'n', $this->asNumber((float) $value));
        } elseif ($type === DataType::TYPE_BOOL) {
            $spool->append($row, $column, 'b', $value ? '1' : '0');
        } else {
            $spool->append($row, $column, 's', $this->asString($value));
        }
    }

    private function asString(mixed $value): string
    {
        return DataType::checkString(StringHelper::convertToString($value, false));
    }

    private function asNumber(float $value): string
    {
        $number = json_encode($value, JSON_PRESERVE_ZERO_FRACTION);
        if ($number === false || !is_finite($value)) {
            throw new RuntimeException('Se encontró un valor numérico no válido para Excel.');
        }

        return $number;
    }

    private function getDimensionMaxColumn(DOMDocument $document): int
    {
        $dimension = $document->getElementsByTagNameNS(self::MAIN_NS, 'dimension')->item(0);
        if ($dimension instanceof DOMElement && preg_match('/(?:^|:)\$?([A-Z]+)\$?\d+$/', $dimension->getAttribute('ref'), $matches)) {
            return Coordinate::columnIndexFromString($matches[1]);
        }

        return 1;
    }

    private function enableFormulaRecalculation(string $sourceDirectory): void
    {
        $workbookPath = $sourceDirectory . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'workbook.xml';
        $document = $this->loadXmlFile($workbookPath);
        $calc = $document->getElementsByTagNameNS(self::MAIN_NS, 'calcPr')->item(0);
        if ($calc instanceof DOMElement) {
            $calc->setAttribute('calcMode', 'auto');
            $calc->setAttribute('fullCalcOnLoad', '1');
            $calc->setAttribute('forceFullCalc', '1');
        }
        $this->saveXmlFile($document, $workbookPath);

        $relationshipsPath = $sourceDirectory . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . '_rels' . DIRECTORY_SEPARATOR . 'workbook.xml.rels';
        $relationships = $this->loadXmlFile($relationshipsPath);
        foreach ($relationships->getElementsByTagNameNS(self::PACKAGE_REL_NS, 'Relationship') as $relationship) {
            if ($relationship instanceof DOMElement && str_ends_with($relationship->getAttribute('Type'), '/calcChain')) {
                $relationship->parentNode?->removeChild($relationship);
            }
        }
        $this->saveXmlFile($relationships, $relationshipsPath);

        $contentTypesPath = $sourceDirectory . DIRECTORY_SEPARATOR . '[Content_Types].xml';
        $contentTypes = $this->loadXmlFile($contentTypesPath);
        foreach ($contentTypes->getElementsByTagNameNS(self::CONTENT_TYPES_NS, 'Override') as $override) {
            if ($override instanceof DOMElement && $override->getAttribute('PartName') === '/xl/calcChain.xml') {
                $override->parentNode?->removeChild($override);
            }
        }
        $this->saveXmlFile($contentTypes, $contentTypesPath);

        $calcChainPath = $sourceDirectory . DIRECTORY_SEPARATOR . 'xl' . DIRECTORY_SEPARATOR . 'calcChain.xml';
        if (is_file($calcChainPath)) {
            unlink($calcChainPath);
        }
    }

    private function zipFiles(string $sourceDirectory, array $entries, string $outputPath): void
    {
        $archive = new ZipArchive();
        if ($archive->open($outputPath, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo XLSX final.');
        }

        try {
            foreach ($entries as $relativePath) {
                $filePath = $sourceDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                if (!is_file($filePath)) {
                    continue;
                }
                if (!$archive->addFile($filePath, $relativePath)) {
                    throw new RuntimeException('No se pudo agregar un componente al XLSX final.');
                }
                $archive->setCompressionName($relativePath, ZipArchive::CM_DEFLATE);
            }
        } finally {
            $archive->close();
        }
    }

    private function loadXml(string $xml): DOMDocument
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;
        if (!$document->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT)) {
            throw new RuntimeException('No se pudo interpretar el XML interno de la plantilla.');
        }

        return $document;
    }

    private function loadXmlFile(string $path): DOMDocument
    {
        $xml = file_get_contents($path);
        if ($xml === false) {
            throw new RuntimeException('No se pudo leer un componente XML del XLSX.');
        }

        return $this->loadXml($xml);
    }

    private function saveXmlFile(DOMDocument $document, string $path): void
    {
        $xml = $document->saveXML();
        if ($xml === false || file_put_contents($path, $xml) === false) {
            throw new RuntimeException('No se pudo guardar un componente XML del XLSX.');
        }
    }

    private function writeAll($handle, string $data): void
    {
        $written = 0;
        $length = strlen($data);
        while ($written < $length) {
            $bytes = fwrite($handle, substr($data, $written));
            if ($bytes === false || $bytes === 0) {
                throw new RuntimeException('No se pudo escribir el XLSX temporal.');
            }
            $written += $bytes;
        }
    }

    private function removeDirectory(string $directory): void
    {
        try {
            if (!is_dir($directory)) {
                return;
            }

            $entries = scandir($directory);
            if ($entries === false) {
                log_message('warning', 'Genera_SI: no se pudo listar el directorio temporal {directorio}; se conserva el XLSX generado.', [
                    'directorio' => $directory,
                ]);
                return;
            }

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $path = $directory . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($path) && !is_link($path)) {
                    $this->removeDirectory($path);
                } else {
                    @unlink($path);
                }
            }
            @rmdir($directory);
        } catch (\Throwable $e) {
            log_message('warning', 'Genera_SI: no se pudo limpiar el directorio temporal {directorio}: {mensaje}', [
                'directorio' => $directory,
                'mensaje'    => $e->getMessage(),
            ]);
        }
    }
}

/** Stores row values in append-only per-row files, avoiding a workbook-sized array. */
class StreamingXlsxCellSpool
{
    /** @var array<int, resource> */
    private array $handles = [];

    public function __construct(private readonly string $directory)
    {
        if (!mkdir($this->directory, 0700)) {
            throw new RuntimeException('No se pudo crear el almacenamiento temporal de las celdas.');
        }
    }

    public function __destruct()
    {
        foreach ($this->handles as $handle) {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }

    public function append(int $row, int $column, string $type, string $value): void
    {
        if (!isset($this->handles[$row])) {
            $path = $this->directory . DIRECTORY_SEPARATOR . $row . '.bin';
            $handle = fopen($path, 'ab');
            if ($handle === false) {
                throw new RuntimeException('No se pudo abrir el almacenamiento temporal de una fila.');
            }
            $this->handles[$row] = $handle;
        }

        $typeCode = $type === 'n' ? 0 : ($type === 'b' ? 2 : 1);
        $record = pack('N', $column) . chr($typeCode) . pack('N', strlen($value)) . $value;
        $written = 0;
        while ($written < strlen($record)) {
            $bytes = fwrite($this->handles[$row], substr($record, $written));
            if ($bytes === false || $bytes === 0) {
                throw new RuntimeException('No se pudo guardar una celda temporalmente.');
            }
            $written += $bytes;
        }
    }

    /** @return array<int, array{type: string, value: string}> */
    public function readRow(int $row): array
    {
        if (isset($this->handles[$row])) {
            fclose($this->handles[$row]);
            unset($this->handles[$row]);
        }

        $path = $this->directory . DIRECTORY_SEPARATOR . $row . '.bin';
        if (!is_file($path)) {
            return [];
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('No se pudo leer una fila temporal del Excel.');
        }

        $cells = [];
        try {
            while (!feof($handle)) {
                $header = $this->readBytes($handle, 9);
                if ($header === '') {
                    break;
                }
                if (strlen($header) !== 9) {
                    throw new RuntimeException('Se encontró una fila temporal incompleta.');
                }

                $column = unpack('Nvalue', substr($header, 0, 4))['value'];
                $typeCode = ord($header[4]);
                $length = unpack('Nvalue', substr($header, 5, 4))['value'];
                $value = $this->readBytes($handle, $length);
                if (strlen($value) !== $length) {
                    throw new RuntimeException('Se encontró un valor temporal incompleto.');
                }

                $cells[$column] = [
                    'type'  => $typeCode === 0 ? 'n' : ($typeCode === 2 ? 'b' : 's'),
                    'value' => $value,
                ];
            }
        } finally {
            fclose($handle);
        }

        return $cells;
    }

    private function readBytes($handle, int $length): string
    {
        $data = '';
        while (strlen($data) < $length && !feof($handle)) {
            $chunk = fread($handle, $length - strlen($data));
            if ($chunk === false || $chunk === '') {
                break;
            }
            $data .= $chunk;
        }

        return $data;
    }
}

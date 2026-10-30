<?php

namespace App\Controllers;

use App\Models\GeneraModel;
use App\Libraries\StreamingXlsxGenerator;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class Genera extends BaseController
{
    protected $Genera_model;

    public function __construct()
    {
        $this->Genera_model = new GeneraModel();
        helper('date');
    }

    // ============================================================
    // DEMO
    // ============================================================

    public function Demo() {

        $params['anio']='2019';
        $params['formato']='G';
        $params['dnivel']='038001';

        $result = $this->Genera_model->getDataMP($params, 'M1');

        print_r($result);
    } 


    // ============================================================
    // CARGA DE VISTA PRINCIPAL
    // ============================================================

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        } 
        
        
        $config = [
            'data' => [
                'titulo'            => 'Sistema Intermedio EEA',
                'subtitulo'         => 'Generación de plantillas EEA',
                'titulo_search'     => 'Generación y exportación de plantillas EEA',
                'titulo_resultado'  => 'Resultado de la Búsqueda'
            ],
            'js' => [
                'pages/si/si.js'
            ],
            'template' => 'template1'
        ];

        return view('main/cuentas/cuentas_view', $config);
    }


    // ============================================================
    // GENERA SISTEMA INTERMEDIO POR TIPO DE PLANTILLA
    // ============================================================

    public function Genera_SI()
    {
        $inicioGeneracion = microtime(true);

        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(401)->setBody('La sesión ha expirado. Inicie sesión nuevamente.');
        }

        // ============================================================
        // 1. OBTENER Y VALIDAR PARÁMETROS DEL POST
        // ============================================================
        $params = $this->request->getPost();

        if (
            !isset($params['anio'], $params['formato'], $params['plantilla'], $params['dnivel']) ||
            !is_scalar($params['anio']) ||
            !is_scalar($params['formato']) ||
            !is_scalar($params['plantilla']) ||
            !is_scalar($params['dnivel']) ||
            !preg_match('/^\d{4}$/', (string) $params['anio']) ||
            !in_array((string) $params['formato'], ['G', 'P'], true) ||
            !in_array((string) $params['plantilla'], ['1', '2', '3'], true) ||
            trim((string) $params['dnivel']) === ''
        ) {
            return $this->response
                ->setStatusCode(400)
                ->setBody('Parámetros incompletos para generar el archivo.');
        }

        $params['anio'] = (string) $params['anio'];
        $anio      = $params['anio'];
        $formato   = (string) $params['formato'];
        $plantilla = (string) $params['plantilla'];

        $excel = null;

        // ============================================================
        // PLANTILLA 1: MODOS DE PRODUCCIÓN (M1 - M7)
        // ============================================================
        if ($plantilla == 1) {

            try {
                $descripcionActividad = $this->Genera_model->getDescripcionActividad(
                    1,
                    (string) $params['dnivel']
                );
            } catch (\Throwable $e) {
                log_message('error', 'Genera_SI: no se pudo obtener la descripción de la actividad {dnivel}; excepción {tipo}, código {codigo}: {mensaje}', [
                    'dnivel'  => (string) $params['dnivel'],
                    'tipo'    => get_class($e),
                    'codigo'  => (string) $e->getCode(),
                    'mensaje' => $e->getMessage(),
                ]);

                return $this->response
                    ->setStatusCode(500)
                    ->setBody('No se pudo obtener la descripción de la actividad seleccionada.');
            }

            if ($descripcionActividad === null) {
                return $this->response
                    ->setStatusCode(404)
                    ->setBody('No se encontró la descripción de la actividad seleccionada.');
            }

            $nombrePlantilla = ($anio === '2021' && in_array($formato, ['G', 'P'], true))
                ? 'V_CENEC_2021_' . $formato . '_Plantilla_AE_N104.xlsx'
                : 'EEA_' . $anio . '_' . $formato . '_Plantilla_AE_N104.xlsx';
            $filename        = FCPATH . 'Templates/' . $nombrePlantilla;

            if (!is_file($filename) || !is_readable($filename)) {
                return $this->response
                    ->setStatusCode(404)
                    ->setBody('No se encontró la plantilla solicitada.');
            }

            if ((string) $params['dnivel'] === '038001') {
                $dnivelNombre = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $params['dnivel']);
                $nombre = ($anio === '2021' && in_array($formato, ['G', 'P'], true))
                    ? 'V_CENEC_2021_' . $formato . '_Plantilla_AE_N104_' . $dnivelNombre . '.xlsx'
                    : 'EEA_' . $anio . '_' . $formato . '_Plantilla_AE_N104_' . $dnivelNombre . '.xlsx';
                $archivoTemporal = tempnam(sys_get_temp_dir(), 'eea_xlsx_');
                if ($archivoTemporal === false) {
                    return $this->response
                        ->setStatusCode(500)
                        ->setBody('No se pudo preparar el archivo Excel.');
                }

                try {
                    $inicioStreaming = microtime(true);
                    $conteos = (new StreamingXlsxGenerator())->generate(
                        $filename,
                        $archivoTemporal,
                        $params,
                        $descripcionActividad,
                        $this->Genera_model
                    );

                    if (array_sum($conteos) === 0) {
                        @unlink($archivoTemporal);

                        return $this->response
                            ->setStatusCode(404)
                            ->setBody('No se encontró información');
                    }

                    log_message('info', 'Genera_SI: XLSX único para {dnivel} generado en {segundos}s; salida {bytes} bytes; empresas {conteos}.', [
                        'dnivel'   => (string) $params['dnivel'],
                        'segundos' => number_format(microtime(true) - $inicioStreaming, 3, '.', ''),
                        'bytes'    => (string) filesize($archivoTemporal),
                        'conteos'  => json_encode($conteos),
                    ]);

                    $respuesta = $this->response
                        ->download($archivoTemporal, null)
                        ->setFileName($nombre);
                    $respuesta->setHeader(
                        'X-Tiempo-Generacion',
                        number_format(microtime(true) - $inicioGeneracion, 2, '.', '')
                    );
                    register_shutdown_function(static function () use ($archivoTemporal): void {
                        if (is_file($archivoTemporal)) {
                            @unlink($archivoTemporal);
                        }
                    });

                    return $respuesta;
                } catch (\Throwable $e) {
                    log_message('error', 'Genera_SI: error en exportación incremental de {dnivel}; excepción {tipo}, código {codigo}: {mensaje}', [
                        'dnivel'  => (string) $params['dnivel'],
                        'tipo'    => get_class($e),
                        'codigo'  => (string) $e->getCode(),
                        'mensaje' => $e->getMessage(),
                    ]);
                    if (is_file($archivoTemporal)) {
                        @unlink($archivoTemporal);
                    }

                    return $this->response
                        ->setStatusCode(500)
                        ->setBody('No se pudo generar el archivo Excel.');
                }
            }

            try {
                $reader = new Xlsx();
                $inicioCargaPlantilla = microtime(true);
                $excel  = $reader->load($filename);
                log_message('info', 'Genera_SI: carga plantilla {anio} en {segundos}s.', [
                    'anio'     => $anio,
                    'segundos' => number_format(microtime(true) - $inicioCargaPlantilla, 3, '.', ''),
                ]);
            } catch (\Throwable $e) {
                log_message('error', 'Genera_SI: no se pudo cargar la plantilla {archivo}; excepción {tipo}, código {codigo}: {mensaje}', [
                    'archivo' => $filename,
                    'tipo'    => get_class($e),
                    'codigo'  => (string) $e->getCode(),
                    'mensaje' => $e->getMessage(),
                ]);

                return $this->response
                    ->setStatusCode(500)
                    ->setBody('No se pudo abrir la plantilla Excel.');
            }

            $sheetConsolidado = $excel->getSheetByName('Consolidado');
            if (!$sheetConsolidado) {
                $excel->disconnectWorksheets();
                unset($excel);

                return $this->response
                    ->setStatusCode(500)
                    ->setBody('No existe la hoja Consolidado en la plantilla.');
            }

            $sheetConsolidado->setCellValue('B2', $descripcionActividad['activ'] ?? '');
            $sheetConsolidado->setCellValue('C2', $descripcionActividad['descrip_activ'] ?? '');

            // ========================================================
            // PROCESAR HOJAS M1 A M5
            // ========================================================
            $totalEmpresas = 0;
            for ($i = 1; $i <= 7; $i++) {

                $nombreHoja = 'M' . $i;
                $sheet      = $excel->getSheetByName($nombreHoja);

                if (!$sheet) {
                    $excel->disconnectWorksheets();
                    unset($excel);

                    return $this->response
                        ->setStatusCode(500)
                        ->setBody('No existe la hoja ' . $nombreHoja . ' en la plantilla.');
                }

                // ====================================================
                // A. INDEXAR CÓDIGOS DE COLUMNA G A SUS FILAS DE EXCEL
                // ====================================================
                $mapaFilas  = [];
                $highestRow = $sheet->getHighestRow();

                for ($row = 11; $row <= $highestRow; $row++) {
                    $codigoG = trim((string)$sheet->getCell('G' . $row)->getValue());
                    if ($codigoG !== '') {
                        $mapaFilas[$codigoG] = $row;
                    }
                }

                // ====================================================
                // B. OBTENER LOS DATOS Y PINTAR CADA EMPRESA
                // ====================================================
                $colIndex = 11; // Columna K (11 en índice numerado de Excel)
                $inicioHoja = microtime(true);
                try {
                    $inicioConsulta = microtime(true);
                    log_message('info', 'Genera_SI: iniciando consulta de datos para {hoja}.', [
                        'hoja' => $nombreHoja,
                    ]);

                    $empresas = $this->Genera_model->getDataMP($params, $nombreHoja);
                    $segundosConsulta = microtime(true) - $inicioConsulta;
                    $totalRegistros = count($empresas);
                    $totalEmpresas += $totalRegistros;
                    log_message('info', 'Genera_SI: consulta de {hoja} completada; {registros} empresas en {segundos}s.', [
                        'hoja'      => $nombreHoja,
                        'registros' => (string) $totalRegistros,
                        'segundos'  => number_format($segundosConsulta, 3, '.', ''),
                    ]);

                    $inicioPintado = microtime(true);
                    foreach ($empresas as $empresaArray) {
                        if ($colIndex > 16384) {
                            throw new \RuntimeException('La cantidad de empresas excede las columnas disponibles en Excel.');
                        }

                        $colEmpresa = $colIndex++;
                        $values     = array_values($empresaArray);

                        // Encabezados de la empresa (filas 1 a 10).
                        $sheet->getCell([$colEmpresa, 1])->setValue($empresaArray['RUC'] ?? '');
                        $sheet->getCell([$colEmpresa, 2])->setValue($empresaArray['RAZSOCIAL'] ?? '');
                        $sheet->getCell([$colEmpresa, 3])->setValue($empresaArray['Nro_Estab'] ?? '000');
                        $sheet->getCell([$colEmpresa, 4])->setValue($values[4] ?? '');
                        $sheet->getCell([$colEmpresa, 5])->setValue($values[5] ?? '');
                        $sheet->getCell([$colEmpresa, 6])->setValue($values[6] ?? '');
                        $sheet->getCell([$colEmpresa, 7])->setValue($values[7] ?? '');
                        $sheet->getCell([$colEmpresa, 8])->setValue($values[8] ?? '');
                        $sheet->getCell([$colEmpresa, 9])->setValue($empresaArray['Sector'] ?? '');
                        $sheet->getCell([$colEmpresa, 10])->setValueExplicit('Año ' . $anio, DataType::TYPE_STRING);

                        // Copiar los valores cuyas cuentas existen en la plantilla.
                        foreach ($empresaArray as $campo => $valor) {
                            $codigoCampo = trim((string) $campo);

                            if (!isset($mapaFilas[$codigoCampo])) {
                                continue;
                            }

                            $celda = [$colEmpresa, $mapaFilas[$codigoCampo]];
                            if ($valor === null || $valor === '') {
                                $sheet->getCell($celda)->setValueExplicit(0, DataType::TYPE_NUMERIC);
                            } elseif (is_numeric($valor) || is_bool($valor)) {
                                $sheet->getCell($celda)->setValueExplicit((float) $valor, DataType::TYPE_NUMERIC);
                            } else {
                                $sheet->getCell($celda)->setValueExplicit((string) $valor, DataType::TYPE_STRING);
                            }
                        }
                    }

                    log_message('info', 'Genera_SI: escritura de celdas en {hoja} completada en {segundos}s.', [
                        'hoja'     => $nombreHoja,
                        'segundos' => number_format(microtime(true) - $inicioPintado, 3, '.', ''),
                    ]);
                } catch (\Throwable $e) {
                    log_message('error', 'Genera_SI: fallo en {hoja} después de {procesadas} empresas; excepción {tipo}, código {codigo}: {mensaje}', [
                        'hoja'       => $nombreHoja,
                        'tipo'       => get_class($e),
                        'codigo'     => (string) $e->getCode(),
                        'mensaje'    => $e->getMessage(),
                        'procesadas' => (string) max(0, $colIndex - 11),
                    ]);

                    $excel->disconnectWorksheets();
                    unset($excel);

                    return $this->response
                        ->setStatusCode(500)
                        ->setBody('No se pudo procesar la hoja ' . $nombreHoja . '.');
                }

                log_message('info', 'Genera_SI: hoja {hoja}, {registros} empresas, total {segundos}s.', [
                    'hoja'      => $nombreHoja,
                    'registros' => (string) $totalRegistros,
                    'segundos'  => number_format(microtime(true) - $inicioHoja, 3, '.', ''),
                ]);

                // Registrar la cantidad de empresas después de recorrer las filas.
                $sheet->setCellValue('H11', $totalRegistros);
            }

            if ($totalEmpresas === 0) {
                $excel->disconnectWorksheets();
                unset($excel);

                return $this->response
                    ->setStatusCode(404)
                    ->setBody('No se encontró información');
            }

        // ============================================================
        // PLANTILLA 2
        // ============================================================
        } else {
            return $this->response
                ->setStatusCode(501)
                ->setBody('La plantilla seleccionada todavía no está implementada.');
        }

        if ($excel === null) {
            return $this->response
                ->setStatusCode(500)
                ->setBody('No se pudo cargar la plantilla Excel.');
        }

        // ============================================================
        // DESCARGA Y SALIDA DEL ARCHIVO EXCEL
        // ============================================================
        $dnivelNombre = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $params['dnivel']);
        $nombre       = ($anio === '2021' && in_array($formato, ['G', 'P'], true))
            ? 'V_CENEC_2021_' . $formato . '_Plantilla_AE_N104_' . $dnivelNombre . '.xlsx'
            : 'EEA_' . $anio . '_' . $formato . '_Plantilla_AE_N104_' . $dnivelNombre . '.xlsx';
        $archivoTemporal = null;

        try {
            $archivoTemporal = tempnam(sys_get_temp_dir(), 'eea_');
            if ($archivoTemporal === false) {
                throw new \RuntimeException('No se pudo crear el archivo temporal para el Excel.');
            }

            $writer = new XlsxWriter($excel);
            $writer->setPreCalculateFormulas(false);

            $inicioGuardado = microtime(true);
            log_message('info', 'Genera_SI: iniciando guardado XLSX {anio}; memoria usada {memoriaUsada} bytes, reservada {memoriaReservada} bytes, pico {pico} bytes.', [
                'anio'              => $anio,
                'memoriaUsada'      => (string) memory_get_usage(false),
                'memoriaReservada'  => (string) memory_get_usage(true),
                'pico'              => (string) memory_get_peak_usage(true),
            ]);
            $writer->save($archivoTemporal);
            log_message('info', 'Genera_SI: guardado XLSX {anio} en {segundos}s; salida {bytes} bytes; memoria usada {memoriaUsada} bytes, reservada {memoriaReservada} bytes, pico {pico} bytes.', [
                'anio'             => $anio,
                'segundos'         => number_format(microtime(true) - $inicioGuardado, 3, '.', ''),
                'bytes'            => (string) filesize($archivoTemporal),
                'memoriaUsada'     => (string) memory_get_usage(false),
                'memoriaReservada' => (string) memory_get_usage(true),
                'pico'             => (string) memory_get_peak_usage(true),
            ]);

            $excel->disconnectWorksheets();
            unset($excel);
            unset($writer);

            $respuesta = $this->response
                ->download($archivoTemporal, null)
                ->setFileName($nombre);
            $respuesta->setHeader(
                'X-Tiempo-Generacion',
                number_format(microtime(true) - $inicioGeneracion, 2, '.', '')
            );

            register_shutdown_function(static function () use ($archivoTemporal): void {
                if (is_file($archivoTemporal)) {
                    @unlink($archivoTemporal);
                }
            });

            return $respuesta;

        } catch (\Throwable $e) {
            log_message('error', 'Genera_SI: error al guardar el archivo {archivo}; excepción {tipo}, código {codigo}: {mensaje}', [
                'archivo' => $nombre,
                'tipo'    => get_class($e),
                'codigo'  => (string) $e->getCode(),
                'mensaje' => $e->getMessage(),
            ]);

            if ($excel !== null) {
                $excel->disconnectWorksheets();
                unset($excel);
            }

            return $this->response
                ->setStatusCode(500)
                ->setBody('Ocurrió un error al generar el archivo Excel.');
        }
    }



}

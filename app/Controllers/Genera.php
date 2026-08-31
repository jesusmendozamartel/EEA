<?php

namespace App\Controllers;

use App\Models\GeneraModel;
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

    public function Demo()
    {
        $result = $this->Genera_model->Genera_Sectores('2019');

        return $this->response->setJSON($result);
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
    // GENERA SISTEMA INTERMEDIO
    // ============================================================

    public function Genera_SI()
    {
        // ===================== PARÁMETROS =====================

        $parametros = $this->request->getPost();

        $param = $this->Genera_model->getParam($parametros['anio']);
        
        $data  = $this->Genera_model->getData($param);

        echo print_r($data);
        exit();

        // ===================== PLANTILLA =====================

        $filename = FCPATH . 'Templates/Plantilla_ET.xlsx';

        if (!file_exists($filename)) {
            return $this->response
                ->setStatusCode(404)
                ->setBody('No existe la plantilla: ' . $filename);
        }

        // ===================== CARGA EXCEL =====================

        try {

            $reader = new Xlsx();

            $excel = $reader->load($filename);

        } catch (\Exception $e) {

            return $this->response
                ->setStatusCode(500)
                ->setBody(
                    'Error al cargar Excel: ' . $e->getMessage()
                );
        }

        // ===================== HOJA =====================

        $sheet = $excel->getSheetByName('Ctas_T_ET');

        if (!$sheet) {

            $excel->disconnectWorksheets();
            unset($excel);

            return $this->response
                ->setStatusCode(500)
                ->setBody(
                    'No existe la hoja Ctas_T_ET'
                );
        }

        // ===================== DATOS =====================

        $result = $this->Genera_model->Genera_ET($periodo);

        if (empty($result)) {

            $excel->disconnectWorksheets();
            unset($excel);

            return $this->response
                ->setStatusCode(404)
                ->setBody(
                    'Resultado vacío del modelo'
                );
        }
        
        // ===================== ESCRITURA EN EXCEL =====================

        $sheet->setCellValue('A3','CUENTA DE PRODUCCIÓN, ' . $periodo );
        $sheet->setCellValue('A25','CUENTA DE GENERACIÓN DEL INGRESO, ' . $periodo );

        foreach ($result as $row) {

            if (isset($row->CELDA) && isset($row->DATO)) 
            {
                $sheet->setCellValue($row->CELDA,$row->DATO);
            }
        }

        // ===================== NOMBRE ARCHIVO =====================

        $username = session()->get('username');

        $nombre = 'CuentaT_ET_' . $username . '_' . $periodo .'.xlsx';

        // ===================== GENERAR EXCEL =====================

        $writer = new XlsxWriter($excel);

        $writer->setPreCalculateFormulas(false);

        // ===================== GENERAR EN MEMORIA =====================

        while (ob_get_level()) {
            ob_end_clean();
        }

        ob_start();

        $writer->save('php://output');

        $contenido = ob_get_clean();

        // ===================== LIMPIEZA MEMORIA =====================

        $excel->disconnectWorksheets();

        unset($excel);

        // ===================== DESCARGA =====================

        return $this->response
            ->download($nombre, $contenido)
            ->setFileName($nombre);
    }
}
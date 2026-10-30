<?php

namespace App\Controllers;

use App\Models\CommonModel;

class Common extends BaseController
{
    protected $common_model;

    public function __construct()
    {
        $this->common_model = new CommonModel();
        
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }
    
        helper('download');
    }

    public function getAnio()
    {
        $result = $this->common_model->getAnio('EEA_parametros');

        return $this->response->setJSON($result);
    }

    public function getNivel()
    {
        $tipo = $this->request->getPost('plantilla');

        $result = $this->common_model->getNivel($tipo);

        return $this->response->setJSON($result);
    }

    public function getDnivel()
    {
        $nivel = $this->request->getPost('nivel');

        $result = $this->common_model->getDnivel($nivel);

        return $this->response->setJSON($result);
    }
}

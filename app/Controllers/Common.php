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

    private function utf8($data)
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
            }
        });

        return $data;
    }

    public function getPeriodo()
    {
        $result = $this->common_model->getPeriodo('ccnnma_datecono');

        return $this->response->setJSON($this->utf8($result));
    }

    public function getNivel()
    {
        $tipo = $this->request->getPost('tipo');

        $result = $this->common_model->getNivel($tipo);

        return $this->response->setJSON($this->utf8($result));
    }

    public function getDnivel()
    {
        $nivel = $this->request->getPost('nivel');

        $result = $this->common_model->getDnivel($nivel);

        return $this->response->setJSON($this->utf8($result));
    }
}
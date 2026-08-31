<?php

namespace App\Models;

use CodeIgniter\Model;

class GeneraModel extends Model
{
    public function getParam($param)
    {
        $query = $this->db->query("EXEC sp_GetParam ?",
            [
                $param['anio']
            ]
        );

        return $query->getResult();
    }
    
    public function getData($param)
    {
        $query = $this->db->query(
            "EXEC sp_GetParam ?, ?, ?, ?, ?",
            [
                $param['anio'],
                $param['formato'],
                $param['plantilla'],
                $param['nivel'],
                $param['dnivel']
            ]
        );

        return $query->getResult();
    }
}
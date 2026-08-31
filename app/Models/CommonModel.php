<?php

namespace App\Models;

use CodeIgniter\Model;

class CommonModel extends Model
{
    public function getAnio($table)
    {
        $builder = $this->db->table($table);

        $builder->select('Anio as valor, Anio as descr');
        $builder->distinct();
        $builder->orderBy('Anio', 'ASC');

        $query = $builder->get();

        return $query->getResultArray();
    }

    public function getNivel($nivel)
    {
        $query = $this->db->query("EXEC sp_ccnngen_lista_nivel " . $nivel);

        return $query->getResultArray();
    }    
    
    public function getDnivel($nivel)
    {
        $query = $this->db->query("EXEC sp_ccnngen_lista_ae " . $nivel);

        return $query->getResultArray();
    }

    public function getAE($nivel, $dnivel)
    {
        $query = $this->db->query(
            "EXEC sp_ccnngen_lista_nivel_ae ?, ?",
            [$nivel, $dnivel]
        );

        return $query->getResultArray();
    }

}
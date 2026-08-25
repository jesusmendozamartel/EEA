<?php

namespace App\Models;

use CodeIgniter\Model;

class CommonModel extends Model
{
    public function getPeriodo($table)
    {
        $builder = $this->db->table($table);

        $builder->select('Annio as valor, Annio as descr');
        $builder->distinct();
        $builder->orderBy('Annio', 'ASC');

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

    public function getSI()
    {
        $query = $this->db->query(
            "EXEC sp_ccnngen_lista_SI"
        );

        return $query->getResultArray();
    }

    public function getTransac($table)
    {
        $builder = $this->db->table($table);

        $builder->select('item valor, descripcion descr');
        $builder->orderBy('item', 'ASC');

        $query = $builder->get();

        return $query->getResultArray();
    }
}
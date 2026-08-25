<?php

namespace App\Models;

use CodeIgniter\Model;

class GeneraModel extends Model
{
    public function Genera_ET($periodo)
    {
        $sql = "EXEC sp_Procesa_CuentasT_ET ?";

        $query = $this->db->query($sql, [$periodo]);

        return $query->getResult();
    }
}
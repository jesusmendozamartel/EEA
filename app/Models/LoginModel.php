<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
    public function valida($username, $password)
    {
        $db = db_connect();

        $query = $db->query(
            "EXEC dncn_administrativo.dbo.sp_validar_usuario ?, ?",
            [$username, $password]
        );

        return $query->getNumRows() === 1;
    }
}
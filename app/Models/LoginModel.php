<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
    protected $DBGroup = 'default';

    public function valida($username, $password)
    {
        $db = db_connect();

        $sql = "
            EXEC dncn_administrativo.dbo.sp_validar_usuario ?, ?
        ";

        $query = $db->query($sql, [
            $username,
            $password
        ]);

        return $query->getNumRows() === 1;
    }
}
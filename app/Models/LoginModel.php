<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginModel extends Model
{
    public function valida($username, $password)
    {
        $db = db_connect();

        try {
            $query = $db->query(
                "EXEC dncn_administrativo.dbo.sp_validar_usuario ?, ?",
                [$username, $password]
            );
        } catch (\Throwable $e) {
            // Do not let a database exception expose bound login credentials
            // in an uncaught stack trace.
            log_message('error', 'Login validation failed ({tipo}, código {codigo}).', [
                'tipo'   => get_class($e),
                'codigo' => (string) $e->getCode(),
            ]);

            return false;
        }

        if ($query === false) {
            return false;
        }

        try {
            // SQLSRV forward-only cursors cannot report getNumRows(). Read at
            // most two rows to preserve the existing exactly-one-row check.
            $primeraFila = $query->getUnbufferedRow('array');
            if ($primeraFila === null || $primeraFila === false) {
                return false;
            }

            return $query->getUnbufferedRow('array') === null;
        } finally {
            $query->freeResult();
        }
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class GeneraModel extends Model
{
    /**
     * Obtiene el código y la descripción de una actividad económica.
     *
     * @return array{activ: mixed, descrip_activ: mixed}|null
     */
    public function getDescripcionActividad(int $nivel, string $dnivel): ?array
    {
        $query = $this->db->query(
            'EXEC [DNCN_Masters].[dbo].[sp_ccnngen_lista_desc_ae] ?, ?',
            [$nivel, $dnivel]
        );

        $fila = $query->getRowArray();
        if ($fila === null) {
            return null;
        }

        $fila = array_change_key_case($fila, CASE_LOWER);

        return [
            'activ'         => $fila['activ'] ?? null,
            'descrip_activ' => $fila['descrip_activ'] ?? null,
        ];
    }

    public function getDataMP($param,$modo)
    {
        $query = $this->db->query("EXEC getDataMP ?, ?, ?, ?",
            [
                $param['anio'],
                $param['formato'],
                $param['dnivel'],
                $modo
            ]
        );
        
        return $query->getResultArray();
    }

    /**
     * Ejecuta getDataMP y expone sus filas de forma incremental para no
     * mantener todo el resultado en memoria.
     *
     * @return \Generator<int, array<string, mixed>>
     */
    public function getDataMPStream($param, $modo): \Generator
    {
        $query = $this->db->query("EXEC getDataMP ?, ?, ?, ?", [
            $param['anio'],
            $param['formato'],
            $param['dnivel'],
            $modo,
        ]);

        return (static function () use ($query): \Generator {
            try {
                while (($fila = $query->getUnbufferedRow('array')) !== null && $fila !== false) {
                    yield $fila;
                }
            } finally {
                $query->freeResult();
            }
        })();
    }
    
    public function getDataDpto($param,$Dpto)
    {
        $query = $this->db->query("EXEC getDataDpto ?, ?, ?, ?",
            [
                $param['anio'],
                $param['formato'],
                $param['dnivel'],
                $Dpto
            ]
        );
        
        return $query->getResult();
    }
   
}

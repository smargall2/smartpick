<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/Usuario.php';

class UsuarioRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    public function buscarPorCI($ci)
    {
        $sql = "
            SELECT
                p.CI,
                p.Nombre_completo,
                p.Mail,
                p.Contrasena,
                o.Rol

            FROM persona p

            INNER JOIN operario o
                ON p.CI = o.CI_operario

            WHERE p.CI = ?

            LIMIT 1
        ";


        $stmt = mysqli_prepare(
            $this->conexion,
            $sql
        );


        if (!$stmt) {
            die(
                'Error al preparar la consulta: ' .
                mysqli_error($this->conexion)
            );
        }


        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $ci
        );


        mysqli_stmt_execute($stmt);


        $resultado =
            mysqli_stmt_get_result($stmt);


        $fila =
            mysqli_fetch_assoc($resultado);


        mysqli_stmt_close($stmt);


        if (!$fila) {
            return null;
        }


        return new Usuario(
            $fila['CI'],
            $fila['Nombre_completo'],
            $fila['Mail'],
            $fila['Contrasena'],
            $fila['Rol']
        );
    }
}
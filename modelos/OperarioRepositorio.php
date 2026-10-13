<?php

class OperarioRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    // ==================================================
    // OBTENER OPERARIOS
    // ==================================================

    public function obtenerOperarios()
    {
        $sql = "
            SELECT
                o.CI_operario,
                o.Rol,
                p.Nombre_completo,
                p.Mail,
                p.Telefono

            FROM operario o

            INNER JOIN persona p
                ON p.CI = o.CI_operario

            WHERE LOWER(o.Rol) IN (
                'operario',
                'administrador'
            )

            ORDER BY p.Nombre_completo ASC
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            throw new Exception(
                "Error al obtener los operarios: " .
                mysqli_error($this->conexion)
            );
        }

        return mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );
    }


    // ==================================================
    // BUSCAR OPERARIO POR CI
    // ==================================================

    public function buscarPorCI($ci)
    {
        $sql = "
            SELECT
                o.CI_operario,
                o.Rol,
                p.Nombre_completo

            FROM operario o

            INNER JOIN persona p
                ON p.CI = o.CI_operario

            WHERE o.CI_operario = ?

            AND LOWER(o.Rol) IN (
                'operario',
                'administrador'
            )

            LIMIT 1
        ";

        $stmt = mysqli_prepare(
            $this->conexion,
            $sql
        );

        if (!$stmt) {
            throw new Exception(
                "Error al preparar la consulta: " .
                mysqli_error($this->conexion)
            );
        }


        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $ci
        );


        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(
                "Error al buscar el operario: " .
                mysqli_stmt_error($stmt)
            );
        }


        $resultado =
            mysqli_stmt_get_result($stmt);


        $operario =
            mysqli_fetch_assoc($resultado);


        mysqli_stmt_close($stmt);


        return $operario;
    }
}
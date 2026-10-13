<?php

class ZonaRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    // ==================================================
    // LISTAR ZONAS
    // ==================================================

    public function obtenerTodas()
    {
        $sql = "
            SELECT
                z.ID_zona,
                z.Nombre,
                z.Nivel,
                z.Estante,
                z.Descripcion,
                z.ID_deposito,
                d.Nombre AS Deposito

            FROM zona z

            INNER JOIN deposito d
                ON d.ID_deposito = z.ID_deposito

            ORDER BY
                d.Nombre ASC,
                z.Nombre ASC,
                z.Nivel ASC,
                z.Estante ASC
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );


        if (!$resultado) {

            throw new Exception(
                "Error al obtener las zonas: " .
                mysqli_error($this->conexion)
            );
        }


        return mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );
    }


    // ==================================================
    // BUSCAR ZONA POR ID
    // ==================================================

    public function buscarPorId($idZona)
    {
        $sql = "
            SELECT
                z.ID_zona,
                z.Nombre,
                z.Nivel,
                z.Estante,
                z.Descripcion,
                z.ID_deposito,
                d.Nombre AS Deposito

            FROM zona z

            INNER JOIN deposito d
                ON d.ID_deposito = z.ID_deposito

            WHERE z.ID_zona = ?

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


        $idZona = (int)$idZona;


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $idZona
        );


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                "Error al buscar la zona: " .
                mysqli_stmt_error($stmt)
            );
        }


        $resultado =
            mysqli_stmt_get_result($stmt);


        $zona =
            mysqli_fetch_assoc($resultado);


        mysqli_stmt_close($stmt);


        return $zona;
    }
}
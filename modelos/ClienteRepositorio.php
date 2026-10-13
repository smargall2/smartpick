<?php

class ClienteRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    // ==================================================
    // LISTAR CLIENTES
    // ==================================================

    public function obtenerTodos()
    {
        $sql = "
            SELECT
                c.CI_cliente,
                p.Nombre_completo,
                p.Telefono,
                p.Mail,
                p.Direccion

            FROM cliente c

            INNER JOIN persona p
                ON p.CI = c.CI_cliente

            ORDER BY p.Nombre_completo ASC
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            die(
                'Error al obtener clientes: ' .
                mysqli_error($this->conexion)
            );
        }

        return mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );
    }


    // ==================================================
    // BUSCAR CLIENTES
    // ==================================================

    public function buscar($texto)
    {
        $sql = "
            SELECT
                c.CI_cliente,
                p.Nombre_completo,
                p.Telefono,
                p.Mail,
                p.Direccion

            FROM cliente c

            INNER JOIN persona p
                ON p.CI = c.CI_cliente

            WHERE
                c.CI_cliente LIKE ?
                OR p.Nombre_completo LIKE ?

            ORDER BY p.Nombre_completo ASC

            LIMIT 10
        ";

        $stmt = mysqli_prepare(
            $this->conexion,
            $sql
        );

        if (!$stmt) {
            die(
                'Error al preparar búsqueda de clientes: ' .
                mysqli_error($this->conexion)
            );
        }

        $busqueda = '%' . $texto . '%';

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $busqueda,
            $busqueda
        );

        mysqli_stmt_execute($stmt);

        $resultado =
            mysqli_stmt_get_result($stmt);

        $clientes =
            mysqli_fetch_all(
                $resultado,
                MYSQLI_ASSOC
            );

        mysqli_stmt_close($stmt);

        return $clientes;
    }


    // ==================================================
    // BUSCAR CLIENTE POR CI
    // ==================================================

    public function buscarPorCI($ci)
    {
        $sql = "
            SELECT
                c.CI_cliente,
                p.Nombre_completo,
                p.Telefono,
                p.Mail,
                p.Direccion

            FROM cliente c

            INNER JOIN persona p
                ON p.CI = c.CI_cliente

            WHERE c.CI_cliente = ?

            LIMIT 1
        ";

        $stmt = mysqli_prepare(
            $this->conexion,
            $sql
        );

        if (!$stmt) {
            die(
                'Error al preparar búsqueda del cliente: ' .
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

        $cliente =
            mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);

        return $cliente;
    }


    // ==================================================
    // CREAR CLIENTE
    // ==================================================

    public function crear(
        $ci,
        $nombre,
        $telefono,
        $mail,
        $direccion
    ) {

        try {

            mysqli_begin_transaction(
                $this->conexion
            );


            // ------------------------------------------
            // VERIFICAR QUE LA CI NO EXISTA
            // ------------------------------------------

            $sqlExiste = "
                SELECT CI
                FROM persona
                WHERE CI = ?
                LIMIT 1
            ";

            $stmtExiste = mysqli_prepare(
                $this->conexion,
                $sqlExiste
            );

            if (!$stmtExiste) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtExiste,
                "s",
                $ci
            );

            mysqli_stmt_execute($stmtExiste);

            $resultadoExiste =
                mysqli_stmt_get_result($stmtExiste);

            if (mysqli_fetch_assoc($resultadoExiste)) {

                mysqli_stmt_close($stmtExiste);

                throw new Exception(
                    "Ya existe una persona registrada con esa CI."
                );
            }

            mysqli_stmt_close($stmtExiste);


            // ------------------------------------------
            // VERIFICAR MAIL SI FUE INGRESADO
            // ------------------------------------------

            if ($mail !== '') {

                $sqlMail = "
                    SELECT CI
                    FROM persona
                    WHERE Mail = ?
                    LIMIT 1
                ";

                $stmtMail = mysqli_prepare(
                    $this->conexion,
                    $sqlMail
                );

                if (!$stmtMail) {
                    throw new Exception(
                        mysqli_error($this->conexion)
                    );
                }

                mysqli_stmt_bind_param(
                    $stmtMail,
                    "s",
                    $mail
                );

                mysqli_stmt_execute($stmtMail);

                $resultadoMail =
                    mysqli_stmt_get_result($stmtMail);

                if (mysqli_fetch_assoc($resultadoMail)) {

                    mysqli_stmt_close($stmtMail);

                    throw new Exception(
                        "Ya existe una persona registrada con ese correo."
                    );
                }

                mysqli_stmt_close($stmtMail);
            }


            // ------------------------------------------
            // CREAR PERSONA
            // ------------------------------------------

            $sqlPersona = "
                INSERT INTO persona
                (
                    CI,
                    Nombre_completo,
                    Telefono,
                    Mail,
                    Direccion,
                    Contrasena,
                    Ultimo_acceso
                )
                VALUES
                (
                    ?,
                    ?,
                    NULLIF(?, ''),
                    NULLIF(?, ''),
                    NULLIF(?, ''),
                    '',
                    NULL
                )
            ";

            $stmtPersona = mysqli_prepare(
                $this->conexion,
                $sqlPersona
            );

            if (!$stmtPersona) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtPersona,
                "sssss",
                $ci,
                $nombre,
                $telefono,
                $mail,
                $direccion
            );

            if (!mysqli_stmt_execute($stmtPersona)) {

                throw new Exception(
                    mysqli_stmt_error($stmtPersona)
                );
            }

            mysqli_stmt_close($stmtPersona);


            // ------------------------------------------
            // CREAR CLIENTE
            // ------------------------------------------

            $sqlCliente = "
                INSERT INTO cliente
                (
                    CI_cliente
                )
                VALUES
                (
                    ?
                )
            ";

            $stmtCliente = mysqli_prepare(
                $this->conexion,
                $sqlCliente
            );

            if (!$stmtCliente) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtCliente,
                "s",
                $ci
            );

            if (!mysqli_stmt_execute($stmtCliente)) {

                throw new Exception(
                    mysqli_stmt_error($stmtCliente)
                );
            }

            mysqli_stmt_close($stmtCliente);


            // ------------------------------------------
            // CONFIRMAR TRANSACCIÓN
            // ------------------------------------------

            mysqli_commit(
                $this->conexion
            );


        } catch (Exception $e) {

            mysqli_rollback(
                $this->conexion
            );

            throw $e;
        }
    }
}
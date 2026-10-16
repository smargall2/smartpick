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
    public function modificar($ci, $nombre, $telefono, $mail, $direccion)
    {
        if (trim($ci) === '' || trim($nombre) === '') throw new Exception('La CI y el nombre son obligatorios.');
        if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) throw new Exception('Correo inválido.');
        $sql = "UPDATE persona p INNER JOIN cliente c ON c.CI_cliente=p.CI SET p.Nombre_completo=?, p.Telefono=NULLIF(?, ''), p.Mail=NULLIF(?, ''), p.Direccion=NULLIF(?, '') WHERE p.CI=?";
        $stmt = mysqli_prepare($this->conexion, $sql);
        if (!$stmt) throw new Exception(mysqli_error($this->conexion));
        mysqli_stmt_bind_param($stmt, 'sssss', $nombre, $telefono, $mail, $direccion, $ci);
        if (!mysqli_stmt_execute($stmt)) { $error=mysqli_stmt_error($stmt); mysqli_stmt_close($stmt); throw new Exception($error); }
        mysqli_stmt_close($stmt);
    }

    public function eliminar($ci)
    {
        mysqli_begin_transaction($this->conexion);
        try {
            $stmt=mysqli_prepare($this->conexion, 'SELECT CI_cliente FROM cliente WHERE CI_cliente=? FOR UPDATE');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt,'s',$ci); mysqli_stmt_execute($stmt);
            $existe=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
            if (!$existe) throw new Exception('Cliente no encontrado.');
            $stmt=mysqli_prepare($this->conexion,'SELECT COUNT(*) AS total FROM recibe_entrega WHERE CI_cliente=?');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt,'s',$ci); mysqli_stmt_execute($stmt);
            $total=(int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total']; mysqli_stmt_close($stmt);
            if ($total>0) throw new Exception('No se puede eliminar: el cliente tiene pedidos asociados.');
            $stmt=mysqli_prepare($this->conexion,'SELECT COUNT(*) AS total FROM operario WHERE CI_operario=?');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt,'s',$ci); mysqli_stmt_execute($stmt);
            $esOperario=(int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total']>0; mysqli_stmt_close($stmt);
            $stmt=mysqli_prepare($this->conexion,'DELETE FROM cliente WHERE CI_cliente=?');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt,'s',$ci);
            if (!mysqli_stmt_execute($stmt)) { $e=mysqli_stmt_error($stmt); mysqli_stmt_close($stmt); throw new Exception($e); }
            mysqli_stmt_close($stmt);
            if (!$esOperario) {
                $stmt=mysqli_prepare($this->conexion,'DELETE FROM persona WHERE CI=?');
                if (!$stmt) throw new Exception(mysqli_error($this->conexion));
                mysqli_stmt_bind_param($stmt,'s',$ci);
                if (!mysqli_stmt_execute($stmt)) { $e=mysqli_stmt_error($stmt); mysqli_stmt_close($stmt); throw new Exception($e); }
                mysqli_stmt_close($stmt);
            }
            mysqli_commit($this->conexion);
        } catch (Throwable $e) { mysqli_rollback($this->conexion); throw $e; }
    }
}

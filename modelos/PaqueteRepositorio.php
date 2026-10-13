<?php

class PaqueteRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    // ==================================================
    // REGISTRAR PAQUETE EN DEPÓSITO
    // ==================================================

    public function crear(
        $ciCliente,
        $descripcion,
        $categoria,
        $idZona
    ) {
        try {

            mysqli_begin_transaction(
                $this->conexion
            );

            $idZona = (int)$idZona;


            // ==========================================
            // 1. VALIDAR CLIENTE
            // ==========================================

            $sqlCliente = "
                SELECT CI_cliente

                FROM cliente

                WHERE CI_cliente = ?

                LIMIT 1

                FOR UPDATE
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
                $ciCliente
            );

            mysqli_stmt_execute(
                $stmtCliente
            );

            $resultadoCliente =
                mysqli_stmt_get_result(
                    $stmtCliente
                );

            $cliente =
                mysqli_fetch_assoc(
                    $resultadoCliente
                );

            mysqli_stmt_close(
                $stmtCliente
            );


            if (!$cliente) {

                throw new Exception(
                    "El cliente seleccionado no existe."
                );
            }


            // ==========================================
            // 2. VALIDAR ZONA
            // ==========================================

            $sqlZona = "
                SELECT ID_zona

                FROM zona

                WHERE ID_zona = ?

                LIMIT 1

                FOR UPDATE
            ";

            $stmtZona = mysqli_prepare(
                $this->conexion,
                $sqlZona
            );

            if (!$stmtZona) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtZona,
                "i",
                $idZona
            );

            mysqli_stmt_execute(
                $stmtZona
            );

            $resultadoZona =
                mysqli_stmt_get_result(
                    $stmtZona
                );

            $zona =
                mysqli_fetch_assoc(
                    $resultadoZona
                );

            mysqli_stmt_close(
                $stmtZona
            );


            if (!$zona) {

                throw new Exception(
                    "La ubicación seleccionada no existe."
                );
            }


            // ==========================================
            // 3. CREAR PRODUCTO / PAQUETE
            // ==========================================

            $sqlProducto = "
                INSERT INTO producto
                (
                    Tipo,
                    Descripcion,
                    Stock_actual,
                    Stock_minimo,
                    Categoria
                )
                VALUES
                (
                    'DEPOSITO',
                    ?,
                    1,
                    0,
                    ?
                )
            ";

            $stmtProducto = mysqli_prepare(
                $this->conexion,
                $sqlProducto
            );

            if (!$stmtProducto) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtProducto,
                "ss",
                $descripcion,
                $categoria
            );


            if (!mysqli_stmt_execute(
                $stmtProducto
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtProducto
                    )
                );
            }


            mysqli_stmt_close(
                $stmtProducto
            );


            $idProducto =
                mysqli_insert_id(
                    $this->conexion
                );


            // ==========================================
            // 4. CREAR PEDIDO DEL PAQUETE
            // ==========================================

            $sqlPedido = "
                INSERT INTO pedido
                (
                    Fecha_entregado,
                    Fecha_ingresado,
                    Estado
                )
                VALUES
                (
                    NULL,
                    CURDATE(),
                    'ALMACENADO'
                )
            ";

            $resultadoPedido =
                mysqli_query(
                    $this->conexion,
                    $sqlPedido
                );


            if (!$resultadoPedido) {

                throw new Exception(
                    mysqli_error(
                        $this->conexion
                    )
                );
            }


            $idPedido =
                mysqli_insert_id(
                    $this->conexion
                );


            // ==========================================
            // 5. ASOCIAR CLIENTE
            // ==========================================

            $sqlRecibe = "
                INSERT INTO recibe_entrega
                (
                    ID_pedido,
                    CI_cliente
                )
                VALUES
                (
                    ?,
                    ?
                )
            ";

            $stmtRecibe = mysqli_prepare(
                $this->conexion,
                $sqlRecibe
            );

            if (!$stmtRecibe) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtRecibe,
                "is",
                $idPedido,
                $ciCliente
            );


            if (!mysqli_stmt_execute(
                $stmtRecibe
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtRecibe
                    )
                );
            }


            mysqli_stmt_close(
                $stmtRecibe
            );


            // ==========================================
            // 6. ASOCIAR PAQUETE AL PEDIDO
            // ==========================================

            $sqlContiene = "
                INSERT INTO contiene
                (
                    ID_pedido,
                    ID_producto,
                    Cantidad
                )
                VALUES
                (
                    ?,
                    ?,
                    1
                )
            ";

            $stmtContiene = mysqli_prepare(
                $this->conexion,
                $sqlContiene
            );

            if (!$stmtContiene) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtContiene,
                "ii",
                $idPedido,
                $idProducto
            );


            if (!mysqli_stmt_execute(
                $stmtContiene
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtContiene
                    )
                );
            }


            mysqli_stmt_close(
                $stmtContiene
            );


            // ==========================================
            // 7. ASIGNAR UBICACIÓN
            // ==========================================

            $sqlUbicacion = "
                INSERT INTO esta
                (
                    ID_producto,
                    ID_zona
                )
                VALUES
                (
                    ?,
                    ?
                )
            ";

            $stmtUbicacion = mysqli_prepare(
                $this->conexion,
                $sqlUbicacion
            );

            if (!$stmtUbicacion) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtUbicacion,
                "ii",
                $idProducto,
                $idZona
            );


            if (!mysqli_stmt_execute(
                $stmtUbicacion
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtUbicacion
                    )
                );
            }


            mysqli_stmt_close(
                $stmtUbicacion
            );


            // ==========================================
            // CONFIRMAR TRANSACCIÓN
            // ==========================================

            mysqli_commit(
                $this->conexion
            );


            return [
                'id_pedido' => $idPedido,
                'id_producto' => $idProducto
            ];


        } catch (Exception $e) {

            mysqli_rollback(
                $this->conexion
            );

            throw $e;
        }
    }
}
<?php

class PickingRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    // =========================================
    // PEDIDOS ASIGNADOS AL OPERARIO
    // =========================================

    public function obtenerPedidosOperario($ciOperario)
    {
        $sql = "
            SELECT DISTINCT
                pe.ID_pedido,
                pe.Fecha_ingresado,
                pe.Estado,
                re.CI_cliente,
                pc.Nombre_completo AS Cliente,

                CASE
                    WHEN UPPER(p.Tipo) = 'DEPOSITO'
                    THEN 'DEPOSITO'
                    ELSE 'ALMACEN'
                END AS Tipo_pedido

            FROM Atiende a

            INNER JOIN Pedido pe
                ON pe.ID_pedido = a.ID_pedido

            INNER JOIN Recibe_entrega re
                ON re.ID_pedido = pe.ID_pedido

            INNER JOIN Persona pc
                ON pc.CI = re.CI_cliente

            INNER JOIN Contiene c
                ON c.ID_pedido = pe.ID_pedido

            INNER JOIN Producto p
                ON p.ID_producto = c.ID_producto

            WHERE a.CI_operario = ?

            AND UPPER(pe.Estado) = 'PREPARANDO'

            ORDER BY pe.Fecha_ingresado ASC
        ";

        $stmt = mysqli_prepare(
            $this->conexion,
            $sql
        );

        if (!$stmt) {
            throw new Exception(
                mysqli_error($this->conexion)
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $ciOperario
        );

        mysqli_stmt_execute($stmt);

        $resultado =
            mysqli_stmt_get_result($stmt);

        $pedidos =
            mysqli_fetch_all(
                $resultado,
                MYSQLI_ASSOC
            );

        mysqli_stmt_close($stmt);

        return $pedidos;
    }


    // =========================================
    // INICIALIZAR PICKING
    // =========================================

    public function inicializarPicking($idPedido)
    {
        $sql = "
            INSERT IGNORE INTO PickingDetalle
            (
                ID_pedido,
                ID_producto,
                Confirmado
            )

            SELECT
                c.ID_pedido,
                c.ID_producto,
                0

            FROM Contiene c

            WHERE c.ID_pedido = ?
        ";

        $stmt = mysqli_prepare(
            $this->conexion,
            $sql
        );

        if (!$stmt) {
            throw new Exception(
                mysqli_error($this->conexion)
            );
        }

        $idPedido = (int)$idPedido;

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $idPedido
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(
                mysqli_stmt_error($stmt)
            );
        }

        mysqli_stmt_close($stmt);
    }


    // =========================================
    // HOJA DE PICKING
    // =========================================

    public function obtenerHojaPicking(
        $idPedido,
        $ciOperario
    ) {
        $idPedido = (int)$idPedido;

        $this->inicializarPicking($idPedido);


        $sql = "
            SELECT
                pe.ID_pedido,
                pe.Fecha_ingresado,
                pe.Estado,

                re.CI_cliente,

                cliente.Nombre_completo AS Cliente,

                c.ID_producto,
                c.Cantidad,

                p.Descripcion,
                p.Tipo,
                p.Categoria,
                p.Stock_actual,

                e.ID_zona,

                z.Nombre AS Zona,
                z.Nivel,
                z.Estante,

                pd.Confirmado,
                pd.Fecha_confirmacion

            FROM Pedido pe

            INNER JOIN Atiende a
                ON a.ID_pedido = pe.ID_pedido

            INNER JOIN Recibe_entrega re
                ON re.ID_pedido = pe.ID_pedido

            INNER JOIN Persona cliente
                ON cliente.CI = re.CI_cliente

            INNER JOIN Contiene c
                ON c.ID_pedido = pe.ID_pedido

            INNER JOIN Producto p
                ON p.ID_producto = c.ID_producto

            LEFT JOIN Esta e
                ON e.ID_producto = p.ID_producto

            LEFT JOIN Zona z
                ON z.ID_zona = e.ID_zona

            LEFT JOIN PickingDetalle pd
                ON pd.ID_pedido = pe.ID_pedido
                AND pd.ID_producto = p.ID_producto

            WHERE pe.ID_pedido = ?

            AND a.CI_operario = ?

            AND UPPER(pe.Estado) = 'PREPARANDO'

            ORDER BY
                pd.Confirmado ASC,
                z.ID_zona ASC,
                z.Estante ASC,
                p.Descripcion ASC
        ";

        $stmt = mysqli_prepare(
            $this->conexion,
            $sql
        );

        if (!$stmt) {
            throw new Exception(
                mysqli_error($this->conexion)
            );
        }

        mysqli_stmt_bind_param(
            $stmt,
            "is",
            $idPedido,
            $ciOperario
        );

        mysqli_stmt_execute($stmt);

        $resultado =
            mysqli_stmt_get_result($stmt);

        $hoja =
            mysqli_fetch_all(
                $resultado,
                MYSQLI_ASSOC
            );

        mysqli_stmt_close($stmt);

        return $hoja;
    }


    // =========================================
    // CONFIRMAR RETIRO EN PICKING
    // =========================================

    public function confirmarProducto(
        $idPedido,
        $idProducto,
        $ciOperario
    ) {
        try {

            mysqli_begin_transaction(
                $this->conexion
            );

            $idPedido = (int)$idPedido;
            $idProducto = (int)$idProducto;


            // =========================================
            // VALIDAR PEDIDO Y OPERARIO
            // =========================================

            $sqlPedido = "
                SELECT
                    pe.ID_pedido,
                    pe.Estado

                FROM Pedido pe

                INNER JOIN Atiende a
                    ON a.ID_pedido = pe.ID_pedido

                WHERE pe.ID_pedido = ?

                AND a.CI_operario = ?

                AND UPPER(pe.Estado) = 'PREPARANDO'

                LIMIT 1

                FOR UPDATE
            ";

            $stmtPedido = mysqli_prepare(
                $this->conexion,
                $sqlPedido
            );

            if (!$stmtPedido) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtPedido,
                "is",
                $idPedido,
                $ciOperario
            );

            mysqli_stmt_execute($stmtPedido);

            $resultadoPedido =
                mysqli_stmt_get_result(
                    $stmtPedido
                );

            $pedido =
                mysqli_fetch_assoc(
                    $resultadoPedido
                );

            mysqli_stmt_close($stmtPedido);


            if (!$pedido) {

                throw new Exception(
                    "El pedido no está disponible para este operario."
                );
            }


            // =========================================
            // OBTENER ELEMENTO
            // =========================================

            $sqlProducto = "
                SELECT
                    c.Cantidad,
                    p.Stock_actual,
                    p.Descripcion,
                    p.Tipo

                FROM Contiene c

                INNER JOIN Producto p
                    ON p.ID_producto = c.ID_producto

                WHERE c.ID_pedido = ?

                AND c.ID_producto = ?

                LIMIT 1

                FOR UPDATE
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
                "ii",
                $idPedido,
                $idProducto
            );

            mysqli_stmt_execute($stmtProducto);

            $resultadoProducto =
                mysqli_stmt_get_result(
                    $stmtProducto
                );

            $producto =
                mysqli_fetch_assoc(
                    $resultadoProducto
                );

            mysqli_stmt_close($stmtProducto);


            if (!$producto) {

                throw new Exception(
                    "El elemento no pertenece al pedido."
                );
            }


            // =========================================
            // COMPROBAR PICKING
            // =========================================

            $sqlPicking = "
                SELECT
                    Confirmado

                FROM PickingDetalle

                WHERE ID_pedido = ?

                AND ID_producto = ?

                LIMIT 1

                FOR UPDATE
            ";

            $stmtPicking = mysqli_prepare(
                $this->conexion,
                $sqlPicking
            );

            if (!$stmtPicking) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtPicking,
                "ii",
                $idPedido,
                $idProducto
            );

            mysqli_stmt_execute($stmtPicking);

            $resultadoPicking =
                mysqli_stmt_get_result(
                    $stmtPicking
                );

            $detalle =
                mysqli_fetch_assoc(
                    $resultadoPicking
                );

            mysqli_stmt_close($stmtPicking);


            if (!$detalle) {

                throw new Exception(
                    "No existe el detalle de picking."
                );
            }


            if (
                (int)$detalle['Confirmado'] === 1
            ) {

                throw new Exception(
                    "Este elemento ya fue confirmado."
                );
            }


            // =========================================
            // SOLO ALMACEN DESCUENTA STOCK
            // =========================================

            if (
                strtoupper($producto['Tipo'])
                === 'ALMACEN'
            ) {

                $cantidad =
                    (int)$producto['Cantidad'];

                $stock =
                    (int)$producto['Stock_actual'];


                if ($stock < $cantidad) {

                    throw new Exception(
                        "No hay stock suficiente de " .
                        $producto['Descripcion']
                    );
                }


                $sqlStock = "
                    UPDATE Producto

                    SET Stock_actual =
                        Stock_actual - ?

                    WHERE ID_producto = ?
                ";

                $stmtStock = mysqli_prepare(
                    $this->conexion,
                    $sqlStock
                );

                if (!$stmtStock) {
                    throw new Exception(
                        mysqli_error(
                            $this->conexion
                        )
                    );
                }

                mysqli_stmt_bind_param(
                    $stmtStock,
                    "ii",
                    $cantidad,
                    $idProducto
                );


                if (!mysqli_stmt_execute(
                    $stmtStock
                )) {

                    throw new Exception(
                        mysqli_stmt_error(
                            $stmtStock
                        )
                    );
                }

                mysqli_stmt_close($stmtStock);
            }


            // =========================================
            // CONFIRMAR RETIRO
            // =========================================

            $sqlConfirmar = "
                UPDATE PickingDetalle

                SET
                    Confirmado = 1,
                    Fecha_confirmacion = NOW(),
                    CI_operario = ?

                WHERE ID_pedido = ?

                AND ID_producto = ?
            ";

            $stmtConfirmar = mysqli_prepare(
                $this->conexion,
                $sqlConfirmar
            );

            if (!$stmtConfirmar) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtConfirmar,
                "sii",
                $ciOperario,
                $idPedido,
                $idProducto
            );


            if (!mysqli_stmt_execute(
                $stmtConfirmar
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtConfirmar
                    )
                );
            }

            mysqli_stmt_close($stmtConfirmar);


            // =========================================
            // VER SI TERMINÓ
            // =========================================

            $sqlPendientes = "
                SELECT
                    COUNT(*) AS pendientes

                FROM PickingDetalle

                WHERE ID_pedido = ?

                AND Confirmado = 0
            ";

            $stmtPendientes = mysqli_prepare(
                $this->conexion,
                $sqlPendientes
            );

            if (!$stmtPendientes) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtPendientes,
                "i",
                $idPedido
            );

            mysqli_stmt_execute(
                $stmtPendientes
            );

            $resultadoPendientes =
                mysqli_stmt_get_result(
                    $stmtPendientes
                );

            $resultado =
                mysqli_fetch_assoc(
                    $resultadoPendientes
                );

            mysqli_stmt_close(
                $stmtPendientes
            );


            $terminado =
                ((int)$resultado['pendientes'] === 0);


            // =========================================
            // LISTO PARA VALIDACIÓN
            // =========================================

            if ($terminado) {

                $sqlEstado = "
                    UPDATE Pedido

                    SET Estado =
                        'LISTO_PARA_VALIDAR'

                    WHERE ID_pedido = ?
                ";

                $stmtEstado = mysqli_prepare(
                    $this->conexion,
                    $sqlEstado
                );

                if (!$stmtEstado) {
                    throw new Exception(
                        mysqli_error(
                            $this->conexion
                        )
                    );
                }

                mysqli_stmt_bind_param(
                    $stmtEstado,
                    "i",
                    $idPedido
                );


                if (!mysqli_stmt_execute(
                    $stmtEstado
                )) {

                    throw new Exception(
                        mysqli_stmt_error(
                            $stmtEstado
                        )
                    );
                }

                mysqli_stmt_close($stmtEstado);
            }


            mysqli_commit(
                $this->conexion
            );


            return [
                'ok' => true,
                'terminado' => $terminado,
                'tipo' =>
                    strtoupper($producto['Tipo'])
            ];


        } catch (Exception $e) {

            mysqli_rollback(
                $this->conexion
            );

            throw $e;
        }
    }
}
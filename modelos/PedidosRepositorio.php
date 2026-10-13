<?php

class PedidoRepositorio
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    // ==================================================
    // LISTAR PEDIDOS
    // ==================================================

    public function obtenerTodos()
    {
        $sql = "
            SELECT
                ID_pedido,
                Fecha_ingresado,
                Fecha_entregado,
                Estado
            FROM pedido
            ORDER BY
                COALESCE(Fecha_entregado, Fecha_ingresado) DESC,
                ID_pedido DESC
        ";

        $resultado = mysqli_query($this->conexion, $sql);

        if (!$resultado) {
            throw new Exception(
                "Error al obtener pedidos: " .
                mysqli_error($this->conexion)
            );
        }

        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }


    // ==================================================
    // BUSCAR PEDIDO POR ID
    // ==================================================

    public function buscarPorId($id)
    {
        $sql = "
            SELECT
                ID_pedido,
                Fecha_ingresado,
                Fecha_entregado,
                Estado
            FROM pedido
            WHERE ID_pedido = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($this->conexion, $sql);

        if (!$stmt) {
            throw new Exception(mysqli_error($this->conexion));
        }

        $id = (int)$id;

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $pedido = mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);

        return $pedido;
    }


    // ==================================================
    // PAQUETES DEL CLIENTE QUE SIGUEN EN EL DEPÓSITO
    // ==================================================

    public function obtenerPaquetesCliente($ciCliente)
    {
        $sql = "
            SELECT
                pe.ID_pedido,
                pe.Fecha_ingresado,
                pe.Estado,

                pr.ID_producto,
                pr.Descripcion,
                pr.Categoria,

                c.Cantidad

            FROM pedido pe

            INNER JOIN recibe_entrega re
                ON re.ID_pedido = pe.ID_pedido

            INNER JOIN contiene c
                ON c.ID_pedido = pe.ID_pedido

            INNER JOIN producto pr
                ON pr.ID_producto = c.ID_producto

            WHERE re.CI_cliente = ?

            AND UPPER(pr.Tipo) = 'DEPOSITO'

            AND UPPER(pe.Estado) IN (
                'INGRESADO',
                'ALMACENADO'
            )

            ORDER BY pe.Fecha_ingresado ASC
        ";

        $stmt = mysqli_prepare($this->conexion, $sql);

        if (!$stmt) {
            throw new Exception(mysqli_error($this->conexion));
        }

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $ciCliente
        );

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        $paquetes = mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );

        mysqli_stmt_close($stmt);

        return $paquetes;
    }


    // ==================================================
    // CREAR PEDIDO DE PRODUCTOS DE ALMACÉN
    // ==================================================

    public function crearPedidoAlmacen($ciCliente, $productos)
    {
        try {

            mysqli_begin_transaction($this->conexion);


            // ==========================================
            // VALIDAR STOCK
            // ==========================================

            foreach ($productos as $producto) {

                $idProducto =
                    (int)$producto['id_producto'];

                $cantidad =
                    (int)$producto['cantidad'];


                if ($cantidad <= 0) {

                    throw new Exception(
                        "La cantidad debe ser mayor a cero."
                    );
                }


                $sql = "
                    SELECT
                        Descripcion,
                        Tipo,
                        Stock_actual
                    FROM producto
                    WHERE ID_producto = ?
                    FOR UPDATE
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
                    "i",
                    $idProducto
                );

                mysqli_stmt_execute($stmt);

                $resultado =
                    mysqli_stmt_get_result($stmt);

                $productoBD =
                    mysqli_fetch_assoc($resultado);

                mysqli_stmt_close($stmt);


                if (!$productoBD) {

                    throw new Exception(
                        "Uno de los productos no existe."
                    );
                }


                if (
                    strtoupper($productoBD['Tipo'])
                    !== 'ALMACEN'
                ) {

                    throw new Exception(
                        "El producto seleccionado no pertenece al almacén."
                    );
                }


                if (
                    $cantidad >
                    (int)$productoBD['Stock_actual']
                ) {

                    throw new Exception(
                        "No hay stock suficiente de " .
                        $productoBD['Descripcion']
                    );
                }
            }


            // ==========================================
            // CREAR PEDIDO
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
                    'PENDIENTE'
                )
            ";

            if (!mysqli_query(
                $this->conexion,
                $sqlPedido
            )) {

                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }


            $idPedido =
                mysqli_insert_id($this->conexion);


            // ==========================================
            // ASOCIAR CLIENTE
            // ==========================================

            $sqlCliente = "
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
                "is",
                $idPedido,
                $ciCliente
            );

            if (!mysqli_stmt_execute($stmtCliente)) {

                throw new Exception(
                    mysqli_stmt_error($stmtCliente)
                );
            }

            mysqli_stmt_close($stmtCliente);


            // ==========================================
            // AGREGAR PRODUCTOS
            // ==========================================

            $sqlProducto = "
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


            foreach ($productos as $producto) {

                $idProducto =
                    (int)$producto['id_producto'];

                $cantidad =
                    (int)$producto['cantidad'];


                mysqli_stmt_bind_param(
                    $stmtProducto,
                    "iii",
                    $idPedido,
                    $idProducto,
                    $cantidad
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
            }


            mysqli_stmt_close($stmtProducto);


            mysqli_commit($this->conexion);

            return $idPedido;


        } catch (Exception $e) {

            mysqli_rollback($this->conexion);

            throw $e;
        }
    }


    // ==================================================
    // DETALLE DEL PEDIDO
    // ==================================================

    public function obtenerDetalle($idPedido)
    {
        $sql = "
            SELECT
                pe.ID_pedido,
                pe.Fecha_ingresado,
                pe.Fecha_entregado,
                pe.Estado,

                re.CI_cliente,

                cliente.Nombre_completo AS Cliente,
                cliente.Telefono AS Telefono_cliente,
                cliente.Mail AS Mail_cliente,

                a.CI_operario,

                operario.Nombre_completo AS Operario

            FROM pedido pe

            INNER JOIN recibe_entrega re
                ON re.ID_pedido = pe.ID_pedido

            INNER JOIN persona cliente
                ON cliente.CI = re.CI_cliente

            LEFT JOIN atiende a
                ON a.ID_pedido = pe.ID_pedido

            LEFT JOIN persona operario
                ON operario.CI = a.CI_operario

            WHERE pe.ID_pedido = ?

            LIMIT 1
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

        mysqli_stmt_execute($stmt);

        $resultado =
            mysqli_stmt_get_result($stmt);

        $pedido =
            mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);

        return $pedido;
    }


    // ==================================================
    // PRODUCTOS DEL PEDIDO
    // ==================================================

    public function obtenerProductosPedido($idPedido)
    {
        $sql = "
            SELECT
                c.ID_producto,
                c.Cantidad,

                p.Descripcion,
                p.Tipo,
                p.Categoria,
                p.Stock_actual

            FROM contiene c

            INNER JOIN producto p
                ON p.ID_producto = c.ID_producto

            WHERE c.ID_pedido = ?

            ORDER BY p.Descripcion ASC
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

        mysqli_stmt_execute($stmt);

        $resultado =
            mysqli_stmt_get_result($stmt);

        $productos =
            mysqli_fetch_all(
                $resultado,
                MYSQLI_ASSOC
            );

        mysqli_stmt_close($stmt);

        return $productos;
    }

        // ==================================================
// MODIFICAR ENTREGA PENDIENTE
// PRODUCTOS + SOLICITUD DE PAQUETES
// ==================================================

public function modificarEntregaPendiente(
    $idPedido,
    $productos,
    $idsPaquetes
) {

    try {

        mysqli_begin_transaction(
            $this->conexion
        );

        $idPedido = (int)$idPedido;


        // ==========================================
        // 1. BLOQUEAR PEDIDO PRINCIPAL
        // ==========================================

        $sqlPedido = "
            SELECT
                pe.ID_pedido,
                pe.Estado,
                re.CI_cliente

            FROM pedido pe

            INNER JOIN recibe_entrega re
                ON re.ID_pedido = pe.ID_pedido

            WHERE pe.ID_pedido = ?

            LIMIT 1

            FOR UPDATE
        ";


        $stmtPedido =
            mysqli_prepare(
                $this->conexion,
                $sqlPedido
            );


        if (!$stmtPedido) {

            throw new Exception(
                mysqli_error(
                    $this->conexion
                )
            );
        }


        mysqli_stmt_bind_param(
            $stmtPedido,
            "i",
            $idPedido
        );


        mysqli_stmt_execute(
            $stmtPedido
        );


        $resultadoPedido =
            mysqli_stmt_get_result(
                $stmtPedido
            );


        $pedido =
            mysqli_fetch_assoc(
                $resultadoPedido
            );


        mysqli_stmt_close(
            $stmtPedido
        );


        if (!$pedido) {

            throw new Exception(
                "El pedido no existe."
            );
        }


        if (
            strtoupper(
                $pedido['Estado']
            ) !== 'PENDIENTE'
        ) {

            throw new Exception(
                "Solo se pueden modificar pedidos pendientes."
            );
        }


        $ciCliente =
            $pedido['CI_cliente'];


        // ==========================================
        // 2. NORMALIZAR PRODUCTOS
        // ==========================================

        $productosNormalizados = [];


        foreach ($productos as $producto) {

            $idProducto =
                (int)(
                    $producto['id_producto']
                    ?? 0
                );

            $cantidad =
                (int)(
                    $producto['cantidad']
                    ?? 0
                );


            if (
                $idProducto <= 0 ||
                $cantidad <= 0
            ) {

                throw new Exception(
                    "Los productos seleccionados no son válidos."
                );
            }


            if (
                isset(
                    $productosNormalizados[
                        $idProducto
                    ]
                )
            ) {

                $productosNormalizados[
                    $idProducto
                ] += $cantidad;

            } else {

                $productosNormalizados[
                    $idProducto
                ] = $cantidad;
            }
        }


        // El pedido principal debe seguir teniendo
        // al menos un producto de ALMACEN.

        if (
            empty($productosNormalizados)
        ) {

            throw new Exception(
                "El pedido debe contener al menos un producto de almacén."
            );
        }


        // ==========================================
        // 3. VALIDAR PRODUCTOS Y STOCK
        // ==========================================

        $sqlProducto = "
            SELECT
                ID_producto,
                Descripcion,
                Tipo,
                Stock_actual

            FROM producto

            WHERE ID_producto = ?

            FOR UPDATE
        ";


        $stmtProducto =
            mysqli_prepare(
                $this->conexion,
                $sqlProducto
            );


        if (!$stmtProducto) {

            throw new Exception(
                mysqli_error(
                    $this->conexion
                )
            );
        }


        foreach (
            $productosNormalizados
            as $idProducto => $cantidad
        ) {

            mysqli_stmt_bind_param(
                $stmtProducto,
                "i",
                $idProducto
            );


            mysqli_stmt_execute(
                $stmtProducto
            );


            $resultadoProducto =
                mysqli_stmt_get_result(
                    $stmtProducto
                );


            $productoBD =
                mysqli_fetch_assoc(
                    $resultadoProducto
                );


            if (!$productoBD) {

                throw new Exception(
                    "Uno de los productos seleccionados no existe."
                );
            }


            if (
                strtoupper(
                    $productoBD['Tipo']
                ) !== 'ALMACEN'
            ) {

                throw new Exception(
                    "Solo se pueden agregar productos de almacén."
                );
            }


            if (
                $cantidad >
                (int)$productoBD['Stock_actual']
            ) {

                throw new Exception(
                    "No hay stock suficiente de "
                    . $productoBD['Descripcion']
                    . "."
                );
            }
        }


        mysqli_stmt_close(
            $stmtProducto
        );


        // ==========================================
        // 4. NORMALIZAR PAQUETES
        // ==========================================

        $paquetesNormalizados = [];


        foreach ($idsPaquetes as $idPaquete) {

            $idPaquete =
                (int)$idPaquete;


            if ($idPaquete > 0) {

                $paquetesNormalizados[
                    $idPaquete
                ] = $idPaquete;
            }
        }


        // ==========================================
        // 5. VALIDAR TODOS LOS PAQUETES
        // ANTES DE MODIFICAR NADA
        // ==========================================

        if (!empty($paquetesNormalizados)) {

            $sqlPaquete = "
                SELECT
                    pe.ID_pedido,
                    pe.Estado,
                    pr.Descripcion

                FROM pedido pe

                INNER JOIN recibe_entrega re
                    ON re.ID_pedido = pe.ID_pedido

                INNER JOIN contiene c
                    ON c.ID_pedido = pe.ID_pedido

                INNER JOIN producto pr
                    ON pr.ID_producto = c.ID_producto

                WHERE pe.ID_pedido = ?

                AND re.CI_cliente = ?

                AND UPPER(pr.Tipo) = 'DEPOSITO'

                AND UPPER(pe.Estado) IN (
                    'INGRESADO',
                    'ALMACENADO'
                )

                LIMIT 1

                FOR UPDATE
            ";


            $stmtPaquete =
                mysqli_prepare(
                    $this->conexion,
                    $sqlPaquete
                );


            if (!$stmtPaquete) {

                throw new Exception(
                    mysqli_error(
                        $this->conexion
                    )
                );
            }


            foreach (
                $paquetesNormalizados
                as $idPaquete
            ) {

                // No debería ser el pedido
                // principal que estamos editando.

                if ($idPaquete === $idPedido) {

                    throw new Exception(
                        "El paquete seleccionado no es válido."
                    );
                }


                mysqli_stmt_bind_param(
                    $stmtPaquete,
                    "is",
                    $idPaquete,
                    $ciCliente
                );


                mysqli_stmt_execute(
                    $stmtPaquete
                );


                $resultadoPaquete =
                    mysqli_stmt_get_result(
                        $stmtPaquete
                    );


                $paquete =
                    mysqli_fetch_assoc(
                        $resultadoPaquete
                    );


                if (!$paquete) {

                    throw new Exception(
                        "El paquete #"
                        . $idPaquete
                        . " no está disponible para retirar."
                    );
                }
            }


            mysqli_stmt_close(
                $stmtPaquete
            );
        }


        // ==========================================
        // 6. REEMPLAZAR PRODUCTOS DEL PEDIDO
        // ==========================================

        $sqlEliminar = "
            DELETE FROM contiene
            WHERE ID_pedido = ?
        ";


        $stmtEliminar =
            mysqli_prepare(
                $this->conexion,
                $sqlEliminar
            );


        if (!$stmtEliminar) {

            throw new Exception(
                mysqli_error(
                    $this->conexion
                )
            );
        }


        mysqli_stmt_bind_param(
            $stmtEliminar,
            "i",
            $idPedido
        );


        if (
            !mysqli_stmt_execute(
                $stmtEliminar
            )
        ) {

            throw new Exception(
                mysqli_stmt_error(
                    $stmtEliminar
                )
            );
        }


        mysqli_stmt_close(
            $stmtEliminar
        );


        // ==========================================
        // 7. INSERTAR PRODUCTOS NUEVOS
        // ==========================================

        $sqlInsertar = "
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
                ?
            )
        ";


        $stmtInsertar =
            mysqli_prepare(
                $this->conexion,
                $sqlInsertar
            );


        if (!$stmtInsertar) {

            throw new Exception(
                mysqli_error(
                    $this->conexion
                )
            );
        }


        foreach (
            $productosNormalizados
            as $idProducto => $cantidad
        ) {

            mysqli_stmt_bind_param(
                $stmtInsertar,
                "iii",
                $idPedido,
                $idProducto,
                $cantidad
            );


            if (
                !mysqli_stmt_execute(
                    $stmtInsertar
                )
            ) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtInsertar
                    )
                );
            }
        }


        mysqli_stmt_close(
            $stmtInsertar
        );


        // ==========================================
        // 8. SOLICITAR RETIRO DE LOS PAQUETES
        // ==========================================

        if (!empty($paquetesNormalizados)) {

            $sqlActualizarPaquete = "
                UPDATE pedido

                SET
                    Estado = 'PENDIENTE_RETIRO',
                    Fecha_entregado = NULL

                WHERE ID_pedido = ?
            ";


            $stmtActualizarPaquete =
                mysqli_prepare(
                    $this->conexion,
                    $sqlActualizarPaquete
                );


            if (!$stmtActualizarPaquete) {

                throw new Exception(
                    mysqli_error(
                        $this->conexion
                    )
                );
            }


            foreach (
                $paquetesNormalizados
                as $idPaquete
            ) {

                mysqli_stmt_bind_param(
                    $stmtActualizarPaquete,
                    "i",
                    $idPaquete
                );


                if (
                    !mysqli_stmt_execute(
                        $stmtActualizarPaquete
                    )
                ) {

                    throw new Exception(
                        mysqli_stmt_error(
                            $stmtActualizarPaquete
                        )
                    );
                }
            }


            mysqli_stmt_close(
                $stmtActualizarPaquete
            );
        }


        // ==========================================
        // 9. CONFIRMAR TODO JUNTO
        // ==========================================

        mysqli_commit(
            $this->conexion
        );


        return [

            'id_pedido' =>
                $idPedido,

            'paquetes' =>
                array_values(
                    $paquetesNormalizados
                )
        ];


    } catch (Exception $e) {

        mysqli_rollback(
            $this->conexion
        );

        throw $e;
    }
}

    // ==================================================
    // ASIGNAR OPERARIO
    // ==================================================

    public function asignarOperario(
        $idPedido,
        $ciOperario
    ) {

        try {

            mysqli_begin_transaction(
                $this->conexion
            );

            $idPedido = (int)$idPedido;


            // ==========================================
            // COMPROBAR QUE EL PEDIDO EXISTA
            // Y BLOQUEARLO DURANTE LA ASIGNACIÓN
            // ==========================================

            $sqlPedido = "
                SELECT Estado
                FROM pedido
                WHERE ID_pedido = ?
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
                "i",
                $idPedido
            );

            mysqli_stmt_execute($stmtPedido);

            $resultadoPedido =
                mysqli_stmt_get_result($stmtPedido);

            $pedido =
                mysqli_fetch_assoc($resultadoPedido);

            mysqli_stmt_close($stmtPedido);


            if (!$pedido) {

                throw new Exception(
                    "El pedido no existe."
                );
            }


            // ==========================================
            // VALIDAR ESTADO
            //
            // PENDIENTE y PENDIENTE_RETIRO:
            // permiten la primera asignación.
            //
            // PREPARANDO:
            // permite reasignar a otro operario.
            // ==========================================

            $estadoActual =
                strtoupper(
                    trim($pedido['Estado'])
                );

            $estadosPermitidos = [
                'PENDIENTE',
                'PENDIENTE_RETIRO',
                'PREPARANDO'
            ];

            if (
                !in_array(
                    $estadoActual,
                    $estadosPermitidos,
                    true
                )
            ) {

                throw new Exception(
                    "El pedido ya no puede ser asignado o reasignado."
                );
            }


            // ==========================================
            // VER SI YA TIENE OPERARIO
            // ==========================================

            $sqlExiste = "
                SELECT ID_pedido
                FROM atiende
                WHERE ID_pedido = ?
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
                "i",
                $idPedido
            );

            mysqli_stmt_execute($stmtExiste);

            $resultadoExiste =
                mysqli_stmt_get_result($stmtExiste);

            $yaExiste =
                mysqli_fetch_assoc($resultadoExiste);

            mysqli_stmt_close($stmtExiste);


            if ($yaExiste) {

                // REASIGNAR

                $sqlAsignar = "
                    UPDATE atiende
                    SET CI_operario = ?
                    WHERE ID_pedido = ?
                ";

                $stmtAsignar =
                    mysqli_prepare(
                        $this->conexion,
                        $sqlAsignar
                    );

                if (!$stmtAsignar) {
                    throw new Exception(
                        mysqli_error(
                            $this->conexion
                        )
                    );
                }

                mysqli_stmt_bind_param(
                    $stmtAsignar,
                    "si",
                    $ciOperario,
                    $idPedido
                );

            } else {

                // PRIMERA ASIGNACIÓN

                $sqlAsignar = "
                    INSERT INTO atiende
                    (
                        ID_pedido,
                        CI_operario
                    )
                    VALUES
                    (
                        ?,
                        ?
                    )
                ";

                $stmtAsignar =
                    mysqli_prepare(
                        $this->conexion,
                        $sqlAsignar
                    );

                if (!$stmtAsignar) {
                    throw new Exception(
                        mysqli_error(
                            $this->conexion
                        )
                    );
                }

                mysqli_stmt_bind_param(
                    $stmtAsignar,
                    "is",
                    $idPedido,
                    $ciOperario
                );
            }


            if (!mysqli_stmt_execute(
                $stmtAsignar
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtAsignar
                    )
                );
            }

            mysqli_stmt_close($stmtAsignar);


            // ==========================================
            // PASAR A PREPARANDO
            // ==========================================

            if ($estadoActual !== 'PREPARANDO') {

                $sqlEstado = "
                    UPDATE pedido
                    SET Estado = 'PREPARANDO'
                    WHERE ID_pedido = ?
                ";

                $stmtEstado = mysqli_prepare(
                    $this->conexion,
                    $sqlEstado
                );

                if (!$stmtEstado) {
                    throw new Exception(
                        mysqli_error($this->conexion)
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


            mysqli_commit($this->conexion);


        } catch (Exception $e) {

            mysqli_rollback($this->conexion);

            throw $e;
        }
    }


    // ==================================================
    // SOLICITAR RETIRO DE PAQUETES
    // ==================================================

    public function retirarPaquetesCliente(
        $ciCliente,
        $idsPedidos
    ) {

        if (empty($idsPedidos)) {
            return;
        }


        try {

            mysqli_begin_transaction(
                $this->conexion
            );


            foreach ($idsPedidos as $idPedido) {

                $idPedido = (int)$idPedido;


                // ======================================
                // VALIDAR PAQUETE
                // ======================================

                $sqlValidar = "
                    SELECT
                        pe.ID_pedido,
                        pe.Estado

                    FROM pedido pe

                    INNER JOIN recibe_entrega re
                        ON re.ID_pedido = pe.ID_pedido

                    INNER JOIN contiene c
                        ON c.ID_pedido = pe.ID_pedido

                    INNER JOIN producto pr
                        ON pr.ID_producto = c.ID_producto

                    WHERE pe.ID_pedido = ?

                    AND re.CI_cliente = ?

                    AND UPPER(pr.Tipo) = 'DEPOSITO'

                    AND UPPER(pe.Estado) IN (
                        'INGRESADO',
                        'ALMACENADO'
                    )

                    LIMIT 1

                    FOR UPDATE
                ";

                $stmtValidar =
                    mysqli_prepare(
                        $this->conexion,
                        $sqlValidar
                    );

                if (!$stmtValidar) {
                    throw new Exception(
                        mysqli_error(
                            $this->conexion
                        )
                    );
                }

                mysqli_stmt_bind_param(
                    $stmtValidar,
                    "is",
                    $idPedido,
                    $ciCliente
                );

                mysqli_stmt_execute(
                    $stmtValidar
                );

                $resultado =
                    mysqli_stmt_get_result(
                        $stmtValidar
                    );

                $paquete =
                    mysqli_fetch_assoc(
                        $resultado
                    );

                mysqli_stmt_close(
                    $stmtValidar
                );


                if (!$paquete) {

                    throw new Exception(
                        "El paquete #" .
                        $idPedido .
                        " no está disponible para solicitar su retiro."
                    );
                }


                // ======================================
                // PASAR A PENDIENTE_RETIRO
                // ======================================

                $sqlActualizar = "
                    UPDATE pedido

                    SET
                        Estado = 'PENDIENTE_RETIRO',
                        Fecha_entregado = NULL

                    WHERE ID_pedido = ?
                ";

                $stmtActualizar =
                    mysqli_prepare(
                        $this->conexion,
                        $sqlActualizar
                    );

                if (!$stmtActualizar) {
                    throw new Exception(
                        mysqli_error(
                            $this->conexion
                        )
                    );
                }

                mysqli_stmt_bind_param(
                    $stmtActualizar,
                    "i",
                    $idPedido
                );

                if (!mysqli_stmt_execute(
                    $stmtActualizar
                )) {

                    throw new Exception(
                        mysqli_stmt_error(
                            $stmtActualizar
                        )
                    );
                }

                mysqli_stmt_close(
                    $stmtActualizar
                );
            }


            mysqli_commit($this->conexion);


        } catch (Exception $e) {

            mysqli_rollback($this->conexion);

            throw $e;
        }
    }


    // ==================================================
    // VALIDAR PEDIDO
    // ==================================================

    public function validarPedido($idPedido)
    {
        try {

            mysqli_begin_transaction(
                $this->conexion
            );

            $idPedido = (int)$idPedido;


            // ==========================================
            // COMPROBAR PEDIDO
            // ==========================================

            $sqlPedido = "
                SELECT
                    pe.ID_pedido,
                    pe.Estado,

                    MAX(
                        CASE
                            WHEN UPPER(pr.Tipo) = 'DEPOSITO'
                            THEN 1
                            ELSE 0
                        END
                    ) AS Es_deposito,

                    MAX(
                        CASE
                            WHEN UPPER(pr.Tipo) = 'ALMACEN'
                            THEN 1
                            ELSE 0
                        END
                    ) AS Es_almacen

                FROM pedido pe

                INNER JOIN contiene c
                    ON c.ID_pedido = pe.ID_pedido

                INNER JOIN producto pr
                    ON pr.ID_producto = c.ID_producto

                WHERE pe.ID_pedido = ?

                GROUP BY
                    pe.ID_pedido,
                    pe.Estado

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
                "i",
                $idPedido
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
                    "El pedido no existe."
                );
            }


            if (
                strtoupper($pedido['Estado'])
                !== 'LISTO_PARA_VALIDAR'
            ) {

                throw new Exception(
                    "El pedido todavía no está listo para validar."
                );
            }


            // ==========================================
            // COMPROBAR PICKING
            // ==========================================

            $sqlPendientes = "
                SELECT
                    COUNT(*) AS pendientes

                FROM PickingDetalle

                WHERE ID_pedido = ?

                AND Confirmado = 0
            ";

            $stmtPendientes =
                mysqli_prepare(
                    $this->conexion,
                    $sqlPendientes
                );

            if (!$stmtPendientes) {
                throw new Exception(
                    mysqli_error(
                        $this->conexion
                    )
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

            $resultadoConsulta =
                mysqli_stmt_get_result(
                    $stmtPendientes
                );

            $resultado =
                mysqli_fetch_assoc(
                    $resultadoConsulta
                );

            mysqli_stmt_close(
                $stmtPendientes
            );


            if (
                (int)$resultado['pendientes'] > 0
            ) {

                throw new Exception(
                    "Todavía existen elementos sin confirmar en el picking."
                );
            }


            // ==========================================
            // DETERMINAR ESTADO FINAL
            // ==========================================

            if (
                (int)$pedido['Es_deposito'] === 1
            ) {

                $estadoFinal = 'RETIRADO';

            } else {

                $estadoFinal = 'ENTREGADO';
            }


            // ==========================================
            // FINALIZAR
            // ==========================================

            $sqlActualizar = "
                UPDATE pedido

                SET
                    Estado = ?,
                    Fecha_entregado = CURDATE()

                WHERE ID_pedido = ?
            ";

            $stmtActualizar =
                mysqli_prepare(
                    $this->conexion,
                    $sqlActualizar
                );

            if (!$stmtActualizar) {
                throw new Exception(
                    mysqli_error(
                        $this->conexion
                    )
                );
            }

            mysqli_stmt_bind_param(
                $stmtActualizar,
                "si",
                $estadoFinal,
                $idPedido
            );

            if (!mysqli_stmt_execute(
                $stmtActualizar
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtActualizar
                    )
                );
            }

            mysqli_stmt_close(
                $stmtActualizar
            );


            mysqli_commit($this->conexion);

            return $estadoFinal;


        } catch (Exception $e) {

            mysqli_rollback($this->conexion);

            throw $e;
        }
    }
}
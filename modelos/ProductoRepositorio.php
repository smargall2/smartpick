<?php

class ProductoRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    // ==================================================
    // OBTENER TODOS
    // ==================================================

    public function obtenerTodos()
    {
        $sql = "
            SELECT
                ID_producto,
                Tipo,
                Descripcion,
                Stock_actual,
                Stock_minimo,
                Categoria
            FROM producto
            ORDER BY Descripcion ASC
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            throw new Exception(
                mysqli_error($this->conexion)
            );
        }

        return mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );
    }


    // ==================================================
    // BUSCAR PRODUCTOS DE ALMACÉN
    // ==================================================

    public function buscarProductosAlmacen($texto)
    {
        $sql = "
            SELECT
                ID_producto,
                Tipo,
                Descripcion,
                Stock_actual,
                Stock_minimo,
                Categoria

            FROM producto

            WHERE UPPER(Tipo) = 'ALMACEN'

            AND (
                Descripcion LIKE ?
                OR Categoria LIKE ?
                OR CAST(ID_producto AS CHAR) LIKE ?
            )

            ORDER BY Descripcion ASC

            LIMIT 20
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

        $busqueda = '%' . $texto . '%';

        mysqli_stmt_bind_param(
            $stmt,
            "sss",
            $busqueda,
            $busqueda,
            $busqueda
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
    // BUSCAR POR ID
    // ==================================================

    public function buscarPorId($id)
    {
        $sql = "
            SELECT
                ID_producto,
                Tipo,
                Descripcion,
                Stock_actual,
                Stock_minimo,
                Categoria

            FROM producto

            WHERE ID_producto = ?

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

        $id = (int)$id;

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $id
        );

        mysqli_stmt_execute($stmt);

        $resultado =
            mysqli_stmt_get_result($stmt);

        $producto =
            mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);

        return $producto;
    }


    // ==================================================
    // OBTENER PRODUCTOS DE ALMACÉN
    // ==================================================

    public function obtenerProductosAlmacen()
    {
        $sql = "
            SELECT
                p.ID_producto,
                p.Descripcion,
                p.Tipo,
                p.Categoria,
                p.Stock_actual,
                p.Stock_minimo,
                z.ID_zona,
                z.Nombre AS Zona,
                z.Nivel,
                z.Estante

            FROM producto p

            LEFT JOIN esta e
                ON e.ID_producto = p.ID_producto

            LEFT JOIN zona z
                ON z.ID_zona = e.ID_zona

            WHERE UPPER(p.Tipo) = 'ALMACEN'

            ORDER BY p.Descripcion ASC
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            throw new Exception(
                mysqli_error($this->conexion)
            );
        }

        return mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );
    }


    // ==================================================
    // CREAR PRODUCTO DE ALMACÉN
    // ==================================================

    public function crearProductoAlmacen(
        $descripcion,
        $categoria,
        $stockActual,
        $stockMinimo,
        $idZona
    ) {
        try {

            mysqli_begin_transaction(
                $this->conexion
            );


            $descripcionNormalizada =
                preg_replace(
                    '/\s+/',
                    ' ',
                    trim($descripcion)
                );

            $stockActual = (int)$stockActual;
            $stockMinimo = (int)$stockMinimo;
            $idZona = (int)$idZona;


            // ==========================================
            // VERIFICAR DUPLICADO
            // ==========================================

            $sqlExiste = "
                SELECT
                    ID_producto,
                    Descripcion

                FROM producto

                WHERE UPPER(Tipo) = 'ALMACEN'

                AND LOWER(TRIM(Descripcion)) =
                    LOWER(TRIM(?))

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
                $descripcionNormalizada
            );

            mysqli_stmt_execute($stmtExiste);

            $resultadoExiste =
                mysqli_stmt_get_result(
                    $stmtExiste
                );

            $productoExistente =
                mysqli_fetch_assoc(
                    $resultadoExiste
                );

            mysqli_stmt_close($stmtExiste);


            if ($productoExistente) {

                throw new Exception(
                    'El producto "' .
                    $productoExistente['Descripcion'] .
                    '" ya está registrado. ' .
                    'Si necesitás cambiar sus datos, utilizá Modificar producto.'
                );
            }


            // ==========================================
            // VERIFICAR ZONA
            // ==========================================

            $sqlZona = "
                SELECT ID_zona
                FROM zona
                WHERE ID_zona = ?
                LIMIT 1
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

            mysqli_stmt_execute($stmtZona);

            $resultadoZona =
                mysqli_stmt_get_result($stmtZona);

            $zona =
                mysqli_fetch_assoc($resultadoZona);

            mysqli_stmt_close($stmtZona);


            if (!$zona) {

                throw new Exception(
                    "La ubicación seleccionada no existe."
                );
            }


            // ==========================================
            // CREAR PRODUCTO
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
                    'ALMACEN',
                    ?,
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

            mysqli_stmt_bind_param(
                $stmtProducto,
                "siis",
                $descripcionNormalizada,
                $stockActual,
                $stockMinimo,
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

            mysqli_stmt_close($stmtProducto);


            $idProducto =
                mysqli_insert_id(
                    $this->conexion
                );


            // ==========================================
            // ASIGNAR UBICACIÓN
            // ==========================================

            $sqlEsta = "
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

            $stmtEsta = mysqli_prepare(
                $this->conexion,
                $sqlEsta
            );

            if (!$stmtEsta) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtEsta,
                "ii",
                $idProducto,
                $idZona
            );

            if (!mysqli_stmt_execute(
                $stmtEsta
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtEsta
                    )
                );
            }

            mysqli_stmt_close($stmtEsta);


            mysqli_commit(
                $this->conexion
            );

            return $idProducto;


        } catch (Exception $e) {

            mysqli_rollback(
                $this->conexion
            );

            throw $e;
        }
    }


    // ==================================================
    // OBTENER PRODUCTO DE ALMACÉN POR ID
    // ==================================================

    public function obtenerProductoAlmacenPorId(
        $idProducto
    ) {
        $sql = "
            SELECT
                p.ID_producto,
                p.Descripcion,
                p.Categoria,
                p.Stock_actual,
                p.Stock_minimo,
                e.ID_zona,
                z.Nombre AS Zona,
                z.Nivel,
                z.Estante

            FROM producto p

            LEFT JOIN esta e
                ON e.ID_producto = p.ID_producto

            LEFT JOIN zona z
                ON z.ID_zona = e.ID_zona

            WHERE p.ID_producto = ?
              AND UPPER(p.Tipo) = 'ALMACEN'

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

        $idProducto = (int)$idProducto;

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $idProducto
        );

        mysqli_stmt_execute($stmt);

        $resultado =
            mysqli_stmt_get_result($stmt);

        $producto =
            mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);

        return $producto;
    }


    // ==================================================
    // MODIFICAR PRODUCTO
    // ==================================================

    public function modificarProductoAlmacen(
        $idProducto,
        $descripcion,
        $categoria,
        $stockMinimo,
        $idZona
    ) {
        try {

            mysqli_begin_transaction(
                $this->conexion
            );


            $idProducto = (int)$idProducto;
            $stockMinimo = (int)$stockMinimo;
            $idZona = (int)$idZona;

            $descripcionNormalizada =
                preg_replace(
                    '/\s+/',
                    ' ',
                    trim($descripcion)
                );


            // ==========================================
            // VERIFICAR PRODUCTO
            // ==========================================

            $sqlProducto = "
                SELECT ID_producto

                FROM producto

                WHERE ID_producto = ?
                  AND UPPER(Tipo) = 'ALMACEN'

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
                "i",
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
                    "El producto no existe."
                );
            }


            // ==========================================
            // VERIFICAR DUPLICADO
            // ==========================================

            $sqlDuplicado = "
                SELECT
                    ID_producto,
                    Descripcion

                FROM producto

                WHERE UPPER(Tipo) = 'ALMACEN'

                AND LOWER(TRIM(Descripcion)) =
                    LOWER(TRIM(?))

                AND ID_producto <> ?

                LIMIT 1
            ";

            $stmtDuplicado = mysqli_prepare(
                $this->conexion,
                $sqlDuplicado
            );

            if (!$stmtDuplicado) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtDuplicado,
                "si",
                $descripcionNormalizada,
                $idProducto
            );

            mysqli_stmt_execute(
                $stmtDuplicado
            );

            $resultadoDuplicado =
                mysqli_stmt_get_result(
                    $stmtDuplicado
                );

            $duplicado =
                mysqli_fetch_assoc(
                    $resultadoDuplicado
                );

            mysqli_stmt_close(
                $stmtDuplicado
            );


            if ($duplicado) {

                throw new Exception(
                    "Ya existe otro producto con ese nombre."
                );
            }


            // ==========================================
            // VERIFICAR ZONA
            // ==========================================

            $sqlZona = "
                SELECT ID_zona

                FROM zona

                WHERE ID_zona = ?

                LIMIT 1
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

            mysqli_stmt_execute($stmtZona);

            $resultadoZona =
                mysqli_stmt_get_result(
                    $stmtZona
                );

            $zona =
                mysqli_fetch_assoc(
                    $resultadoZona
                );

            mysqli_stmt_close($stmtZona);


            if (!$zona) {

                throw new Exception(
                    "La ubicación seleccionada no existe."
                );
            }


            // ==========================================
            // ACTUALIZAR DATOS
            // ==========================================

            $sqlActualizar = "
                UPDATE producto

                SET
                    Descripcion = ?,
                    Categoria = ?,
                    Stock_minimo = ?

                WHERE ID_producto = ?
                  AND UPPER(Tipo) = 'ALMACEN'
            ";

            $stmtActualizar = mysqli_prepare(
                $this->conexion,
                $sqlActualizar
            );

            if (!$stmtActualizar) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtActualizar,
                "ssii",
                $descripcionNormalizada,
                $categoria,
                $stockMinimo,
                $idProducto
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


            // ==========================================
            // ACTUALIZAR UBICACIÓN
            // ==========================================

            $sqlEsta = "
                INSERT INTO esta
                    (ID_producto, ID_zona)

                VALUES
                    (?, ?)

                ON DUPLICATE KEY UPDATE
                    ID_zona = VALUES(ID_zona)
            ";

            $stmtEsta = mysqli_prepare(
                $this->conexion,
                $sqlEsta
            );

            if (!$stmtEsta) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtEsta,
                "ii",
                $idProducto,
                $idZona
            );

            if (!mysqli_stmt_execute(
                $stmtEsta
            )) {

                throw new Exception(
                    mysqli_stmt_error(
                        $stmtEsta
                    )
                );
            }

            mysqli_stmt_close($stmtEsta);


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


    // ==================================================
    // OBTENER INVENTARIO DE ALMACÉN
    // ==================================================

    public function obtenerInventarioAlmacen()
    {
        $sql = "
            SELECT
                p.ID_producto,
                p.Descripcion,
                p.Categoria,
                p.Stock_actual,
                p.Stock_minimo,
                z.Nombre AS Zona,
                z.Nivel,
                z.Estante

            FROM producto p

            LEFT JOIN esta e
                ON e.ID_producto = p.ID_producto

            LEFT JOIN zona z
                ON z.ID_zona = e.ID_zona

            WHERE UPPER(p.Tipo) = 'ALMACEN'

            ORDER BY p.Descripcion ASC
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {

            throw new Exception(
                mysqli_error($this->conexion)
            );
        }

        return mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );
    }


    // ==================================================
    // REPONER STOCK
    // ==================================================

    public function reponerStock(
        $idProducto,
        $cantidad
    ) {
        $idProducto = (int)$idProducto;
        $cantidad = (int)$cantidad;


        if ($cantidad <= 0) {

            throw new Exception(
                "La cantidad a reponer debe ser mayor a cero."
            );
        }


        try {

            mysqli_begin_transaction(
                $this->conexion
            );


            // ==========================================
            // BLOQUEAR PRODUCTO
            // ==========================================

            $sqlProducto = "
                SELECT
                    ID_producto,
                    Descripcion,
                    Stock_actual

                FROM producto

                WHERE ID_producto = ?
                  AND UPPER(Tipo) = 'ALMACEN'

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
                "i",
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
                    "El producto seleccionado no existe."
                );
            }


            // ==========================================
            // SUMAR STOCK
            // ==========================================

            $sqlActualizar = "
                UPDATE producto

                SET Stock_actual =
                    Stock_actual + ?

                WHERE ID_producto = ?
            ";

            $stmtActualizar = mysqli_prepare(
                $this->conexion,
                $sqlActualizar
            );

            if (!$stmtActualizar) {
                throw new Exception(
                    mysqli_error($this->conexion)
                );
            }

            mysqli_stmt_bind_param(
                $stmtActualizar,
                "ii",
                $cantidad,
                $idProducto
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


            $nuevoStock =
                (int)$producto['Stock_actual']
                + $cantidad;


            mysqli_commit(
                $this->conexion
            );


            return [
                'producto' =>
                    $producto['Descripcion'],

                'stock_anterior' =>
                    (int)$producto['Stock_actual'],

                'cantidad' =>
                    $cantidad,

                'stock_nuevo' =>
                    $nuevoStock
            ];


        } catch (Exception $e) {

            mysqli_rollback(
                $this->conexion
            );

            throw $e;
        }
    }


    // ==================================================
    // OBTENER PAQUETES EN DEPÓSITO
    // ==================================================

    public function obtenerPaquetesDeposito()
    {
        $sql = "
            SELECT
                p.ID_producto,
                p.Descripcion,
                p.Categoria,

                pe.ID_pedido,
                pe.Fecha_ingresado,
                pe.Estado,

                per.CI,
                per.Nombre_completo AS Cliente,

                z.Nombre AS Zona,
                z.Nivel,
                z.Estante

            FROM producto p

            INNER JOIN contiene c
                ON c.ID_producto = p.ID_producto

            INNER JOIN pedido pe
                ON pe.ID_pedido = c.ID_pedido

            INNER JOIN recibe_entrega re
                ON re.ID_pedido = pe.ID_pedido

            INNER JOIN cliente cl
                ON cl.CI_cliente = re.CI_cliente

            INNER JOIN persona per
                ON per.CI = cl.CI_cliente

            LEFT JOIN esta e
                ON e.ID_producto = p.ID_producto

            LEFT JOIN zona z
                ON z.ID_zona = e.ID_zona

            WHERE UPPER(p.Tipo) = 'DEPOSITO'

            AND UPPER(pe.Estado) NOT IN (
                'RETIRADO',
                'ENTREGADO'
            )

            ORDER BY
                pe.Fecha_ingresado DESC,
                pe.ID_pedido DESC
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {

            throw new Exception(
                mysqli_error($this->conexion)
            );
        }

        return mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );
    }
}
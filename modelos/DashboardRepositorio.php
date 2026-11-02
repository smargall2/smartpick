<?php

class DashboardRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    // ==========================================
    // STOCK TOTAL
    // ==========================================

    public function obtenerStockTotal()
    {
        $sql = "
            SELECT COALESCE(SUM(Stock_actual), 0) AS total
            FROM producto
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            die(
                'Error al obtener stock total: ' .
                mysqli_error($this->conexion)
            );
        }

        $fila = mysqli_fetch_assoc($resultado);

        return $fila['total'];
    }


    // ==========================================
    // PEDIDOS PENDIENTES
    // ==========================================

    public function obtenerPedidosPendientes()
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM pedido
            WHERE LOWER(Estado) = 'pendiente'
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            die(
                'Error al obtener pedidos pendientes: ' .
                mysqli_error($this->conexion)
            );
        }

        $fila = mysqli_fetch_assoc($resultado);

        return $fila['total'];
    }


    // ==========================================
    // CANTIDAD DE OPERARIOS
    // ==========================================

    public function obtenerCantidadOperarios()
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM operario
            WHERE LOWER(Rol) = 'operario'
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            die(
                'Error al obtener cantidad de operarios: ' .
                mysqli_error($this->conexion)
            );
        }

        $fila = mysqli_fetch_assoc($resultado);

        return $fila['total'];
    }


    // ==========================================
    // PRODUCTOS CON STOCK BAJO
    // ==========================================

    public function obtenerStockBajo()
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM producto
            WHERE UPPER(Tipo) = 'ALMACEN'
              AND Stock_actual <= Stock_minimo
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            die(
                'Error al obtener stock bajo: ' .
                mysqli_error($this->conexion)
            );
        }

        $fila = mysqli_fetch_assoc($resultado);

        return (int)($fila['total'] ?? 0);
    }


    // ==========================================
    // ÚLTIMOS PEDIDOS
    // ==========================================

    public function obtenerPedidosRecientes()
    {
        $sql = "
            SELECT
                ID_pedido,
                Fecha_ingresado,
                Estado
            FROM pedido
            ORDER BY ID_pedido DESC
            LIMIT 5
        ";

        $resultado = mysqli_query(
            $this->conexion,
            $sql
        );

        if (!$resultado) {
            die(
                'Error al obtener pedidos recientes: ' .
                mysqli_error($this->conexion)
            );
        }

        return mysqli_fetch_all(
            $resultado,
            MYSQLI_ASSOC
        );
    }


    // ==========================================
    // REPORTES: STOCK POR CATEGORÍA
    // ==========================================
    public function obtenerStockPorCategoria()
    {
        $sql = "SELECT Categoria AS categoria,
                       COALESCE(SUM(Stock_actual), 0) AS total
                FROM producto
                WHERE UPPER(Tipo) = 'ALMACEN'
                GROUP BY Categoria
                ORDER BY categoria ASC";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception('Error al consultar stock por categoría: ' . mysqli_error($this->conexion));
        }
        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }

    // ==========================================
    // REPORTES: PRODUCTOS CON STOCK BAJO
    // ==========================================
    public function obtenerProductosStockBajo()
    {
        $sql = "SELECT ID_producto, Descripcion, Categoria,
                       Stock_actual, Stock_minimo
                FROM producto
                WHERE UPPER(Tipo) = 'ALMACEN'
                  AND Stock_actual <= Stock_minimo
                ORDER BY Stock_actual ASC, Descripcion ASC";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception('Error al consultar productos con stock bajo: ' . mysqli_error($this->conexion));
        }
        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }

    // ==========================================
    // REPORTES: PEDIDOS POR ESTADO
    // ==========================================
    public function obtenerPedidosPorEstado()
    {
        $sql = "SELECT Estado AS estado, COUNT(*) AS total
                FROM pedido
                GROUP BY Estado
                ORDER BY Estado ASC";

        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) {
            throw new Exception('Error al consultar pedidos por estado: ' . mysqli_error($this->conexion));
        }
        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }
}
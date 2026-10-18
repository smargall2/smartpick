<?php
/** API simulada SmartPick - POST /api/pedidos.php
 * Requiere PHP 8+, MySQLi y las clases actuales del proyecto.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function responder(int $codigo, string $estado, string $mensaje, array $extra = []): void {
    http_response_code($codigo);
    echo json_encode(array_merge(['estado' => $estado, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    responder(405, 'rechazado', 'Solo se admite POST.');
}

// Definir SMARTPICK_API_KEY en el entorno del servidor; nunca guardar una clave real en Git.
$claveConfigurada = getenv('SMARTPICK_API_KEY');
$claveRecibida = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!is_string($claveConfigurada) || strlen($claveConfigurada) < 24 ||
    !is_string($claveRecibida) || !hash_equals($claveConfigurada, $claveRecibida)) {
    responder(401, 'rechazado', 'API no autorizada o sin configurar.');
}

if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 16384) {
    responder(413, 'rechazado', 'El JSON supera el tamaño permitido.');
}

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada) || json_last_error() !== JSON_ERROR_NONE) {
    responder(400, 'rechazado', 'JSON inválido.');
}

$ci = $entrada['cliente_ci'] ?? null;
$nombre = $entrada['cliente_nombre'] ?? null;
$sku = $entrada['sku'] ?? null;
$cantidad = $entrada['cantidad'] ?? null;
$zona = $entrada['zona'] ?? null;

if (!is_string($ci) || !preg_match('/^[0-9]{6,20}$/D', $ci) ||
    !is_string($nombre) || trim($nombre) === '' || mb_strlen($nombre) > 100 ||
    filter_var($sku, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) === false ||
    filter_var($cantidad, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) === false ||
    filter_var($zona, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) === false) {
    responder(422, 'rechazado', 'Datos inválidos: cliente_ci, cliente_nombre, sku, cantidad y zona son obligatorios.');
}
$sku = (int)$sku;
$cantidad = (int)$cantidad;
$zona = (int)$zona;

try {
    require_once dirname(__DIR__) . '/conexion.php';
    require_once dirname(__DIR__) . '/modelos/PedidosRepositorio.php';
    if (!isset($conexion) || !($conexion instanceof mysqli)) {
        throw new RuntimeException('Conexión no disponible.');
    }
    mysqli_set_charset($conexion, 'utf8mb4');

    // La API opera con clientes previamente registrados, para respetar el modelo actual.
    $sqlCliente = 'SELECT p.Nombre_completo FROM cliente c INNER JOIN persona p ON p.CI = c.CI_cliente WHERE c.CI_cliente = ? LIMIT 1';
    $st = mysqli_prepare($conexion, $sqlCliente);
    mysqli_stmt_bind_param($st, 's', $ci);
    mysqli_stmt_execute($st);
    $cliente = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    if (!$cliente) responder(422, 'rechazado', 'El cliente no está registrado en SmartPick.');
    if (mb_strtolower(trim($cliente['Nombre_completo'])) !== mb_strtolower(trim($nombre))) {
        responder(422, 'rechazado', 'El nombre no coincide con el cliente registrado.');
    }

    // En esta versión el SKU corresponde al ID_producto y la zona al ID_zona.
    $sqlProducto = 'SELECT p.Tipo, p.Stock_actual, p.Descripcion, e.ID_zona FROM producto p LEFT JOIN esta e ON e.ID_producto = p.ID_producto WHERE p.ID_producto = ? LIMIT 1';
    $st = mysqli_prepare($conexion, $sqlProducto);
    mysqli_stmt_bind_param($st, 'i', $sku);
    mysqli_stmt_execute($st);
    $producto = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
    mysqli_stmt_close($st);
    if (!$producto || strtoupper((string)$producto['Tipo']) !== 'ALMACEN') {
        responder(422, 'rechazado', 'El SKU no corresponde a un producto de almacén.');
    }
    if ((int)$producto['ID_zona'] !== $zona) {
        responder(422, 'rechazado', 'La zona no coincide con la ubicación del producto.');
    }
    if ((int)$producto['Stock_actual'] < $cantidad) {
        responder(200, 'pendiente', 'Stock insuficiente. No se registró el pedido.', [
            'id_pedido' => null, 'id_picking' => null, 'stock_disponible' => (int)$producto['Stock_actual']
        ]);
    }

    // Reutiliza la transacción y validación de stock del repositorio existente.
    $repositorio = new PedidoRepositorio($conexion);
    try {
        $idPedido = $repositorio->crearPedidoAlmacen($ci, [['id_producto'=>$sku, 'cantidad'=>$cantidad]]);
    } catch (Throwable $e) {
        // Si otro pedido consumió el stock entre la consulta y el INSERT, no se crea nada.
        if (stripos($e->getMessage(), 'stock') !== false) {
            responder(200, 'pendiente', 'Stock insuficiente al registrar. No se creó el pedido.', ['id_pedido'=>null, 'id_picking'=>null]);
        }
        throw $e;
    }
    responder(201, 'aceptado', 'Pedido registrado correctamente y pendiente de asignación.', [
        'id_pedido' => (int)$idPedido,
        'id_picking' => null,
        'estado_pedido' => 'PENDIENTE'
    ]);
} catch (Throwable $e) {
    error_log('SmartPick API: ' . $e->getMessage());
    responder(500, 'error', 'Error interno al procesar el pedido.');
}

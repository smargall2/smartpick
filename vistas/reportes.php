<?php
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    http_response_code(403);
    exit('Acceso denegado.');
}
$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reportes | SmartPick</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/dashboard.css">
<style>
.reportes-contenido{padding:30px 40px;background:#f4f5f8;min-height:calc(100vh - 135px)}
.reportes-intro{margin-bottom:25px}.reportes-intro h2{font-family:'Bebas Neue',sans-serif;font-size:34px;color:#112f4e;margin:4px 0}.reportes-intro p{color:#758195}
.reportes-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:18px;margin-bottom:22px}
.reportes-card,.reportes-panel{background:#fff;border-radius:12px;padding:23px;box-shadow:0 2px 12px rgba(20,45,75,.04)}
.reportes-card span{font-size:13px;color:#6b7b8c}.reportes-card strong{display:block;font-size:29px;color:#173957;margin-top:8px}
.reportes-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(330px,1fr));gap:20px;margin-bottom:22px}
.reportes-panel h3{font-family:'Bebas Neue',sans-serif;font-size:25px;color:#143655;margin-bottom:12px}
.reportes-chart{position:relative;height:280px}.reportes-tabla{width:100%;border-collapse:collapse;font-size:13px}
.reportes-tabla th,.reportes-tabla td{text-align:left;padding:12px 9px;border-bottom:1px solid #e8edf2}
.reportes-tabla th{color:#163755}.reportes-nota{font-size:12px;color:#718092;margin-top:12px}
.reportes-acciones{display:flex;gap:10px;flex-wrap:wrap;margin:15px 0}.reportes-boton{border:0;border-radius:8px;background:#245c9b;color:#fff;padding:11px 16px;font-family:inherit;font-weight:600;cursor:pointer}
.sensores-lecturas{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin:15px 0}.sensor-lectura{padding:14px;background:#f4f7f9;border-radius:9px}.sensor-lectura strong{display:block;color:#173957;font-size:20px;margin-top:6px}.sensor-lectura span{font-size:12px;color:#6b7b8c}.sensor-lectura small{display:block;color:#6b7b8c;margin-top:4px}.sensor-alerta{color:#b45309!important}.sensor-normal{color:#087f65!important}@media(max-width:700px){.reportes-contenido{padding:18px}.reportes-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="dashboard">
    <!-- ==========================================
         SIDEBAR
    =========================================== -->

    <aside class="sidebar">


        <!-- LOGO -->

        <div class="sidebar-logo">

            <img src="img/logoSinFondo.png" alt="SmartPick">

        </div>



        <!-- NAVEGACIÓN -->

        <nav class="sidebar-menu">

        <!-- DASHBOARD -->

            <a href="index.php?ruta=dashboard" class="menu-item">

                <i class="bi bi-grid"></i>

                <span>Dashboard</span>

            </a>

            <!-- CLIENTES -->

            <?php if (strtolower($_SESSION['usuario']['rol']) === 'administrador'): ?>

            <a href="index.php?ruta=clientes" class="menu-item">
                
                <i class="bi bi-person-vcard"></i>
        
                <span>Clientes</span>
            </a>

            <?php endif; ?>

            <!-- INGRESAR PAQUETE -->

            <?php if (strtolower($_SESSION['usuario']['rol']) === 'administrador'): ?>

            <a href="index.php?ruta=ingresar-paquete" class="menu-item">
                
                <i class="bi bi-box-arrow-in-down"></i>
                
                <span>Ingresar paquete</span>
            </a>

            <?php endif; ?>

            <!-- PRODUCTOS -->

            <?php if (strtolower($_SESSION['usuario']['rol']) === 'administrador'): ?>

            <a href="index.php?ruta=productos" class="menu-item">
                
                <i class="bi bi-box-seam"></i>
                
                <span>Productos</span>
            </a>

            <?php endif; ?>

            <!-- ZONAS -->

            <a href="index.php?ruta=zonas" class="menu-item">
                <i class="bi bi-geo-alt"></i>
                <span>Zonas</span>
            </a>

            <!-- INVENTARIO -->

            <?php if (strtolower($_SESSION['usuario']['rol']) === 'administrador'): ?>

            <a href="index.php?ruta=inventario" class="menu-item">
                <i class="bi bi-boxes"></i>
                <span>Inventario</span>
            </a>

            <?php endif; ?>

            <!-- PEDIDOS -->

            <a href="index.php?ruta=pedidos" class="menu-item">

                <i class="bi bi-clipboard-check"></i>

                <span>Pedidos</span>

            </a>

            <!-- OPERARIOS -->

            <?php if (strtolower($usuario['rol']) === 'administrador'): ?>
            <a href="index.php?ruta=operarios" class="menu-item">

                <i class="bi bi-people"></i>

                <span>Operarios</span>

            </a>
            <?php endif; ?>

            <!-- PICKING -->

            <a href="index.php?ruta=picking" class="menu-item">

                <i class="bi bi-signpost-2"></i>

                <span>Picking</span>

            </a>

            <!-- REPORTES -->

            <?php if (strtolower($usuario['rol']) === 'administrador'): ?>
            <a href="index.php?ruta=reportes" class="menu-item activo">

                <i class="bi bi-bar-chart"></i>

                <span>Reportes</span>

            </a>
            <?php endif; ?>


        </nav>



        <!-- LOGOUT -->

        <div class="sidebar-footer">

            <a
                href="index.php?ruta=logout"
                class="menu-item cerrar-sesion"
            >

                <i class="bi bi-box-arrow-left"></i>

                <span>Cerrar sesión</span>

            </a>

        </div>


    </aside>
<main class="main-content">
<header class="topbar"><div class="titulo-dashboard"><h1>REPORTES</h1><p>Indicadores de inventario y pedidos</p></div><div class="usuario-header"><div class="usuario-icono"><i class="bi bi-person"></i></div><div class="usuario-info"><strong><?= htmlspecialchars($usuario['nombre']) ?></strong><span><?= htmlspecialchars($usuario['rol']) ?></span></div></div></header>
<section class="reportes-contenido">
<div class="reportes-acciones"><button type="button" class="reportes-boton" id="descargarPDF"><i class="bi bi-file-earmark-pdf"></i> Descargar reporte PDF</button></div>
<div class="reportes-intro"><span style="color:#50a2a7;font-weight:700">ANÁLISIS OPERATIVO</span><h2>REPORTES DE SMARTPICK</h2><p>Datos actuales de la base de datos del depósito.</p></div>
<div class="reportes-cards">
<div class="reportes-card"><span>Unidades en stock</span><strong><?= (int)$stockTotal ?></strong></div>
<div class="reportes-card"><span>Pedidos pendientes</span><strong><?= (int)$pedidosPendientes ?></strong></div>
<div class="reportes-card"><span>Productos con stock bajo</span><strong><?= count($productosStockBajo) ?></strong></div>
</div>
<div class="reportes-grid">
<div class="reportes-panel"><h3>STOCK POR CATEGORÍA</h3><div class="reportes-chart"><canvas id="graficoStock"></canvas></div></div>
<div class="reportes-panel"><h3>PEDIDOS POR ESTADO</h3><div class="reportes-chart"><canvas id="graficoPedidos"></canvas></div></div>
</div>
<div class="reportes-panel"><h3>PRODUCTOS QUE NECESITAN REPOSICIÓN</h3>
<div style="overflow-x:auto"><table class="reportes-tabla"><thead><tr><th>ID</th><th>Producto</th><th>Categoría</th><th>Stock</th><th>Mínimo</th></tr></thead><tbody>
<?php if (empty($productosStockBajo)): ?><tr><td colspan="5">No hay productos con stock bajo.</td></tr><?php else: ?>
<?php foreach ($productosStockBajo as $producto): ?><tr><td>#<?= (int)$producto['ID_producto'] ?></td><td><?= htmlspecialchars($producto['Descripcion']) ?></td><td><?= htmlspecialchars($producto['Categoria']) ?></td><td><?= (int)$producto['Stock_actual'] ?></td><td><?= (int)$producto['Stock_minimo'] ?></td></tr><?php endforeach; ?><?php endif; ?>
</tbody></table></div></div>
<div class="reportes-panel" style="margin-top:22px">
<h3>SENSORES DEL DEPÓSITO (SIMULACIÓN)</h3>
<p class="reportes-nota">Valores generados con JavaScript para demostrar un sistema de monitoreo. No corresponden a sensores físicos ni se guardan en la base de datos.</p>
<div class="reportes-acciones"><button type="button" class="reportes-boton" id="actualizarSensores"><i class="bi bi-arrow-clockwise"></i> Simular nueva lectura</button></div>
<div id="lecturasSensores" class="sensores-lecturas"></div>
<div class="reportes-chart"><canvas id="graficoSensores"></canvas></div>
<p class="reportes-nota">Rangos de demostración: temperatura entre 15 y 30 °C; humedad entre 40 y 80 %. Los avisos se basan en límites ilustrativos, no en especificaciones reales del depósito.</p>
</div>
</section>
</main></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
const stockPorCategoria = <?= json_encode($stockPorCategoria, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const pedidosPorEstado = <?= json_encode($pedidosPorEstado, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
new Chart(document.getElementById('graficoStock'), {type:'bar',data:{labels:stockPorCategoria.map(item=>item.categoria),datasets:[{label:'Unidades',data:stockPorCategoria.map(item=>Number(item.total)),backgroundColor:'#50a2a7'}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}});
new Chart(document.getElementById('graficoPedidos'), {type:'doughnut',data:{labels:pedidosPorEstado.map(item=>item.estado),datasets:[{data:pedidosPorEstado.map(item=>Number(item.total)),backgroundColor:['#245c9b','#50a2a7','#ed474a','#f5bd56','#9d83c5','#6d8297']}]},options:{responsive:true,maintainAspectRatio:false}});
// Lecturas ficticias para practicar objetos, arrays, forEach y manipulación del DOM.
const zonasSensores = ['Zona A', 'Zona B', 'Zona C', 'Zona D'];
let graficoSensores;
let lecturasActuales = [];

function generarLecturas() {
    const lecturas = [];
    zonasSensores.forEach(function(zona) {
        lecturas.push({
            zona: zona,
            temperatura: Math.round((15 + Math.random() * 15) * 10) / 10,
            humedad: Math.round(40 + Math.random() * 40)
        });
    });
    return lecturas;
}

function mostrarLecturas(lecturas) {
    lecturasActuales = lecturas;
    const contenedor = document.getElementById('lecturasSensores');
    contenedor.innerHTML = '';
    lecturas.forEach(function(lectura) {
        const tarjeta = document.createElement('div');
        tarjeta.className = 'sensor-lectura';
        const titulo = document.createElement('span');
        titulo.textContent = lectura.zona;
        const temperatura = document.createElement('strong');
        temperatura.textContent = lectura.temperatura + ' °C';
        const humedad = document.createElement('small');
        humedad.textContent = 'Humedad: ' + lectura.humedad + ' %';
        const aviso = document.createElement('small');
        const fueraDeRango = lectura.temperatura > 25 || lectura.humedad > 70;
        aviso.textContent = fueraDeRango ? 'Aviso simulado: revisar zona' : 'Lectura simulada normal';
        aviso.className = fueraDeRango ? 'sensor-alerta' : 'sensor-normal';
        tarjeta.appendChild(titulo);
        tarjeta.appendChild(temperatura);
        tarjeta.appendChild(humedad);
        tarjeta.appendChild(aviso);
        contenedor.appendChild(tarjeta);
    });

    const etiquetas = lecturas.map(function(lectura) { return lectura.zona; });
    const temperaturas = lecturas.map(function(lectura) { return lectura.temperatura; });
    const humedades = lecturas.map(function(lectura) { return lectura.humedad; });
    if (graficoSensores) {
        graficoSensores.destroy();
    }
    graficoSensores = new Chart(document.getElementById('graficoSensores'), {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [
                {label: 'Temperatura (°C)', data: temperaturas, backgroundColor: '#ed474a'},
                {label: 'Humedad (%)', data: humedades, backgroundColor: '#50a2a7'}
            ]
        },
        options: {responsive: true, maintainAspectRatio: false, scales: {y: {beginAtZero: true}}}
    });
}

// Exporta los datos actuales, incluyendo la última lectura simulada.
document.getElementById('descargarPDF').addEventListener('click', function() {
    if (!window.jspdf || !window.jspdf.jsPDF) {
        alert('No se pudo cargar la biblioteca para generar el PDF. Revisá la conexión a Internet.');
        return;
    }

    const pdf = new window.jspdf.jsPDF();
    if (typeof pdf.autoTable !== 'function') {
        alert('No se pudo cargar la biblioteca de tablas del PDF.');
        return;
    }

    const fecha = new Date();
    const fechaTexto = fecha.toLocaleString('es-UY');
    pdf.setFontSize(18);
    pdf.text('SmartPick - Reporte operativo', 14, 18);
    pdf.setFontSize(10);
    pdf.text('Fecha de generacion: ' + fechaTexto, 14, 26);
    pdf.text('Stock total: <?= (int)$stockTotal ?> unidades', 14, 34);
    pdf.text('Pedidos pendientes: <?= (int)$pedidosPendientes ?>', 14, 40);
    pdf.text('Productos con stock bajo: <?= count($productosStockBajo) ?>', 14, 46);

    let posicion = 54;
    pdf.autoTable({
        startY: posicion,
        head: [['Categoria', 'Unidades en stock']],
        body: stockPorCategoria.map(function(item) {
            return [String(item.categoria), String(item.total)];
        }),
        theme: 'striped'
    });
    posicion = pdf.lastAutoTable.finalY + 10;
    pdf.autoTable({
        startY: posicion,
        head: [['Estado del pedido', 'Cantidad']],
        body: pedidosPorEstado.map(function(item) {
            return [String(item.estado), String(item.total)];
        }),
        theme: 'striped'
    });

    const productosBajos = <?= json_encode(array_map(function($producto) { return [ (string)$producto['ID_producto'], (string)$producto['Descripcion'], (string)$producto['Categoria'], (string)$producto['Stock_actual'], (string)$producto['Stock_minimo'] ]; }, $productosStockBajo), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    posicion = pdf.lastAutoTable.finalY + 10;
    pdf.autoTable({
        startY: posicion,
        head: [['ID', 'Producto', 'Categoria', 'Stock', 'Minimo']],
        body: productosBajos,
        theme: 'striped',
        styles: {fontSize: 8}
    });

    posicion = pdf.lastAutoTable.finalY + 10;
    pdf.autoTable({
        startY: posicion,
        head: [['Zona (simulada)', 'Temperatura (C)', 'Humedad (%)']],
        body: lecturasActuales.map(function(lectura) {
            return [lectura.zona, String(lectura.temperatura), String(lectura.humedad)];
        }),
        theme: 'striped'
    });

    posicion = pdf.lastAutoTable.finalY + 8;
    if (posicion > 275) {
        pdf.addPage();
        posicion = 18;
    }
    pdf.setFontSize(9);
    pdf.text('Sensores: valores simulados, no obtenidos de dispositivos fisicos.', 14, posicion);
    pdf.save('reporte_smartpick.pdf');
});

document.getElementById('actualizarSensores').addEventListener('click', function() {
    mostrarLecturas(generarLecturas());
});
mostrarLecturas(generarLecturas());
</script>
</body></html>

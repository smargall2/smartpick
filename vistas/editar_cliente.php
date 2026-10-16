<?php
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') {
    http_response_code(403);
    exit('Acceso denegado');
}
function eCliente($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar cliente | SmartPick</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/clientes.css">
    <link rel="stylesheet" href="css/editar-cliente.css">
</head>
<body>
    <div class="dashboard">
    <aside class="sidebar">


        <!-- LOGO -->

        <div class="sidebar-logo">

            <img src="/proyecto/smartpick/img/logoSinFondo.png" alt="SmartPick">

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

            <a href="index.php?ruta=clientes" class="menu-item activo">
                
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

            <a href="index.php?ruta=operarios" class="menu-item">

                <i class="bi bi-people"></i>

                <span>Operarios</span>

            </a>

            <!-- PICKING -->

            <a href="index.php?ruta=picking" class="menu-item">

                <i class="bi bi-signpost-2"></i>

                <span>Picking</span>

            </a>

            <!-- REPORTES -->

            <a href="#" class="menu-item">

                <i class="bi bi-bar-chart"></i>

                <span>Reportes</span>

            </a>


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
<header class="topbar"><div class="titulo-dashboard"><h1>EDITAR CLIENTE</h1><p>Administración de clientes registrados</p></div><div class="usuario-header"><div class="usuario-icono"><i class="bi bi-person"></i></div><div class="usuario-info"><strong><?= eCliente($_SESSION['usuario']['nombre'] ?? '') ?></strong><span><?= eCliente($_SESSION['usuario']['rol'] ?? '') ?></span></div></div></header>
<div class="editar-pagina">
        <div style="width:100%;max-width:760px">
            <a class="editar-volver" href="index.php?ruta=clientes"><i class="bi bi-arrow-left"></i> VOLVER A CLIENTES</a>
            <section class="editar">
                <header class="editar-encabezado">
                    <span class="editar-etiqueta"><i class="bi bi-person-gear"></i> GESTIÓN DE CLIENTES</span>
                    <h1>EDITAR CLIENTE</h1>
                    <p>Actualizá los datos del cliente. Su cédula de identidad no puede modificarse.</p>
                </header>
                <form class="editar-formulario" action="index.php?ruta=guardar-edicion-cliente" method="post">
                    <div class="editar-campos">
                        <div class="editar-campo">
                            <label for="ci">Cédula de identidad</label>
                            <input id="ci" name="ci" value="<?= eCliente($clienteEditar['CI_cliente']) ?>" readonly>
                            <span class="editar-ayuda">Este dato no se puede modificar.</span>
                        </div>
                        <div class="editar-campo">
                            <label for="telefono">Teléfono</label>
                            <input id="telefono" name="telefono" maxlength="30" value="<?= eCliente($clienteEditar['Telefono'] ?? '') ?>">
                        </div>
                        <div class="editar-campo ancho-completo">
                            <label for="nombre">Nombre completo</label>
                            <input id="nombre" name="nombre" required maxlength="100" value="<?= eCliente($clienteEditar['Nombre_completo']) ?>">
                        </div>
                        <div class="editar-campo ancho-completo">
                            <label for="mail">Correo electrónico</label>
                            <input id="mail" name="mail" type="email" maxlength="100" value="<?= eCliente($clienteEditar['Mail'] ?? '') ?>">
                        </div>
                        <div class="editar-campo ancho-completo">
                            <label for="direccion">Dirección</label>
                            <input id="direccion" name="direccion" maxlength="150" value="<?= eCliente($clienteEditar['Direccion'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="editar-acciones">
                        <a class="editar-boton editar-boton-secundario" href="index.php?ruta=clientes"><i class="bi bi-x-lg"></i> CANCELAR</a>
                        <button class="editar-boton editar-boton-principal" type="submit"><i class="bi bi-check2-circle"></i> GUARDAR CAMBIOS</button>
                    </div>
                </form>
            </section>
        </div>
</div>
    </main>
    </div>
</body>
</html>

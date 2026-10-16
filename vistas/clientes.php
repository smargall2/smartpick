<?php

    if (!isset($_SESSION['usuario'])) {
        header("Location: index.php?ruta=login");
        exit;
    }

    $usuario = $_SESSION['usuario'];

    $mensaje = $_SESSION['mensaje_cliente'] ?? null;

    $error = $_SESSION['error_cliente'] ?? null;

    unset($_SESSION['mensaje_cliente']);
    unset($_SESSION['error_cliente']);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Clientes | SmartPick</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="css/clientes.css"
    >

</head>

<body>

<div class="dashboard">

    <!-- ==========================================
         SIDEBAR
    =========================================== -->

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


    <!-- CONTENIDO -->

    <main class="main-content">

        <header class="topbar">

            <div class="titulo-dashboard">

                <h1>CLIENTES</h1>

                <p>
                    Administración de clientes registrados
                </p>

            </div>


            <div class="usuario-header">

                <div class="usuario-icono">
                    <i class="bi bi-person"></i>
                </div>

                <div class="usuario-info">

                    <strong>
                        <?= htmlspecialchars($usuario['nombre']) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($usuario['rol']) ?>
                    </span>

                </div>

            </div>

        </header>


        <section class="clientes-contenido">


            <?php if ($mensaje): ?>

                <div class="mensaje-exito">

                    <i class="bi bi-check-circle"></i>

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>


            <?php if ($error): ?>

                <div class="mensaje-error">

                    <i class="bi bi-exclamation-circle"></i>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <div class="clientes-cabecera">

                <div>

                    <span class="etiqueta-seccion">
                        GESTIÓN DE CLIENTES
                    </span>

                    <h2>
                        CLIENTES REGISTRADOS
                    </h2>

                    <p>
                        Consultá y administrá los clientes de SmartPick.
                    </p>

                </div>


                <a
                    href="index.php?ruta=nuevo-cliente"
                    class="btn-nuevo-cliente"
                >

                    <i class="bi bi-person-plus"></i>

                    NUEVO CLIENTE

                </a>

            </div>


            <!-- BUSCADOR -->

            <div class="clientes-filtros">

                <div class="buscador">

                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        id="buscarCliente"
                        placeholder="Buscar por nombre, CI o correo..."
                    >

                </div>

            </div>


            <!-- TABLA -->

            <div class="panel-clientes">

                <div class="tabla-responsive">

                    <table class="tabla-clientes">

                        <thead>

                            <tr>

                                <th>CI</th>
                                <th>NOMBRE</th>
                                <th>TELÉFONO</th>
                                <th>CORREO</th>
                                <th>DIRECCIÓN</th>
                                <th>ACCIONES</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php if (!empty($clientes)): ?>

                            <?php foreach ($clientes as $cliente): ?>

                                <tr
                                    class="fila-cliente"
                                    data-busqueda="<?=
                                        htmlspecialchars(
                                            strtolower(
                                                $cliente['CI_cliente']
                                                . ' '
                                                . $cliente['Nombre_completo']
                                                . ' '
                                                . ($cliente['Mail'] ?? '')
                                            )
                                        )
                                    ?>"
                                >

                                    <td>

                                        <strong>
                                            <?= htmlspecialchars(
                                                $cliente['CI_cliente']
                                            ) ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $cliente['Nombre_completo']
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $cliente['Telefono'] ?: '-'
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $cliente['Mail'] ?: '-'
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $cliente['Direccion'] ?: '-'
                                        ) ?>

                                    </td>
                                    <td>
                                        <a href="index.php?ruta=editar-cliente&ci=<?= urlencode($cliente['CI_cliente']) ?>" title="Editar cliente" style="margin-right:12px;color:#205b9d"><i class="bi bi-pencil-square"></i></a>
                                        <form method="post" action="index.php?ruta=eliminar-cliente" style="display:inline" onsubmit="return confirm('¿Eliminar este cliente? Si tiene pedidos asociados no será posible.');">
                                            <input type="hidden" name="ci" value="<?= htmlspecialchars($cliente['CI_cliente'], ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" title="Eliminar cliente" style="background:none;border:0;cursor:pointer;color:#b42318"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                            <?php endforeach; ?>


                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="tabla-vacia"
                                >

                                    <i class="bi bi-people"></i>

                                    <p>
                                        No hay clientes registrados.
                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </main>

</div>


<script>

const buscarCliente =
    document.getElementById('buscarCliente');


buscarCliente.addEventListener(
    'input',
    function () {

        const texto =
            this.value
                .trim()
                .toLowerCase();

        const filas =
            document.querySelectorAll(
                '.fila-cliente'
            );


        filas.forEach(fila => {

            const datos =
                fila.dataset.busqueda;

            fila.style.display =
                datos.includes(texto)
                    ? ''
                    : 'none';

        });

    }
);

</script>

</body>

</html>
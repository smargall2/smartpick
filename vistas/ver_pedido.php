<?php

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?ruta=login");
    exit;
}


$usuario = $_SESSION['usuario'];


$mensaje =
    $_SESSION['mensaje_pedido'] ?? null;


$error =
    $_SESSION['error_pedido'] ?? null;


unset($_SESSION['mensaje_pedido']);
unset($_SESSION['error_pedido']);


$estado =
    strtolower($pedido['Estado']);


$claseEstado =
    'estado-pendiente';


if (
    $estado === 'preparando' ||
    $estado === 'en proceso'
) {

    $claseEstado =
        'estado-proceso';

} elseif (
    $estado === 'listo_para_validar'
) {

    $claseEstado =
        'estado-validar';

} elseif (
    $estado === 'entregado' ||
    $estado === 'completado' ||
    $estado === 'retirado'
) {

    $claseEstado =
        'estado-completado';

} elseif (
    $estado === 'almacenado'
) {

    $claseEstado =
        'estado-almacenado';

} elseif (
    $estado === 'pendiente_retiro'
) {

    $claseEstado =
        'estado-proceso';
}


$esAdministrador =
    strtolower(
        $_SESSION['usuario']['rol']
    ) === 'administrador';


$estaListo =
    strtoupper(
        $pedido['Estado']
    ) === 'LISTO_PARA_VALIDAR';


$puedeAsignarOperario =
    $esAdministrador
    &&
    in_array(
        strtolower($pedido['Estado']),
        [
            'pendiente',
            'pendiente_retiro',
            'preparando'
        ],
        true
    );

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Pedido #<?= (int)$pedido['ID_pedido'] ?> | SmartPick
    </title>


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
        href="css/ver-pedido.css"
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

            <img
                src="/proyecto/smartpick/img/logoSinFondo.png"
                alt="SmartPick"
            >

        </div>



        <!-- NAVEGACIÓN -->

        <nav class="sidebar-menu">


            <!-- DASHBOARD -->

            <a
                href="index.php?ruta=dashboard"
                class="menu-item"
            >

                <i class="bi bi-grid"></i>

                <span>Dashboard</span>

            </a>


            <!-- CLIENTES -->

            <?php if ($esAdministrador): ?>

                <a
                    href="index.php?ruta=clientes"
                    class="menu-item"
                >

                    <i class="bi bi-person-vcard"></i>

                    <span>Clientes</span>

                </a>

            <?php endif; ?>


            <!-- INGRESAR PAQUETE -->

            <?php if ($esAdministrador): ?>

                <a
                    href="index.php?ruta=ingresar-paquete"
                    class="menu-item"
                >

                    <i class="bi bi-box-arrow-in-down"></i>

                    <span>Ingresar paquete</span>

                </a>

            <?php endif; ?>


            <!-- PRODUCTOS -->

            <?php if ($esAdministrador): ?>

                <a
                    href="index.php?ruta=productos"
                    class="menu-item"
                >

                    <i class="bi bi-box-seam"></i>

                    <span>Productos</span>

                </a>

            <?php endif; ?>


            <!-- ZONAS -->

            <a
                href="index.php?ruta=zonas"
                class="menu-item"
            >

                <i class="bi bi-geo-alt"></i>

                <span>Zonas</span>

            </a>


            <!-- INVENTARIO -->

            <?php if ($esAdministrador): ?>

                <a
                    href="index.php?ruta=inventario"
                    class="menu-item"
                >

                    <i class="bi bi-boxes"></i>

                    <span>Inventario</span>

                </a>

            <?php endif; ?>


            <!-- PEDIDOS -->

            <a
                href="index.php?ruta=pedidos"
                class="menu-item activo"
            >

                <i class="bi bi-clipboard-check"></i>

                <span>Pedidos</span>

            </a>


            <!-- OPERARIOS -->

            <a
                href="#"
                class="menu-item"
            >

                <i class="bi bi-people"></i>

                <span>Operarios</span>

            </a>


            <!-- PICKING -->

            <a
                href="index.php?ruta=picking"
                class="menu-item"
            >

                <i class="bi bi-signpost-2"></i>

                <span>Picking</span>

            </a>


            <!-- REPORTES -->

            <a
                href="#"
                class="menu-item"
            >

                <i class="bi bi-bar-chart"></i>

                <span>Reportes</span>

            </a>


        </nav>



        <!-- CERRAR SESIÓN -->

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



    <!-- ==========================================
         CONTENIDO
    =========================================== -->

    <main class="main-content">


        <!-- TOPBAR -->

        <header class="topbar">


            <div class="titulo-dashboard">

                <h1>
                    PEDIDO #<?= (int)$pedido['ID_pedido'] ?>
                </h1>

                <p>
                    Detalle y asignación del pedido
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



        <!-- ==========================================
             DETALLE
        =========================================== -->

        <section class="detalle-contenido">


            <a
                href="index.php?ruta=pedidos"
                class="volver"
            >

                <i class="bi bi-arrow-left"></i>

                Volver a pedidos

            </a>



            <!-- MENSAJE DE ÉXITO -->

            <?php if ($mensaje): ?>

                <div class="mensaje-exito">

                    <i class="bi bi-check-circle"></i>

                    <?= htmlspecialchars($mensaje) ?>

                </div>

            <?php endif; ?>



            <!-- MENSAJE DE ERROR -->

            <?php if ($error): ?>

                <div class="mensaje-error">

                    <i class="bi bi-exclamation-circle"></i>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>



            <!-- ======================================
                 INFORMACIÓN GENERAL + CLIENTE
            ======================================= -->

            <div class="grid-detalle">


                <!-- INFORMACIÓN DEL PEDIDO -->

                <div class="tarjeta">


                    <div class="encabezado-tarjeta">

                        <i class="bi bi-receipt"></i>

                        <div>

                            <h2>
                                INFORMACIÓN DEL PEDIDO
                            </h2>

                            <p>
                                Datos generales
                            </p>

                        </div>

                    </div>


                    <div class="datos-grid">


                        <!-- ID -->

                        <div class="dato">

                            <span>
                                ID PEDIDO
                            </span>

                            <strong>
                                #<?= (int)$pedido['ID_pedido'] ?>
                            </strong>

                        </div>



                        <!-- FECHA INGRESO -->

                        <div class="dato">

                            <span>
                                FECHA DE INGRESO
                            </span>

                            <strong>

                                <?php

                                if (!empty($pedido['Fecha_ingresado'])) {

                                    echo date(
                                        'd/m/Y',
                                        strtotime(
                                            $pedido['Fecha_ingresado']
                                        )
                                    );

                                } else {

                                    echo '-';
                                }

                                ?>

                            </strong>

                        </div>



                        <!-- ESTADO -->

                        <div class="dato">

                            <span>
                                ESTADO
                            </span>

                            <strong
                                class="estado <?= $claseEstado ?>"
                            >

                                <?php

                                $estadoMostrar =
                                    strtoupper(
                                        $pedido['Estado']
                                    );


                                if (
                                    $estadoMostrar ===
                                    'LISTO_PARA_VALIDAR'
                                ) {

                                    echo 'LISTO PARA VALIDAR';

                                } elseif (
                                    $estadoMostrar ===
                                    'PENDIENTE_RETIRO'
                                ) {

                                    echo 'PENDIENTE DE RETIRO';

                                } else {

                                    echo htmlspecialchars(
                                        $pedido['Estado']
                                    );
                                }

                                ?>

                            </strong>

                        </div>



                        <!-- FECHA ENTREGA -->

                        <div class="dato">

                            <span>
                                FECHA DE ENTREGA
                            </span>

                            <strong>

                                <?php

                                if (!empty($pedido['Fecha_entregado'])) {

                                    echo date(
                                        'd/m/Y',
                                        strtotime(
                                            $pedido['Fecha_entregado']
                                        )
                                    );

                                } else {

                                    echo '-';
                                }

                                ?>

                            </strong>

                        </div>


                    </div>


                </div>



                <!-- ======================================
                     CLIENTE
                ======================================= -->

                <div class="tarjeta">


                    <div class="encabezado-tarjeta">

                        <i class="bi bi-person"></i>

                        <div>

                            <h2>
                                CLIENTE
                            </h2>

                            <p>
                                Responsable del pedido
                            </p>

                        </div>

                    </div>


                    <div class="cliente-detalle">


                        <div class="avatar-cliente">

                            <i class="bi bi-person"></i>

                        </div>


                        <div>

                            <strong>

                                <?= htmlspecialchars(
                                    $pedido['Cliente']
                                ) ?>

                            </strong>


                            <span>

                                CI:

                                <?= htmlspecialchars(
                                    $pedido['CI_cliente']
                                ) ?>

                            </span>


                            <?php if (!empty($pedido['Mail_cliente'])): ?>

                                <small>

                                    <?= htmlspecialchars(
                                        $pedido['Mail_cliente']
                                    ) ?>

                                </small>

                            <?php endif; ?>


                        </div>


                    </div>


                </div>


            </div>



            <!-- ======================================
                 PRODUCTOS
            ======================================= -->

            <div class="tarjeta tarjeta-productos">


                <div class="encabezado-tarjeta">

                    <i class="bi bi-box-seam"></i>

                    <div>

                        <h2>
                            PRODUCTOS DEL PEDIDO
                        </h2>

                        <p>
                            Mercadería solicitada
                        </p>

                    </div>

                </div>


                <div class="tabla-responsive">

                    <table class="tabla-productos">


                        <thead>

                            <tr>

                                <th>
                                    CÓDIGO
                                </th>

                                <th>
                                    PRODUCTO
                                </th>

                                <th>
                                    CATEGORÍA
                                </th>

                                <th>
                                    CANTIDAD
                                </th>

                                <th>
                                    STOCK
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if (!empty($productosPedido)): ?>


                                <?php foreach ($productosPedido as $producto): ?>

                                    <tr>


                                        <td>

                                            #<?= (int)$producto['ID_producto'] ?>

                                        </td>


                                        <td>

                                            <strong>

                                                <?= htmlspecialchars(
                                                    $producto['Descripcion']
                                                ) ?>

                                            </strong>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $producto['Categoria']
                                            ) ?>

                                        </td>


                                        <td>

                                            <?= (int)$producto['Cantidad'] ?>

                                        </td>


                                        <td>

                                            <?= (int)$producto['Stock_actual'] ?>

                                        </td>


                                    </tr>

                                <?php endforeach; ?>


                            <?php else: ?>


                                <tr>

                                    <td colspan="5">

                                        No hay productos registrados
                                        para este pedido.

                                    </td>

                                </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>

                </div>


            </div>



            <!-- ======================================
                 VALIDACIÓN FINAL
            ======================================= -->

            <?php if ($esAdministrador && $estaListo): ?>


                <div class="validacion-pedido">


                    <div class="validacion-info">

                        <i class="bi bi-shield-check"></i>

                        <div>

                            <strong>
                                PEDIDO LISTO PARA VALIDAR
                            </strong>

                            <span>
                                Todos los productos fueron retirados
                                durante el picking.
                            </span>

                        </div>

                    </div>


                    <form
                        action="index.php?ruta=validar-pedido"
                        method="POST"
                        onsubmit="return confirm('¿Confirmar que el pedido fue revisado y entregado?');"
                    >


                        <input
                            type="hidden"
                            name="id_pedido"
                            value="<?= (int)$pedido['ID_pedido'] ?>"
                        >


                        <button
                            type="submit"
                            class="btn-validar-pedido"
                        >

                            <i class="bi bi-check-circle"></i>

                            VALIDAR Y FINALIZAR

                        </button>


                    </form>


                </div>


            <?php endif; ?>



            <!-- ======================================
                 OPERARIO
            ======================================= -->

            <div class="tarjeta">


                <div class="encabezado-tarjeta">

                    <i class="bi bi-person-gear"></i>

                    <div>

                        <h2>
                            ASIGNACIÓN DE OPERARIO
                        </h2>

                        <p>
                            Responsable del picking
                        </p>

                    </div>

                </div>



                <!-- OPERARIO ACTUAL -->

                <?php if (!empty($pedido['Operario'])): ?>


                    <div class="operario-actual">

                        <i class="bi bi-person-check"></i>

                        <div>

                            <span>
                                OPERARIO ASIGNADO
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $pedido['Operario']
                                ) ?>

                            </strong>

                        </div>

                    </div>


                <?php else: ?>


                    <div class="operario-actual">

                        <i class="bi bi-person-dash"></i>

                        <div>

                            <span>
                                OPERARIO ASIGNADO
                            </span>

                            <strong>
                                Sin asignar
                            </strong>

                        </div>

                    </div>


                <?php endif; ?>



                <!-- ==================================
                     FORMULARIO DE ASIGNACIÓN

                     Solo lo ve el administrador.

                     El administrador también puede
                     seleccionarse como responsable
                     del picking.
                =================================== -->

                <?php if ($puedeAsignarOperario): ?>


                    <form
                        action="index.php?ruta=asignar-operario"
                        method="POST"
                        class="form-asignacion"
                    >


                        <input
                            type="hidden"
                            name="id_pedido"
                            value="<?= (int)$pedido['ID_pedido'] ?>"
                        >


                        <div class="campo-operario">


                            <label for="ci_operario">

                                <?= !empty($pedido['Operario'])
                                    ? 'Reasignar operario'
                                    : 'Seleccionar operario'
                                ?>

                            </label>


                            <select
                                id="ci_operario"
                                name="ci_operario"
                                required
                            >


                                <option value="">
                                    Seleccione un operario
                                </option>


                                <?php foreach ($operarios as $operario): ?>


                                    <option
                                        value="<?= htmlspecialchars(
                                            $operario['CI_operario']
                                        ) ?>"
                                        <?= (
                                            !empty($pedido['CI_operario'])
                                            &&
                                            $pedido['CI_operario']
                                            ===
                                            $operario['CI_operario']
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        <?= htmlspecialchars(
                                            $operario['Nombre_completo']
                                        ) ?>

                                        <?php if (
                                            strtolower($operario['Rol'])
                                            === 'administrador'
                                        ): ?>

                                            (Administrador)

                                        <?php else: ?>

                                            (Operario)

                                        <?php endif; ?>

                                    </option>


                                <?php endforeach; ?>


                            </select>


                        </div>



                        <button
                            type="submit"
                            class="btn-asignar"
                        >

                            <i class="bi bi-person-check"></i>

                            <?= !empty($pedido['Operario'])
                                ? 'REASIGNAR'
                                : 'ASIGNAR OPERARIO'
                            ?>

                        </button>


                    </form>


                <?php endif; ?>


            </div>


        </section>


    </main>


</div>


</body>

</html>
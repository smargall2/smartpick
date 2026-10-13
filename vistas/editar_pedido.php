<?php

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php?ruta=login");
    exit;
}

$usuario = $_SESSION['usuario'];

$error =
    $_SESSION['error_editar_pedido']
    ?? null;

unset(
    $_SESSION['error_editar_pedido']
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
        Modificar pedido #<?= (int)$pedido['ID_pedido'] ?> | SmartPick
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
        href="css/nuevo-pedido.css"
    >

</head>

<body>

<div class="dashboard">

    <aside class="sidebar">

        <div class="sidebar-logo">

            <img
                src="img/logoSinFondo.png"
                alt="SmartPick"
            >

        </div>


        <nav class="sidebar-menu">

            <a
                href="index.php?ruta=dashboard"
                class="menu-item"
            >
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>


            <a
                href="index.php?ruta=clientes"
                class="menu-item"
            >
                <i class="bi bi-person-vcard"></i>
                <span>Clientes</span>
            </a>


            <a
                href="index.php?ruta=ingresar-paquete"
                class="menu-item"
            >
                <i class="bi bi-box-arrow-in-down"></i>
                <span>Ingresar paquete</span>
            </a>


            <a
                href="index.php?ruta=productos"
                class="menu-item"
            >
                <i class="bi bi-box-seam"></i>
                <span>Productos</span>
            </a>


            <a
                href="index.php?ruta=zonas"
                class="menu-item"
            >
                <i class="bi bi-geo-alt"></i>
                <span>Zonas</span>
            </a>


            <a
                href="index.php?ruta=inventario"
                class="menu-item"
            >
                <i class="bi bi-boxes"></i>
                <span>Inventario</span>
            </a>


            <a
                href="index.php?ruta=pedidos"
                class="menu-item activo"
            >
                <i class="bi bi-clipboard-check"></i>
                <span>Pedidos</span>
            </a>


            <a href="#" class="menu-item">
                <i class="bi bi-people"></i>
                <span>Operarios</span>
            </a>


            <a
                href="index.php?ruta=picking"
                class="menu-item"
            >
                <i class="bi bi-signpost-2"></i>
                <span>Picking</span>
            </a>


            <a href="#" class="menu-item">
                <i class="bi bi-bar-chart"></i>
                <span>Reportes</span>
            </a>

        </nav>


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

        <header class="topbar">

            <div class="titulo-dashboard">

                <h1>
                    MODIFICAR PEDIDO
                </h1>

                <p>
                    Pedido #<?= (int)$pedido['ID_pedido'] ?>
                </p>

            </div>


            <div class="usuario-header">

                <div class="usuario-icono">
                    <i class="bi bi-person"></i>
                </div>

                <div class="usuario-info">

                    <strong>
                        <?= htmlspecialchars(
                            $usuario['nombre']
                        ) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars(
                            $usuario['rol']
                        ) ?>
                    </span>

                </div>

            </div>

        </header>


        <section class="entrega-contenido">

            <a
                href="index.php?ruta=ver-pedido&id=<?= (int)$pedido['ID_pedido'] ?>"
                class="volver"
            >
                <i class="bi bi-arrow-left"></i>
                Volver al pedido
            </a>


            <?php if ($error): ?>

                <div class="mensaje-error">

                    <i class="bi bi-exclamation-circle"></i>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <form
                action="index.php?ruta=guardar-edicion-pedido"
                method="POST"
                id="formEditarPedido"
            >

                <input
                    type="hidden"
                    name="id_pedido"
                    value="<?= (int)$pedido['ID_pedido'] ?>"
                >


                <!-- CLIENTE -->

                <div class="tarjeta-entrega">

                    <div class="titulo-seccion">

                        <div class="icono-seccion">
                            <i class="bi bi-person-check"></i>
                        </div>

                        <div>

                            <h2>
                                1. CLIENTE
                            </h2>

                            <p>
                                Cliente asociado a esta entrega.
                            </p>

                        </div>

                    </div>


                    <div class="cliente-seleccionado">

                        <div class="cliente-avatar">
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

                            <?php if (
                                !empty(
                                    $pedido['Mail_cliente']
                                )
                            ): ?>

                                <small>
                                    <?= htmlspecialchars(
                                        $pedido['Mail_cliente']
                                    ) ?>
                                </small>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>


                <!-- PAQUETES -->

                <div class="tarjeta-entrega">

                    <div class="titulo-seccion">

                        <div class="icono-seccion">
                            <i class="bi bi-box2"></i>
                        </div>

                        <div>

                            <h2>
                                2. PAQUETES ALMACENADOS
                            </h2>

                            <p>
                                Puede agregar paquetes guardados
                                pertenecientes al cliente.
                            </p>

                        </div>

                    </div>


                    <div class="lista-paquetes">

                        <?php if (
                            empty($paquetesCliente)
                        ): ?>

                            <div class="estado-vacio">

                                <i class="bi bi-box-seam"></i>

                                <p>
                                    Este cliente no tiene paquetes
                                    disponibles para retirar.
                                </p>

                            </div>

                        <?php else: ?>


                            <?php foreach (
                                $paquetesCliente
                                as $paquete
                            ): ?>

                                <label class="paquete-item">

                                    <input
                                        type="checkbox"
                                        name="paquetes[]"
                                        value="<?= (int)$paquete['ID_pedido'] ?>"
                                    >


                                    <div class="paquete-check">
                                        <i class="bi bi-check-lg"></i>
                                    </div>


                                    <div class="paquete-info">

                                        <strong>
                                            <?= htmlspecialchars(
                                                $paquete['Descripcion']
                                            ) ?>
                                        </strong>

                                        <span>
                                            Pedido
                                            #<?= (int)$paquete['ID_pedido'] ?>

                                            ·

                                            <?= htmlspecialchars(
                                                $paquete['Categoria']
                                            ) ?>
                                        </span>

                                    </div>


                                    <div class="estado-paquete">
                                        <?= htmlspecialchars(
                                            $paquete['Estado']
                                        ) ?>
                                    </div>

                                </label>

                            <?php endforeach; ?>


                        <?php endif; ?>

                    </div>

                </div>


                <!-- PRODUCTOS -->

                <div class="tarjeta-entrega">

                    <div class="titulo-seccion">

                        <div class="icono-seccion">
                            <i class="bi bi-cart3"></i>
                        </div>

                        <div>

                            <h2>
                                3. PRODUCTOS DE ALMACÉN
                            </h2>

                            <p>
                                Puede agregar, quitar o cambiar
                                cantidades.
                            </p>

                        </div>

                    </div>


                    <div class="buscador-contenedor">

                        <i class="bi bi-search"></i>

                        <input
                            type="text"
                            id="buscarProducto"
                            placeholder="Buscar por nombre, categoría o código..."
                            autocomplete="off"
                        >

                    </div>


                    <div
                        id="resultadosProductos"
                        class="resultados-productos"
                    ></div>


                    <h3 class="subtitulo">
                        PRODUCTOS DEL PEDIDO
                    </h3>


                    <div
                        id="productosSeleccionados"
                        class="productos-seleccionados"
                    >

                        <div
                            class="estado-vacio"
                            id="sinProductos"
                            style="display:none;"
                        >

                            <i class="bi bi-cart-x"></i>

                            <p>
                                No hay productos agregados.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- GUARDAR -->

                <div class="tarjeta-resumen">

                    <div>

                        <strong>
                            PEDIDO #<?= (int)$pedido['ID_pedido'] ?>
                        </strong>

                        <p>
                            Los cambios se guardarán antes
                            de comenzar el picking.
                        </p>

                    </div>


                    <button
                        type="submit"
                        class="btn-generar"
                    >
                        <i class="bi bi-check-lg"></i>
                        GUARDAR CAMBIOS
                    </button>

                </div>

            </form>

        </section>

    </main>

</div>


<script>

const buscarProducto =
    document.getElementById(
        'buscarProducto'
    );

const resultadosProductos =
    document.getElementById(
        'resultadosProductos'
    );

const productosSeleccionados =
    document.getElementById(
        'productosSeleccionados'
    );

const sinProductos =
    document.getElementById(
        'sinProductos'
    );


let productosAgregados = {};


const productosIniciales =
    <?= json_encode(
        $productosPedido,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    ) ?>;


// ==============================================
// CARGAR PRODUCTOS ACTUALES
// ==============================================

productosIniciales.forEach(
    producto => {

        producto.ID_producto =
            parseInt(
                producto.ID_producto
            );

        producto.Stock_actual =
            parseInt(
                producto.Stock_actual
            );

        producto.Cantidad =
            parseInt(
                producto.Cantidad
            );


        agregarProducto(
            producto,
            producto.Cantidad
        );
    }
);


// ==============================================
// BUSCAR PRODUCTOS
// ==============================================

let temporizadorProducto;


buscarProducto.addEventListener(
    'input',
    function () {

        clearTimeout(
            temporizadorProducto
        );


        const texto =
            this.value.trim();


        if (texto.length < 2) {

            resultadosProductos.innerHTML =
                '';

            return;
        }


        temporizadorProducto =
            setTimeout(

                async function () {

                    try {

                        const respuesta =
                            await fetch(
                                'index.php?ruta=buscar-productos&q='
                                +
                                encodeURIComponent(
                                    texto
                                )
                            );


                        const productos =
                            await respuesta.json();


                        mostrarProductos(
                            productos
                        );


                    } catch (error) {

                        console.error(
                            error
                        );
                    }

                },

                300
            );
    }
);


// ==============================================
// MOSTRAR RESULTADOS
// ==============================================

function mostrarProductos(productos)
{

    resultadosProductos.innerHTML =
        '';


    if (productos.length === 0) {

        resultadosProductos.innerHTML = `

            <div class="sin-resultados">
                No se encontraron productos.
            </div>
        `;

        return;
    }


    productos.forEach(
        producto => {

            const div =
                document.createElement(
                    'div'
                );


            div.className =
                'producto-resultado';


            const yaAgregado =
                productosAgregados[
                    producto.ID_producto
                ];


            div.innerHTML = `

                <div class="producto-datos">

                    <strong>
                        ${escaparHTML(
                            producto.Descripcion
                        )}
                    </strong>

                    <span>
                        ${escaparHTML(
                            producto.Categoria
                        )}

                        · Código:
                        ${producto.ID_producto}
                    </span>

                    <small>
                        Stock disponible:
                        ${producto.Stock_actual}
                    </small>

                </div>


                <button
                    type="button"
                    class="btn-agregar"
                    ${yaAgregado
                        ? 'disabled'
                        : ''
                    }
                >

                    <i class="bi bi-plus-lg"></i>

                    ${yaAgregado
                        ? 'Agregado'
                        : 'Agregar'
                    }

                </button>
            `;


            if (!yaAgregado) {

                div.querySelector(
                    '.btn-agregar'
                )
                .addEventListener(
                    'click',
                    function () {

                        agregarProducto(
                            producto,
                            1
                        );
                    }
                );
            }


            resultadosProductos
                .appendChild(div);
        }
    );
}


// ==============================================
// AGREGAR PRODUCTO
// ==============================================

function agregarProducto(
    producto,
    cantidad = 1
) {

    const id =
        parseInt(
            producto.ID_producto
        );


    if (productosAgregados[id]) {

        return;
    }


    productosAgregados[id] =
        producto;


    sinProductos.style.display =
        'none';


    const div =
        document.createElement(
            'div'
        );


    div.className =
        'producto-seleccionado';


    div.dataset.id =
        id;


    div.innerHTML = `

        <input
            type="hidden"
            name="producto_id[]"
            value="${id}"
        >


        <div class="producto-seleccionado-info">

            <strong>
                ${escaparHTML(
                    producto.Descripcion
                )}
            </strong>

            <span>
                Stock:
                ${producto.Stock_actual}
            </span>

        </div>


        <div class="cantidad-producto">

            <label>
                Cantidad
            </label>

            <input
                type="number"
                name="cantidad[]"
                value="${cantidad}"
                min="1"
                max="${producto.Stock_actual}"
                required
            >

        </div>


        <button
            type="button"
            class="btn-quitar"
            title="Quitar producto"
        >
            <i class="bi bi-trash"></i>
        </button>
    `;


    div.querySelector(
        '.btn-quitar'
    )
    .addEventListener(
        'click',
        function () {

            delete productosAgregados[id];

            div.remove();


            if (
                Object.keys(
                    productosAgregados
                ).length === 0
            ) {

                sinProductos.style.display =
                    'flex';
            }
        }
    );


    productosSeleccionados
        .appendChild(div);
}


// ==============================================
// VALIDACIÓN
// ==============================================

document
.getElementById(
    'formEditarPedido'
)
.addEventListener(
    'submit',
    function (event) {

        const productos =
            document.querySelectorAll(
                'input[name="producto_id[]"]'
            );


        if (productos.length === 0) {

            event.preventDefault();

            alert(
                'El pedido debe contener al menos un producto de almacén.'
            );

            return;
        }


        const cantidades =
            document.querySelectorAll(
                'input[name="cantidad[]"]'
            );


        for (
            const cantidad of cantidades
        ) {

            if (
                parseInt(
                    cantidad.value
                ) <= 0
            ) {

                event.preventDefault();

                alert(
                    'Las cantidades deben ser mayores a cero.'
                );

                return;
            }
        }
    }
);


// ==============================================
// ESCAPAR HTML
// ==============================================

function escaparHTML(texto)
{

    const div =
        document.createElement(
            'div'
        );

    div.textContent =
        texto ?? '';

    return div.innerHTML;
}

</script>

</body>

</html>
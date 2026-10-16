<?php
if (!isset($_SESSION['usuario']) || strtolower($_SESSION['usuario']['rol']) !== 'administrador') { http_response_code(403); exit('Acceso denegado.'); }
$usuario = $_SESSION['usuario'];
$mensaje = $_SESSION['mensaje_operarios'] ?? null;
$error = $_SESSION['error_operarios'] ?? null;
unset($_SESSION['mensaje_operarios'], $_SESSION['error_operarios']);
$editarCI = trim($_GET['editar'] ?? '');
$editando = $editarCI !== '' ? $operarioRepositorio->buscarPorCI($editarCI) : null;
function hOperario($valor) { return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Operarios | SmartPick</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/dashboard.css">
<style>
.operarios-contenido{padding:28px;display:grid;gap:24px}.operarios-card{background:#fff;border-radius:15px;padding:25px;box-shadow:0 4px 18px #102f4f12}.operarios-card h2{font-family:'Bebas Neue',sans-serif;font-size:30px;color:#102f4f;margin:0 0 18px}.operarios-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.operarios-form label{display:grid;gap:7px;font-size:13px;font-weight:700;color:#102f4f}.operarios-form input,.operarios-form select{border:1px solid #d5dce5;border-radius:8px;padding:12px;font:inherit;width:100%;box-sizing:border-box}.operarios-form .acciones-form{grid-column:1/-1;display:flex;gap:12px;align-items:center}.btn-operarios{background:#205b9d;color:white;border:0;border-radius:8px;padding:11px 16px;text-decoration:none;font:600 13px Montserrat,sans-serif;cursor:pointer}.btn-operarios.secundario{background:#e7edf3;color:#102f4f}.operarios-tabla{width:100%;border-collapse:collapse;font-size:13px}.operarios-tabla th,.operarios-tabla td{text-align:left;padding:13px;border-bottom:1px solid #e9edf2}.operarios-tabla th{color:#102f4f}.operarios-tabla td form{display:inline}.btn-eliminar{border:0;background:#fff0f0;color:#b12525;padding:9px;border-radius:7px;cursor:pointer}.aviso{padding:13px;border-radius:8px;background:#e8f5f4}.aviso.error{background:#ffe9e9}.operarios-buscador{display:flex;align-items:center;gap:10px;max-width:460px;margin:0 0 18px;padding:0 14px;background:#f7f9fc;border:1px solid #d5dce5;border-radius:9px;color:#5aadb1}.operarios-buscador:focus-within{border-color:#205b9d;box-shadow:0 0 0 3px #205b9d18}.operarios-buscador input{width:100%;padding:13px 0;border:0;outline:0;background:transparent;font:500 13px Montserrat,sans-serif;color:#102f4f}.operarios-buscador input::placeholder{color:#8290a1}.tabla-scroll{overflow-x:auto}@media(max-width:700px){.operarios-contenido{padding:15px}.operarios-form{grid-template-columns:1fr}}
</style></head><body>
<div class="dashboard"><aside class="sidebar"><div class="sidebar-logo"><img src="img/logoSinFondo.png" alt="SmartPick"></div>
<nav class="sidebar-menu"><a href="index.php?ruta=dashboard" class="menu-item"><i class="bi bi-grid"></i><span>Dashboard</span></a><a href="index.php?ruta=clientes" class="menu-item"><i class="bi bi-person-vcard"></i><span>Clientes</span></a><a href="index.php?ruta=productos" class="menu-item"><i class="bi bi-box-seam"></i><span>Productos</span></a><a href="index.php?ruta=zonas" class="menu-item"><i class="bi bi-geo-alt"></i><span>Zonas</span></a><a href="index.php?ruta=inventario" class="menu-item"><i class="bi bi-boxes"></i><span>Inventario</span></a><a href="index.php?ruta=pedidos" class="menu-item"><i class="bi bi-clipboard-check"></i><span>Pedidos</span></a><a href="index.php?ruta=operarios" class="menu-item activo"><i class="bi bi-people"></i><span>Operarios</span></a><a href="index.php?ruta=picking" class="menu-item"><i class="bi bi-signpost-2"></i><span>Picking</span></a></nav><div class="sidebar-footer"><a href="index.php?ruta=logout" class="menu-item cerrar-sesion"><i class="bi bi-box-arrow-left"></i><span>Cerrar sesión</span></a></div></aside>
<main class="main-content"><header class="topbar"><div class="titulo-dashboard"><h1>OPERARIOS</h1><p>Gestión de usuarios del centro logístico</p></div><div class="usuario-header"><div class="usuario-icono"><i class="bi bi-person"></i></div><div class="usuario-info"><strong><?= hOperario($usuario['nombre']) ?></strong><span><?= hOperario($usuario['rol']) ?></span></div></div></header>
<div class="operarios-contenido">
<?php if ($mensaje): ?><div class="aviso"><?= hOperario($mensaje) ?></div><?php endif; ?>
<?php if ($error): ?><div class="aviso error"><?= hOperario($error) ?></div><?php endif; ?>
<section class="operarios-card"><h2><?= $editando ? 'MODIFICAR OPERARIO' : 'AGREGAR OPERARIO' ?></h2>
<form class="operarios-form" method="post" action="index.php?ruta=guardar-operario">
<input type="hidden" name="accion" value="<?= $editando ? 'modificar' : 'crear' ?>">
<label>CI <input name="ci" maxlength="20" required <?= $editando ? 'readonly' : '' ?> value="<?= hOperario($editando['CI_operario'] ?? '') ?>"></label>
<label>Nombre completo <input name="nombre" maxlength="100" required value="<?= hOperario($editando['Nombre_completo'] ?? '') ?>"></label>
<label>Teléfono <input name="telefono" maxlength="30" value="<?= hOperario($editando['Telefono'] ?? '') ?>"></label>
<label>Correo electrónico <input type="email" name="mail" maxlength="100" value="<?= hOperario($editando['Mail'] ?? '') ?>"></label>
<label>Dirección <input name="direccion" maxlength="150" value="<?= hOperario($editando['Direccion'] ?? '') ?>"></label>
<label>Rol <select name="rol" required><option value="operario" <?= strtolower($editando['Rol'] ?? '')==='operario'?'selected':'' ?>>Operario</option><option value="administrador" <?= strtolower($editando['Rol'] ?? '')==='administrador'?'selected':'' ?>>Administrador</option></select></label>
<?php if (!$editando): ?><label>Contraseña inicial <input type="password" name="contrasena" required autocomplete="new-password"></label><?php endif; ?>
<div class="acciones-form"><button class="btn-operarios" type="submit"><?= $editando ? 'GUARDAR CAMBIOS' : 'AGREGAR OPERARIO' ?></button><?php if ($editando): ?><a class="btn-operarios secundario" href="index.php?ruta=operarios">Cancelar</a><?php endif; ?></div>
</form></section>
<section class="operarios-card"><h2>OPERARIOS REGISTRADOS</h2><div class="operarios-buscador"><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="buscarOperario" placeholder="Buscar por nombre, CI o correo..." aria-label="Buscar operarios"></div><div class="tabla-scroll"><table class="operarios-tabla"><thead><tr><th>CI</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Rol</th><th>Acciones</th></tr></thead><tbody>
<?php foreach ($operarios as $op): ?><tr class="fila-operario" data-busqueda="<?= hOperario($op['CI_operario'] . ' ' . $op['Nombre_completo'] . ' ' . ($op['Mail'] ?? '')) ?>"><td><?= hOperario($op['CI_operario']) ?></td><td><?= hOperario($op['Nombre_completo']) ?></td><td><?= hOperario($op['Mail']) ?></td><td><?= hOperario($op['Telefono']) ?></td><td><?= hOperario($op['Rol']) ?></td><td><a class="btn-operarios secundario" href="index.php?ruta=operarios&amp;editar=<?= urlencode($op['CI_operario']) ?>">Editar</a> <?php if ($op['CI_operario'] !== $usuario['ci']): ?><form method="post" action="index.php?ruta=eliminar-operario" onsubmit="return confirm('¿Eliminar este operario? Esta acción no se puede deshacer.');"><input type="hidden" name="ci" value="<?= hOperario($op['CI_operario']) ?>"><button class="btn-eliminar" type="submit" title="Eliminar"><i class="bi bi-trash"></i></button></form><?php endif; ?></td></tr><?php endforeach; ?>
<?php if (!$operarios): ?><tr><td colspan="6">No hay operarios registrados.</td></tr><?php endif; ?>
</tbody></table><p id="sinResultadosOperarios" style="display:none;padding:15px;color:#66758a;font-size:13px">No se encontraron operarios con esos datos.</p></div></section></div></main></div>
<script>
const buscarOperario = document.getElementById('buscarOperario');
const filasOperarios = Array.from(document.querySelectorAll('.fila-operario'));
const sinResultadosOperarios = document.getElementById('sinResultadosOperarios');
const normalizar = texto => texto.toLocaleLowerCase('es').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
buscarOperario.addEventListener('input', function () {
    const consulta = normalizar(this.value.trim());
    let visibles = 0;
    filasOperarios.forEach(fila => {
        const coincide = normalizar(fila.dataset.busqueda || '').includes(consulta);
        fila.style.display = coincide ? '' : 'none';
        if (coincide) visibles++;
    });
    sinResultadosOperarios.style.display = filasOperarios.length > 0 && visibles === 0 ? 'block' : 'none';
});
</script>
</body></html>

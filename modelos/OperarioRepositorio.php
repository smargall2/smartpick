<?php
class OperarioRepositorio
{
    private $conexion;
    public function __construct($conexion) { $this->conexion = $conexion; }

    public function obtenerOperarios()
    {
        $sql = "SELECT o.CI_operario, o.Rol, p.Nombre_completo, p.Mail, p.Telefono, p.Direccion
                FROM operario o INNER JOIN persona p ON p.CI = o.CI_operario
                WHERE LOWER(o.Rol) IN ('operario','administrador') ORDER BY p.Nombre_completo";
        $resultado = mysqli_query($this->conexion, $sql);
        if (!$resultado) throw new Exception(mysqli_error($this->conexion));
        return mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    }

    public function buscarPorCI($ci)
    {
        $sql = "SELECT o.CI_operario, o.Rol, p.Nombre_completo, p.Mail, p.Telefono, p.Direccion
                FROM operario o INNER JOIN persona p ON p.CI=o.CI_operario
                WHERE o.CI_operario=? AND LOWER(o.Rol) IN ('operario','administrador') LIMIT 1";
        $stmt = mysqli_prepare($this->conexion, $sql);
        if (!$stmt) throw new Exception(mysqli_error($this->conexion));
        mysqli_stmt_bind_param($stmt, 's', $ci);
        if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila ?: null;
    }

    public function crear($datos)
    {
        $ci = trim($datos['ci'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');
        $mail = trim($datos['mail'] ?? '');
        $telefono = trim($datos['telefono'] ?? '');
        $direccion = trim($datos['direccion'] ?? '');
        $rol = strtolower(trim($datos['rol'] ?? ''));
        $clave = (string)($datos['contrasena'] ?? '');

        if ($ci === '' || !in_array($rol, ['operario', 'administrador'], true)) {
            throw new Exception('Completá la CI y seleccioná un rol válido.');
        }
        $rolBD = $rol === 'administrador' ? 'Administrador' : 'Operario';
        mysqli_begin_transaction($this->conexion);
        try {
            $stmt = mysqli_prepare($this->conexion, 'SELECT CI, Contrasena FROM persona WHERE CI=? LIMIT 1 FOR UPDATE');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 's', $ci);
            if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
            $existePersona = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if (!$existePersona) {
                if ($nombre === '' || $clave === '') {
                    throw new Exception('Para una persona nueva, nombre y contraseña son obligatorios.');
                }
                if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Correo electrónico inválido.');
                }
                $mailBD = $mail === '' ? null : $mail;
                $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($this->conexion, 'INSERT INTO persona (CI, Nombre_completo, Telefono, Mail, Direccion, Contrasena) VALUES (?,?,?,?,?,?)');
                if (!$stmt) throw new Exception(mysqli_error($this->conexion));
                mysqli_stmt_bind_param($stmt, 'ssssss', $ci, $nombre, $telefono, $mailBD, $direccion, $claveHash);
                if (!mysqli_stmt_execute($stmt)) throw new Exception('No se pudo crear la persona: ' . mysqli_stmt_error($stmt));
                mysqli_stmt_close($stmt);
            } elseif ((string)($existePersona['Contrasena'] ?? '') === '') {
                // Los clientes creados sin acceso al sistema tienen contraseña vacía.
                // Al asignarles un rol de operario, se les crea una contraseña segura.
                if ($clave === '') {
                    throw new Exception('Esta persona ya es cliente y no tiene contraseña. Ingresá una contraseña inicial.');
                }
                $claveHash = password_hash($clave, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($this->conexion, "UPDATE persona SET Contrasena=? WHERE CI=? AND Contrasena=''");
                if (!$stmt) throw new Exception(mysqli_error($this->conexion));
                mysqli_stmt_bind_param($stmt, 'ss', $claveHash, $ci);
                if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
                mysqli_stmt_close($stmt);
            }

            $stmt = mysqli_prepare($this->conexion, 'SELECT CI_operario FROM operario WHERE CI_operario=? LIMIT 1');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 's', $ci);
            if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
            $yaEsOperario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);
            if ($yaEsOperario) throw new Exception('Esta persona ya está registrada como operario o administrador. Podés modificar su rol desde la lista.');

            $stmt = mysqli_prepare($this->conexion, 'INSERT INTO operario (CI_operario, Rol) VALUES (?,?)');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 'ss', $ci, $rolBD);
            if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            mysqli_commit($this->conexion);
        } catch (Throwable $e) {
            mysqli_rollback($this->conexion);
            throw $e;
        }
    }

    public function modificar($ci, $datos)
    {
        $nombre = trim($datos['nombre'] ?? '');
        $mail = trim($datos['mail'] ?? '');
        $telefono = trim($datos['telefono'] ?? '');
        $direccion = trim($datos['direccion'] ?? '');
        $rol = strtolower(trim($datos['rol'] ?? ''));
        if (!$this->buscarPorCI($ci)) throw new Exception('Operario no encontrado.');
        if ($nombre === '' || !in_array($rol, ['operario','administrador'], true)) throw new Exception('Nombre y rol son obligatorios.');
        if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) throw new Exception('Correo electrónico inválido.');
        $mailBD = $mail === '' ? null : $mail;
        $rolBD = $rol === 'administrador' ? 'Administrador' : 'Operario';
        mysqli_begin_transaction($this->conexion);
        try {
            $stmt = mysqli_prepare($this->conexion, 'UPDATE persona SET Nombre_completo=?, Telefono=?, Mail=?, Direccion=? WHERE CI=?');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 'sssss', $nombre, $telefono, $mailBD, $direccion, $ci);
            if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($this->conexion, 'UPDATE operario SET Rol=? WHERE CI_operario=?');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 'ss', $rolBD, $ci);
            if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            mysqli_commit($this->conexion);
        } catch (Throwable $e) { mysqli_rollback($this->conexion); throw $e; }
    }

    public function eliminar($ci)
    {
        if (!$this->buscarPorCI($ci)) throw new Exception('Operario no encontrado.');
        mysqli_begin_transaction($this->conexion);
        try {
            $stmt = mysqli_prepare($this->conexion, 'SELECT COUNT(*) AS total FROM atiende WHERE CI_operario=?');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 's', $ci);
            mysqli_stmt_execute($stmt);
            $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
            mysqli_stmt_close($stmt);
            if ($total > 0) throw new Exception('No se puede eliminar: tiene pedidos asociados.');
            $stmt = mysqli_prepare($this->conexion, 'SELECT COUNT(*) AS total FROM pickingdetalle WHERE CI_operario=?');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 's', $ci);
            mysqli_stmt_execute($stmt);
            $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
            mysqli_stmt_close($stmt);
            if ($total > 0) throw new Exception('No se puede eliminar: tiene confirmaciones de picking registradas.');
            $stmt = mysqli_prepare($this->conexion, 'DELETE FROM operario WHERE CI_operario=?');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 's', $ci);
            if (!mysqli_stmt_execute($stmt)) throw new Exception(mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($this->conexion, 'DELETE FROM persona WHERE CI=? AND NOT EXISTS (SELECT 1 FROM cliente WHERE CI_cliente=?)');
            if (!$stmt) throw new Exception(mysqli_error($this->conexion));
            mysqli_stmt_bind_param($stmt, 'ss', $ci, $ci);
            if (!mysqli_stmt_execute($stmt)) throw new Exception('La persona tiene otros registros asociados y no puede eliminarse: ' . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            mysqli_commit($this->conexion);
        } catch (Throwable $e) { mysqli_rollback($this->conexion); throw $e; }
    }
}

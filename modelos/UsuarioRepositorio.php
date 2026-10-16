<?php

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/Usuario.php';

class UsuarioRepositorio
{
    private $conexion;


    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }


    public function buscarPorCI($ci)
    {
        $sql = "
            SELECT
                p.CI,
                p.Nombre_completo,
                p.Mail,
                p.Contrasena,
                o.Rol

            FROM persona p

            INNER JOIN operario o
                ON p.CI = o.CI_operario

            WHERE p.CI = ?

            LIMIT 1
        ";


        $stmt = mysqli_prepare(
            $this->conexion,
            $sql
        );


        if (!$stmt) {
            die(
                'Error al preparar la consulta: ' .
                mysqli_error($this->conexion)
            );
        }


        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $ci
        );


        mysqli_stmt_execute($stmt);


        $resultado =
            mysqli_stmt_get_result($stmt);


        $fila =
            mysqli_fetch_assoc($resultado);


        mysqli_stmt_close($stmt);


        if (!$fila) {
            return null;
        }


        return new Usuario(
            $fila['CI'],
            $fila['Nombre_completo'],
            $fila['Mail'],
            $fila['Contrasena'],
            $fila['Rol']
        );
    }

    // Verifica contraseñas modernas y migra las antiguas al iniciar sesión.
    // No modifica contraseñas vacías de clientes sin acceso al sistema.
    public function validarContrasena($usuario, $contrasena)
    {
        if ($usuario === null || !is_string($contrasena) || $contrasena === '') {
            return false;
        }

        $guardada = (string) $usuario->contrasena;
        if ($guardada === '') {
            return false;
        }

        $info = password_get_info($guardada);
        $esHash = $info['algo'] !== null && $info['algo'] !== 0;

        if ($esHash) {
            if (!password_verify($contrasena, $guardada)) {
                return false;
            }
            if (password_needs_rehash($guardada, PASSWORD_DEFAULT)) {
                $this->actualizarHashSiCoincide($usuario->ci, $guardada, password_hash($contrasena, PASSWORD_DEFAULT));
            }
            return true;
        }

        // Compatibilidad temporal con las cuentas existentes en texto plano.
        if (!hash_equals($guardada, $contrasena)) {
            return false;
        }

        $nuevoHash = password_hash($contrasena, PASSWORD_DEFAULT);
        $this->actualizarHashSiCoincide($usuario->ci, $guardada, $nuevoHash);
        return true;
    }

    private function actualizarHashSiCoincide($ci, $anterior, $nuevoHash)
    {
        // La condición adicional evita sobrescribir una contraseña cambiada
        // entre la lectura del usuario y esta actualización.
        $sql = 'UPDATE persona SET Contrasena = ? WHERE CI = ? AND Contrasena = ?';
        $stmt = mysqli_prepare($this->conexion, $sql);
        if (!$stmt) {
            error_log('SmartPick: no se pudo preparar la actualización de contraseña.');
            return;
        }
        mysqli_stmt_bind_param($stmt, 'sss', $nuevoHash, $ci, $anterior);
        if (!mysqli_stmt_execute($stmt)) {
            error_log('SmartPick: no se pudo actualizar el hash de contraseña.');
        }
        mysqli_stmt_close($stmt);
    }

}
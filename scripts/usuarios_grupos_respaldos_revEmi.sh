#!/usr/bin/env bash

# Administración de usuarios, grupos y respaldos Linux

# Detecta fallos en cualquiera de los comandos de una tubería.
set -o pipefail

LOGS="logs.txt"
BACKUP_DIR="/var/backups/constant"

log() {
  echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOGS"
}

ayuda() {
  echo "==============================================="
  echo "Uso: ./usuarios_grupos_con_respaldos.sh"
  echo
  echo "Funciones:"
  echo "  * Crear usuarios"
  echo "  * Eliminar usuarios"
  echo "  * Cambiar contraseñas"
  echo "  * Consultar usuarios"
  echo "  * Crear grupos"
  echo "  * Eliminar grupos"
  echo "  * Agregar usuarios a grupos"
  echo "  * Respaldar /home y /root"
  echo "  * Respaldar configuraciones del sistema"
  echo "  * Respaldar todas las bases de datos de MariaDB"
  echo "  * Restaurar un respaldo SQL de MariaDB"
  echo "==============================================="
}

# Prepara una carpeta de respaldos, que puede requerir permisos de administrador.
preparar_carpeta_respaldo() {
  if ! sudo mkdir -p "$1"; then
    echo "Error: no se pudo crear la carpeta $1"
    return 1
  fi
  # Los respaldos pueden contener información privada y contraseñas.
  if ! sudo chmod 700 "$BACKUP_DIR"; then
    echo "Error: no se pudieron proteger los permisos de $BACKUP_DIR"
    return 1
  fi
}

# Estas cuatro funciones se incorporaron a partir del script anterior.
backup_directorios() {
  local fecha
  fecha=$(date +%Y%m%d_%H%M%S)

  preparar_carpeta_respaldo "$BACKUP_DIR/home" || return 1
  preparar_carpeta_respaldo "$BACKUP_DIR/root" || return 1

  # Sin --delete para no borrar archivos que ya se habían respaldado.
  if sudo rsync -a /home/ "$BACKUP_DIR/home/" && \
     sudo rsync -a /root/ "$BACKUP_DIR/root/"; then
    log "Respaldo de /home y /root realizado ($fecha)."
    echo "Respaldo completado en $BACKUP_DIR."
  else
    echo "Error: no se pudo completar el respaldo de directorios."
    log "Error en el respaldo de /home y /root ($fecha)."
    return 1
  fi
}

backup_config() {
  local fecha ruta encontrados=0 errores=0
  fecha=$(date +%Y%m%d_%H%M%S)

  preparar_carpeta_respaldo "$BACKUP_DIR/config" || return 1

  # Mismas carpetas del script anterior, si existen en este equipo.
  for ruta in /etc/ssh /etc/apache2 /etc/mysql /etc/ufw; do
    if [[ -d "$ruta" ]]; then
      encontrados=$((encontrados + 1))
      if ! sudo rsync -a "$ruta" "$BACKUP_DIR/config/"; then
        echo "No se pudo respaldar $ruta"
        errores=$((errores + 1))
      fi
    else
      echo "Se omite $ruta (no existe en este equipo)."
    fi
  done

  if (( encontrados > 0 && errores == 0 )); then
    log "Respaldo de configuraciones realizado ($fecha)."
    echo "Configuraciones respaldadas en $BACKUP_DIR/config/."
  else
    echo "El respaldo de configuraciones no se completó correctamente."
    log "Respaldo de configuraciones incompleto ($fecha)."
    return 1
  fi
}

backup_bd() {
  local fecha archivo temporal
  fecha=$(date +%Y%m%d_%H%M%S)
  archivo="$BACKUP_DIR/database/mariadb_$fecha.sql"
  temporal="$BACKUP_DIR/database/.mariadb_$fecha.sql.parcial"

  if ! command -v mariadb-dump >/dev/null 2>&1; then
    echo "Error: no se encontró mariadb-dump."
    echo "Este respaldo usa MariaDB del sistema, como el script anterior."
    return 1
  fi

  preparar_carpeta_respaldo "$BACKUP_DIR/database" || return 1

  # sudo tee escribe en /var/backups con permisos de administrador.
  # El archivo temporal evita dar por completo un volcado fallido.
  if sudo mariadb-dump --all-databases | sudo tee "$temporal" >/dev/null; then
    if sudo chmod 600 "$temporal" && sudo mv "$temporal" "$archivo"; then
      log "Respaldo de MariaDB realizado ($fecha): $archivo"
      echo "Bases de datos respaldadas en: $archivo"
    else
      sudo rm -f -- "$temporal"
      echo "Error: no se pudo guardar el archivo SQL."
      log "Error al guardar respaldo de MariaDB ($fecha)."
      return 1
    fi
  else
    sudo rm -f -- "$temporal"
    echo "Error: falló el respaldo de MariaDB."
    log "Error en respaldo de MariaDB ($fecha)."
    return 1
  fi
}

restaurar_bd() {
  local archivo confirmacion
  read -r -p "Ruta del respaldo SQL: " archivo

  if ! sudo test -f "$archivo"; then
    echo "Error: el archivo indicado no existe."
    return 1
  fi

  if ! command -v mariadb >/dev/null 2>&1; then
    echo "Error: no se encontró el comando mariadb."
    return 1
  fi

  echo "ADVERTENCIA: restaurar este SQL puede reemplazar datos existentes."
  read -r -p "Para confirmar, escribí RESTAURAR: " confirmacion
  if [[ "$confirmacion" != "RESTAURAR" ]]; then
    echo "Restauración cancelada."
    return 0
  fi

  # sudo cat permite leer archivos SQL protegidos en /var/backups.
  if sudo cat -- "$archivo" | sudo mariadb; then
    log "Base de datos restaurada desde $archivo."
    echo "Restauración finalizada."
  else
    echo "Error: la restauración no se completó correctamente."
    log "Error al restaurar base de datos desde $archivo."
    return 1
  fi
}

gestionar_respaldos() {
  echo
  echo "===== RESPALDOS ====="
  echo "1) Respaldo de /home y /root"
  echo "2) Respaldo de configuraciones"
  echo "3) Respaldo de MariaDB"
  echo "4) Restaurar MariaDB"
  echo "5) Volver"
  read -r -p "Opción: " op

  case "$op" in
    1) backup_directorios ;;
    2) backup_config ;;
    3) backup_bd ;;
    4) restaurar_bd ;;
    5) return 0 ;;
    *) echo "Opción inválida." ;;
  esac
}

gestionar_usuarios() {
  echo
  echo "=== USUARIOS ==="
  echo "1) Crear usuario"
  echo "2) Eliminar usuario"
  echo "3) Cambiar contraseña"
  echo "4) Consultar usuario"
  read -p "Opción: " op

  case "$op" in
    1)
      read -p "Nombre de usuario: " usuario
      sudo useradd -m "$usuario" && log "Usuario creado: $usuario"
      ;;
    2)
      read -p "Usuario a eliminar: " usuario
      sudo userdel -r "$usuario" && log "Usuario eliminado: $usuario"
      ;;
    3)
      read -p "Usuario: " usuario
      sudo passwd "$usuario"
      log "Contraseña modificada: $usuario"
      ;;
    4)
      read -p "Usuario a consultar: " usuario
      id "$usuario"
      ;;
    *)
      echo "Opción inválida."
      ;;
  esac
}

gestionar_grupos() {
  echo
  echo "=== GRUPOS ==="
  echo "1) Crear grupo"
  echo "2) Eliminar grupo"
  echo "3) Agregar usuario a grupo"
  echo "4) Consultar grupo"
  read -p "Opción: " op

  case "$op" in
    1)
      read -p "Nombre del grupo: " grupo
      sudo groupadd "$grupo" && log "Grupo creado: $grupo"
      ;;
    2)
      read -p "Grupo a eliminar: " grupo
      sudo groupdel "$grupo" && log "Grupo eliminado: $grupo"
      ;;
    3)
      read -p "Usuario: " usuario
      read -p "Grupo: " grupo
      sudo usermod -aG "$grupo" "$usuario" && log "Usuario $usuario agregado a $grupo"
      ;;
    4)
      read -p "Grupo a consultar: " grupo
      getent group "$grupo"
      ;;
    *)
      echo "Opción inválida."
      ;;
  esac
}

while true; do
  echo
  echo "===== SCRIPT ADMINISTRACIÓN ====="
  echo "1) Gestionar usuarios"
  echo "2) Gestionar grupos"
  echo "3) Gestionar respaldos"
  echo "4) Ver logs"
  echo "5) Ayuda"
  echo "6) Salir"

  read -p "Opción: " op

  case "$op" in
    1) gestionar_usuarios ;;
    2) gestionar_grupos ;;
    3) gestionar_respaldos ;;
    4) cat "$LOGS" 2>/dev/null ;;
    5) ayuda ;;
    6) exit 0 ;;
    *) echo "Opción inválida." ;;
  esac
done

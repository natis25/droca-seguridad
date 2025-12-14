<?php
// Procesa ALTA de CLIENTE
ini_set('session.cookie_httponly','1');
ini_set('session.cookie_samesite','Lax');
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
session_regenerate_id(true);

require_once __DIR__ . '/auth_helpers.php';

// Rate limiting
if (hit_rate_limit('rl_reg_empleado', 10)) {
  $_SESSION['flash_error'] = 'Demasiados intentos. Espera un momento.';
  header("Location: ../OSI/registrarEmpleado.php"); exit;
}

$__POST_REDIRECT = '../OSI/registrarEmpleado.php';
require_once __DIR__ . '/bootstrap_post.php';

// Validar permisos OSI
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'trabajador') {
  $_SESSION['flash_error'] = 'Sin autorización.';
  header("Location: ../login.php"); exit;
}

// Verificar que sea OSI
require_once __DIR__ . '/helpers.php';
if (!esUsuarioOSI($mysqli, $_SESSION['user_id'])) {
  $_SESSION['flash_error'] = 'Solo OSI puede registrar empleados.';
  header("Location: ../OSI/panelControl.php"); exit;
}

// Obtener datos del formulario
$nombre   = cap($_POST['nombre']  ?? '', 100);
$apellido = cap($_POST['apellido'] ?? '', 100);
$telefono = cap($_POST['telefono'] ?? '', 20);
$correo   = filter_var(cap($_POST['correo'] ?? '', 255), FILTER_VALIDATE_EMAIL);
$usuario  = cap($_POST['usuario']  ?? '', 100);
$password = $_POST['password'] ?? '';
$idRol    = (int)($_POST['idRol'] ?? 0);
$idArea   = (int)($_POST['idArea'] ?? 0);

if (!$nombre || !$apellido || !$correo || !$usuario || !$password || !$idRol || !$idArea) {
  $_SESSION['flash_error'] = 'Faltan campos obligatorios.';
  header("Location: {$__POST_REDIRECT}"); exit;
}

// Validar política de contraseñas
list($pwd_ok, $checks) = validar_password($password);
if (!$pwd_ok) {
  $_SESSION['flash_error'] = 'Contraseña no cumple política (min 12 caracteres, mayúscula, minúscula, número, símbolo).';
  header("Location: {$__POST_REDIRECT}"); exit;
}

// Lock para evitar colisiones
if (!get_named_lock($mysqli, 'reg_empleado', 5)) {
  $_SESSION['flash_error'] = 'Sistema ocupado. Reintenta.';
  header("Location: {$__POST_REDIRECT}"); exit;
}

try {
  $mysqli->begin_transaction();

  // Validar correo único
  if (correo_existe($mysqli, $correo, 'trabajador')) {
    throw new Exception('El correo ya está registrado.');
  }
  if (correo_existe($mysqli, $correo, 'cliente')) {
    throw new Exception('El correo ya está registrado en cliente.');
  }

  // Normalizar usuario
  $usuario = sanitize_username_input($usuario, $nombre, $apellido);
  $usuario = username_unico($mysqli, $usuario, 'trabajador');

  // Hash de contraseña
  $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

  // Insertar trabajador
  $sql = "INSERT INTO trabajador (Nombre, Apellido, Telefono, Correo, Usuario, idRol, idArea, EstadoCuenta, IntentosFallidos, is_deleted, created_at)
          VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo', 0, 0, NOW())";
  $s = $mysqli->prepare($sql);
  $s->bind_param('sssssii', $nombre, $apellido, $telefono, $correo, $usuario, $idRol, $idArea);
  if (!$s->execute()) throw new Exception('Error al insertar trabajador: ' . $s->error);
  $idTrabajador = $mysqli->insert_id;
  $s->close();

  // Guardar contraseña en historial
  $sql_hist = "INSERT INTO password_history (user_type, user_id, PasswordHash, FechaCambio, is_first_login)
               VALUES ('trabajador', ?, ?, NOW(), 1)";
  $sh = $mysqli->prepare($sql_hist);
  $sh->bind_param('is', $idTrabajador, $hash);
  if (!$sh->execute()) throw new Exception('Error al guardar historial: ' . $sh->error);
  $sh->close();

  $mysqli->commit();

  // 🆕 LOGS DE SEGURIDAD
  require_once __DIR__ . '/SecurityLogger.php';
  $secLogger = new SecurityLogger($mysqli);
  $secLogger->logCreacionUsuario($usuario, 'trabajador', $_SESSION['username'] ?? 'SISTEMA');

  $_SESSION['flash_success'] = "Trabajador '{$usuario}' creado exitosamente. Se requerirá cambio de contraseña al primer ingreso.";
  
} catch (Exception $e) {
  $mysqli->rollback();
  
  // LOG DE ERROR
  require_once __DIR__ . '/ApplicationLogger.php';
  $appLogger = new ApplicationLogger($mysqli);
  $appLogger->logError('registro_empleado', $e->getMessage());
  
  $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
} finally {
  release_named_lock($mysqli, 'reg_empleado');
}

$mysqli->close();
header("Location: ../OSI/gestionarEmpleados.php");
exit();
?>
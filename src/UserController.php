<?php
// src/UserController.php
declare(strict_types=1);

// --- Bufferiza para poder limpiar ruido (BOM/espacios/notices) antes de responder
ob_start();

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';    // Para obtener el ID del usuario logueado
require_once __DIR__ . '/Auditor.php'; // Para registrar logs

/* ================== CARGA TCPDF (Composer o directa) ================== */
(function () {
  if (!class_exists('TCPDF', false)) {
    $tryAutoload = [
      __DIR__ . '/../vendor/autoload.php',
      __DIR__ . '/../../vendor/autoload.php',
    ];
    foreach ($tryAutoload as $path) {
      if (is_file($path)) { require_once $path; break; }
    }
  }
  if (!class_exists('TCPDF', false)) {
    $tryTcpdf = [
      __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php',
      __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php',
      __DIR__ . '/tcpdf/tcpdf.php',
    ];
    foreach ($tryTcpdf as $path) {
      if (is_file($path)) { require_once $path; break; }
    }
  }
})();

// Para PDFs
require_once __DIR__ . '/Reports/UserPdf.php';
require_once __DIR__ . '/Reports/UserDetailPdf.php';

// --- Helpers de respuesta JSON "a prueba de ruido"
function respond($data = null, int $totalRecords = 0): void {
  while (ob_get_level() > 0) { ob_end_clean(); }
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true, 'data' => $data, 'total_records' => $totalRecords], JSON_UNESCAPED_UNICODE);
  exit;
}
function fail(string $msg, int $code = 400): void {
  while (ob_get_level() > 0) { ob_end_clean(); }
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

// --- Conexión
try {
  $pdo = Database::getInstance()->getConnection();
} catch (Throwable $e) {
  fail('DB: ' . $e->getMessage(), 500);
}

// --- Obtener ID del usuario logueado (Se usa en todas las acciones)
$auth = new Auth();
if (!$auth->check()) {
    fail('Usuario no autenticado para esta acción.', 401); // Seguridad
}
$user = $auth->user();
$userId = $user['id'] ?? null;
// Verificar que el usuario sea 'admin' para la mayoría de las acciones
if ($user['role'] !== 'admin' && in_array($_GET['action'] ?? $_POST['action'] ?? '', ['create', 'update', 'delete'])) {
    fail('Acción denegada. Se requiere rol de administrador.', 403);
}


// --- Funciones helper para auditoría
function get_user_data(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT id, email, full_name, role FROM users WHERE id=?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
/** Compara dos arrays (old y new) y devuelve solo los cambios. */
function array_diff_assoc_recursive_user($array1, $array2): array {
    $difference = [];
    foreach ($array1 as $key => $value) {
        if (array_key_exists($key, $array2)) {
            $value2 = $array2[$key];
            // Excluir password y campos internos que no se editan
            if (!in_array($key, ['created_at', 'password'])) {
                if ((string)$value !== (string)$value2) {
                    $difference[$key] = ['old' => $value, 'new' => $value2];
                }
            }
        } else {
             // Caso especial para manejar la password, no la registramos si no se envió
             if ($key === 'password' && empty($array2['password'])) continue;
        }
    }
    return $difference;
}


$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
  switch ($action) {
    
    /* ================= LISTADO (CON PAGINACIÓN) ================= */
    case 'list': {
      $page = (int)($_GET['page'] ?? 1);
      $limit = (int)($_GET['limit'] ?? 10);
      $q = trim($_GET['q'] ?? '');
      
      // Aseguramos valores válidos
      $limit = max(1, min(100, $limit)); 
      $page = max(1, $page);
      $offset = ($page - 1) * $limit;

      // 1. Consulta base y parámetros
      $baseSql = "FROM users";
      $whereSql = "";
      $params = [];
      if ($q !== '') {
        $whereSql = " WHERE full_name LIKE :q1 OR email LIKE :q2";
        $params[':q1'] = "%$q%";
        $params[':q2'] = "%$q%";
      }

      // 2. Contar el total de registros
      $countSql = "SELECT COUNT(id) " . $baseSql . $whereSql;
      $stmtCount = $pdo->prepare($countSql);
      $stmtCount->execute(array_slice($params, 0, count($params))); // Solo parámetros de búsqueda
      $totalRows = (int)$stmtCount->fetchColumn();

      // Ajustar la página si el offset es mayor al total
      $totalPages = ceil($totalRows / $limit);
      if ($page > $totalPages && $totalPages > 0) {
          $page = $totalPages;
          $offset = ($page - 1) * $limit;
      } elseif ($totalRows === 0) {
          $page = 1;
          $offset = 0;
      }

      // 3. Obtener los datos paginados
      $sql = "SELECT id, email, full_name, role, created_at "
           . $baseSql
           . $whereSql
           . " ORDER BY id DESC"
           . " LIMIT :limit OFFSET :offset";
      
      $stmt = $pdo->prepare($sql);
      
      // Bind de los parámetros de búsqueda
      foreach ($params as $key => $value) {
          $stmt->bindValue($key, $value, PDO::PARAM_STR);
      }
      // Bind de LIMIT y OFFSET
      $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
      $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
      $stmt->execute();
      
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      respond($rows, $totalRows); // Enviamos los datos y el total de registros
    }

    /* ================= FIND ================= */
    case 'find': {
      $id = (int)($_GET['id'] ?? 0);
      if(!$id) fail('ID inválido');
      $row = get_user_data($pdo, $id);
      if(!$row) fail('Usuario no encontrado', 404);
      respond($row);
    }

    /* ================= CREATE ================= */
    case 'create': {
      $email = trim($_POST['email'] ?? '');
      $name  = trim($_POST['full_name'] ?? '');
      $pass  = $_POST['password'] ?? '';
      $role  = $_POST['role'] ?? 'employee';

      if($email==='' || $name==='' || strlen($pass)<6 || !in_array($role, ['admin', 'employee'])) {
          fail('Campos requeridos faltantes o inválidos (Email, Nombre, Password >= 6 chars, Rol)');
      }

      $passHash = password_hash($pass, PASSWORD_DEFAULT);
      
      $stmt = $pdo->prepare("INSERT INTO users (email,full_name,password,role) VALUES (?,?,?,?)");
      $stmt->execute([$email,$name,$passHash,$role]);
      $id_user = (int)$pdo->lastInsertId();

      // AUDITORÍA
      Auditor::log($userId, 'users', $id_user, 'CREATE', [
          'email' => $email, 
          'full_name' => $name,
          'role' => $role
      ]);

      respond(['id'=>$id_user]);
    }

    /* ================= UPDATE ================= */
    case 'update': {
      $id    = (int)($_POST['id'] ?? 0);
      if(!$id) fail('ID inválido');
      $old_data = get_user_data($pdo, $id);
      if (!$old_data) fail('Usuario no encontrado', 404);

      $email = trim($_POST['email'] ?? '');
      $name  = trim($_POST['full_name'] ?? '');
      $pass  = $_POST['password'] ?? '';
      $role  = $_POST['role'] ?? 'employee';

      if($email==='' || $name==='' || !in_array($role, ['admin', 'employee'])) {
          fail('Campos requeridos faltantes o rol inválido');
      }

      $fields = ['email=?,full_name=?,role=?'];
      $params = [$email,$name,$role];

      if($pass !== ''){
          if(strlen($pass)<6) fail('La contraseña debe tener al menos 6 caracteres');
          $passHash = password_hash($pass, PASSWORD_DEFAULT);
          $fields[] = 'password=?';
          $params[] = $passHash;
      }

      $sql = "UPDATE users SET " . implode(',', $fields) . " WHERE id=?";
      $params[] = $id;

      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);

      // AUDITORÍA
      $new_data = ['id' => $id, 'email' => $email, 'full_name' => $name, 'role' => $role];
      if($pass !== '') $new_data['password'] = '...changed...';

      $changes = array_diff_assoc_recursive_user($old_data, $new_data);
      if (!empty($changes)) {
          Auditor::log($userId, 'users', $id, 'UPDATE', $changes);
      }
      
      respond(true);
    }

    /* ================= DELETE ================= */
    case 'delete': {
      $id = (int)($_POST['id'] ?? 0);
      if(!$id) fail('ID inválido');
      // No permitir auto-eliminación
      if($id === $userId) fail('No puedes eliminar tu propia cuenta.', 403);

      $old_data = get_user_data($pdo, $id);
      if (!$old_data) fail('Usuario no encontrado', 404);
      
      $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);

      // AUDITORÍA
      Auditor::log($userId, 'users', $id, 'DELETE', [
          'registro_eliminado' => $old_data['full_name'] ?? 'ID ' . $id,
          'email' => $old_data['email']
      ]);
      
      respond(true);
    }

    /* ================== REPORTE GENERAL (PDF) ================== */
    case 'report_general': {
      if (!class_exists('TCPDF')) { while (ob_get_level() > 0) { ob_end_clean(); } http_response_code(500); echo "TCPDF no está disponible."; exit; }
      while (ob_get_level() > 0) { ob_end_clean(); }

      $q = trim($_GET['q'] ?? '');
      $sql = "SELECT id, email, full_name, role, created_at FROM users";
      $params = [];
      if ($q !== '') {
        $sql .= " WHERE full_name LIKE ? OR email LIKE ?";
        $like = "%$q%"; $params = [$like, $like];
      }
      $sql .= " ORDER BY id DESC";
      $stmt = $pdo->prepare($sql);
      $stmt->execute($params);
      $rows = $stmt->fetchAll();

      $pdf = new UserPdf('Usuarios - Reporte General');
      $pdf->output($rows, 'SIRIO S.A. DE C.V.', $q !== '' ? $q : null);
      exit;
    }

    /* ================== REPORTE DETALLE (PDF) ================== */
    case 'report_detalle': {
      if (!class_exists('TCPDF')) { while (ob_get_level() > 0) { ob_end_clean(); } http_response_code(500); echo "TCPDF no está disponible."; exit; }
      while (ob_get_level() > 0) { ob_end_clean(); }

      $id = (int)($_GET['id'] ?? 0);
      if(!$id) { http_response_code(400); echo 'ID de usuario inválido'; exit; }

      $user = get_user_data($pdo, $id);
      if(!$user){ http_response_code(404); echo 'Usuario no encontrado'; exit; }
      
      // Obtener logs de auditoría para el usuario
      $logs = $pdo->prepare("
        SELECT id_log, tabla_afectada, registro_id, accion, fecha_hora, ip_origen, detalle_cambios
        FROM log_auditoria
        WHERE id_usuario = ?
        ORDER BY fecha_hora DESC
      ");
      $logs->execute([$id]);
      $logRows = $logs->fetchAll();

      $pdf = new UserDetailPdf('Ficha de Usuario');
      $pdf->output($user, $logRows, 'SIRIO S.A. DE C.V.');
      exit;
    }

    default:
      fail('Acción no soportada', 404);
  }
} catch (Throwable $e) {
  // Manejo de errores específicos
  if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'email') !== false) {
      fail('El email ingresado ya está registrado.', 409); // Conflict
  }
  fail($e->getMessage(), 500);
}
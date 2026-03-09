<?php
session_start();
if (empty($_SESSION['token'])) {
  header('Location: login.php');
  exit;
}

require_once __DIR__ . '/classe/env.php';
loadEnvFile(__DIR__ . '/.env');
$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$meHttpCode = null;
$meData = null;
if ($apiBase !== '') {
  $ch = curl_init($apiBase . '/auth/me');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $_SESSION['token'],
    'Accept: application/json',
  ]);
  $response = curl_exec($ch);
  $meHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $ch = null;
  if ($response !== false) {
    $decoded = json_decode($response, true);
    if (is_array($decoded)) {
      $meData = $decoded;
    }
  }
  $message = is_array($meData) ? ($meData['message'] ?? '') : '';
  if ($meHttpCode === 401 || $message === 'Unauthenticated.') {
    session_destroy();
    header('Location: login.php');
    exit;
  }
}

function extractEmpresaIdFromToken($token) {
  $parts = explode('.', (string)$token);
  if (count($parts) < 2) {
    return '';
  }
  $payload = strtr($parts[1], '-_', '+/');
  $padding = strlen($payload) % 4;
  if ($padding > 0) {
    $payload .= str_repeat('=', 4 - $padding);
  }
  $decoded = base64_decode($payload, true);
  if ($decoded === false) {
    return '';
  }
  $data = json_decode($decoded, true);
  if (!is_array($data)) {
    return '';
  }
  $empresaId = $data['empresa_id'] ?? $data['empresaId'] ?? '';
  return is_scalar($empresaId) ? (string)$empresaId : '';
}

function extractUserIdFromToken($token) {
  $parts = explode('.', (string)$token);
  if (count($parts) < 2) {
    return '';
  }
  $payload = strtr($parts[1], '-_', '+/');
  $padding = strlen($payload) % 4;
  if ($padding > 0) {
    $payload .= str_repeat('=', 4 - $padding);
  }
  $decoded = base64_decode($payload, true);
  if ($decoded === false) {
    return '';
  }
  $data = json_decode($decoded, true);
  if (!is_array($data)) {
    return '';
  }
  $userId = $data['sub'] ?? $data['user_id'] ?? $data['usuario_id'] ?? '';
  return is_scalar($userId) ? (string)$userId : '';
}

if (empty($_SESSION['empresa_id']) && !empty($_SESSION['token'])) {
  $empresaIdToken = extractEmpresaIdFromToken($_SESSION['token']);
  if ($empresaIdToken !== '') {
    $_SESSION['empresa_id'] = $empresaIdToken;
  }
}

if (empty($_SESSION['user_id']) && !empty($_SESSION['token'])) {
  $userIdToken = extractUserIdFromToken($_SESSION['token']);
  if ($userIdToken !== '') {
    $_SESSION['user_id'] = $userIdToken;
  }
}

$role = strtolower((string)($_SESSION['user_role'] ?? ''));
if ($role !== '') {
  $role = str_replace(['-', ' '], '_', $role);
  if (strpos($role, 'master') !== false) {
    $role = 'admin_master';
  } elseif (strpos($role, 'admin') !== false) {
    $role = 'admin';
  }
  $nameLower = strtolower((string)($_SESSION['user_name'] ?? ''));
  $emailLower = strtolower((string)($_SESSION['user_email'] ?? ''));
  if ($role !== 'admin_master' && (strpos($nameLower, 'master') !== false || $emailLower === 'admin@clickresto.com')) {
    $role = 'admin_master';
  }
  if ($role === '') {
    $role = 'admin';
  }
  $_SESSION['user_role'] = $role;
}
if ($role === '') {
  if ($meHttpCode !== null && $meHttpCode >= 200 && $meHttpCode < 300 && is_array($meData)) {
      $user = $meData['user'] ?? $meData;
      $name = $user['nome'] ?? $user['name'] ?? '';
      $email = $user['email'] ?? '';
      $userId = $user['id'] ?? $user['usuario_id'] ?? $user['user_id'] ?? '';
      $perfil = $user['perfil'] ?? $user['role'] ?? '';
      if (is_array($perfil)) {
        $perfil = $perfil['nome'] ?? $perfil['name'] ?? '';
      }
      $perfilNormalized = is_string($perfil) ? strtolower(trim($perfil)) : '';
      $perfilNormalized = str_replace(['-', ' '], '_', $perfilNormalized);
      $nameLower = is_string($name) ? strtolower($name) : '';
      $emailLower = is_string($email) ? strtolower($email) : '';
      if (strpos($perfilNormalized, 'master') !== false) {
        $perfilNormalized = 'admin_master';
      } elseif (strpos($perfilNormalized, 'admin') !== false) {
        $perfilNormalized = 'admin';
      }
      if ($perfilNormalized !== 'admin_master' && (strpos($nameLower, 'master') !== false || $emailLower === 'admin@clickresto.com')) {
        $perfilNormalized = 'admin_master';
      }
      if ($perfilNormalized === '') {
        $perfilNormalized = 'admin';
      }
      $_SESSION['user_name'] = is_string($name) ? $name : '';
      $_SESSION['user_email'] = is_string($email) ? $email : '';
      $_SESSION['user_role'] = $perfilNormalized;
      if ($userId !== '') {
        $_SESSION['user_id'] = (string)$userId;
      }
      $role = $perfilNormalized;
  }
}
$requested = $_GET['paginas'] ?? '';

if ($requested === '' || $requested === null) {
  $_GET['paginas'] = ($role === 'admin') ? 'HomeResto' : 'home';
  $requested = $_GET['paginas'];
}

$adminMasterPages = [
  'home',
  'empresas',
  'empresa_form',
  'planos',
  'licencas',
  'usuarios',
  'relatorios',
  'configuracoes',
  'permissoes',
  'monitoramento',
];
$adminPages = [
  'homeresto',
];

$requestedNormalized = strtolower((string)$requested);
$requestedNormalized = str_replace(['-', ' '], '_', $requestedNormalized);

if ($role === 'admin' && in_array($requestedNormalized, $adminMasterPages, true)) {
  header('Location: index.php?paginas=HomeResto');
  exit;
}

if ($role === 'admin_master' && in_array($requestedNormalized, $adminPages, true)) {
  header('Location: index.php?paginas=home');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <?php include __DIR__ . '/pages/componentes/head.php'; ?>
  </head>
  <body class="with-welcome-text">
    <div class="container-scroller">
      <!--<?php include __DIR__ . '/pages/componentes/proBanner.php'; ?> -->
      <!-- partial:partials/_navbar.html -->
      <?php include __DIR__ . '/pages/componentes/navbar.php'; ?>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:partials/_sidebar.html -->
        <?php include __DIR__ . '/pages/componentes/sidebar.php'; ?>
        <!-- partial Navegações-->
            <?php //$paginas =  $_GET["paginas"]; ?>
            <?php //include __DIR__ . "/pages/paginas/$paginas.php"; ?>
              <?php
                include_once("classe/verURL.php");
                $url = new verURL();
                $url->trocarURL($_GET["paginas"] ?? '');
                          //echo "teste".$cod_user.$strUsrLevel;
          		?>
        <!-- main-panel ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="assets/vendors/chart.js/chart.umd.js"></script>
    <script src="assets/vendors/progressbar.js/progressbar.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/template.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="assets/js/jquery.cookie.js" type="text/javascript"></script>
    <script src="assets/js/dashboard.js"></script>
    <!-- <script src="assets/js/Chart.roundedBarCharts.js"></script> -->
    <!-- End custom js for this page-->
  </body>
</html>


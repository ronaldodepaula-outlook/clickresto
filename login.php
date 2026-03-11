<?php
session_start();
require_once __DIR__ . '/classe/env.php';
loadEnvFile(__DIR__ . '/.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$loginError = '';
$emailValue = '';

function extractLicencaFromResponse($data) {
  if (!is_array($data)) {
    return null;
  }
  $empresa = $data['empresa'] ?? null;
  if (!is_array($empresa)) {
    return null;
  }
  if (isset($empresa['assinatura_ativa']) && is_array($empresa['assinatura_ativa'])) {
    $assinatura = $empresa['assinatura_ativa'];
    if (isset($assinatura['licenca']) && is_array($assinatura['licenca'])) {
      return $assinatura['licenca'];
    }
  }
  if (isset($empresa['licenca']) && is_array($empresa['licenca'])) {
    return $empresa['licenca'];
  }
  return null;
}

function normalizeLicencaStatus($status) {
  $status = is_string($status) ? trim($status) : '';
  $status = strtolower($status);
  return $status;
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

if (!empty($_SESSION['token'])) {
  header('Location: ?paginas=home');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $emailValue = trim((string)($_POST['email'] ?? ''));
  $senha = (string)($_POST['senha'] ?? '');

  if ($apiBase === '') {
    $loginError = 'API_BASE_URL nao configurada no arquivo .env.';
  } elseif ($emailValue === '' || $senha === '') {
    $loginError = 'Informe e-mail e senha.';
  } else {
    $payload = json_encode([
      'email' => $emailValue,
      'senha' => $senha,
    ]);

    $ch = curl_init($apiBase . '/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $ch = null;

    if ($response === false) {
      $loginError = 'Falha ao conectar na API. ' . $curlError;
    } else {
      $data = json_decode($response, true);
      if ($httpCode >= 200 && $httpCode < 300 && isset($data['access_token'])) {
        $licenca = extractLicencaFromResponse($data);
        $licencaStatus = '';
        $licencaMensagem = '';
        $licencaDiasRestantes = null;
        $licencaDiasExpirados = null;
        $licencaDuracaoDias = null;
        $licencaDuracaoMeses = null;
        $licencaDuracaoAnos = null;
        if (is_array($licenca)) {
          $licencaStatus = normalizeLicencaStatus($licenca['status'] ?? '');
          $licencaMensagem = is_string($licenca['mensagem'] ?? null) ? (string)$licenca['mensagem'] : '';
          $licencaDiasRestantes = $licenca['dias_restantes'] ?? null;
          $licencaDiasExpirados = $licenca['dias_expirados'] ?? null;
          $licencaDuracaoDias = $licenca['duracao_dias'] ?? null;
          $licencaDuracaoMeses = $licenca['duracao_meses'] ?? null;
          $licencaDuracaoAnos = $licenca['duracao_anos'] ?? null;
        }

        unset(
          $_SESSION['licenca_status'],
          $_SESSION['licenca_mensagem'],
          $_SESSION['licenca_dias_restantes'],
          $_SESSION['licenca_dias_expirados'],
          $_SESSION['licenca_duracao_dias'],
          $_SESSION['licenca_duracao_meses'],
          $_SESSION['licenca_duracao_anos']
        );

        if ($licencaStatus === 'expirada') {
          $loginError = $licencaMensagem !== '' ? $licencaMensagem : 'Licenca expirada';
          $_SESSION['licenca_status'] = $licencaStatus;
          $_SESSION['licenca_mensagem'] = $loginError;
        } else {
          if ($licencaStatus === 'ok' && $licencaMensagem === '') {
            $licencaMensagem = 'Licenca ok';
          }

          $_SESSION['token'] = $data['access_token'];
          $_SESSION['licenca_status'] = $licencaStatus;
          if ($licencaMensagem !== '') {
            $_SESSION['licenca_mensagem'] = $licencaMensagem;
          }
          if (is_numeric($licencaDiasRestantes)) {
            $_SESSION['licenca_dias_restantes'] = (int)$licencaDiasRestantes;
          }
          if (is_numeric($licencaDiasExpirados)) {
            $_SESSION['licenca_dias_expirados'] = (int)$licencaDiasExpirados;
          }
          if (is_numeric($licencaDuracaoDias)) {
            $_SESSION['licenca_duracao_dias'] = (int)$licencaDuracaoDias;
          }
          if (is_numeric($licencaDuracaoMeses)) {
            $_SESSION['licenca_duracao_meses'] = (int)$licencaDuracaoMeses;
          }
          if (is_numeric($licencaDuracaoAnos)) {
            $_SESSION['licenca_duracao_anos'] = (int)$licencaDuracaoAnos;
          }

          $loginNome = $data['nome'] ?? '';
          $loginEmail = $data['email'] ?? '';
          $loginEmpresaId = $data['empresa_id'] ?? '';
          $loginUserId = $data['id'] ?? '';
          $perfilLogin = '';
          $perfilIdLogin = '';
          if (!empty($data['perfis']) && is_array($data['perfis'])) {
            $perfilItem = $data['perfis'][0] ?? null;
            if (is_object($perfilItem)) {
              $perfilItem = (array)$perfilItem;
            }
            if (is_array($perfilItem)) {
              $perfilLogin = $perfilItem['nome'] ?? $perfilItem['name'] ?? '';
              $perfilIdLogin = $perfilItem['id'] ?? '';
            }
          }

        $_SESSION['user_name'] = '';
        $_SESSION['user_email'] = '';

        $meCh = curl_init($apiBase . '/auth/me');
        curl_setopt($meCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($meCh, CURLOPT_HTTPHEADER, [
          'Authorization: Bearer ' . $data['access_token'],
          'Accept: application/json',
        ]);
        $meResponse = curl_exec($meCh);
        $meCode = curl_getinfo($meCh, CURLINFO_HTTP_CODE);
        $meCh = null;

        if ($meResponse !== false && $meCode >= 200 && $meCode < 300) {
          $meData = json_decode($meResponse, true);
          $user = $meData['user'] ?? $meData;
          $name = $user['nome'] ?? $user['name'] ?? '';
          $email = $user['email'] ?? '';
          $empresaId = $user['empresa_id'] ?? $user['empresaId'] ?? '';
          $userId = $user['id'] ?? $user['usuario_id'] ?? $user['user_id'] ?? '';
          $perfil = $user['perfil'] ?? $user['role'] ?? '';
          if (is_array($perfil)) {
            $perfil = $perfil['nome'] ?? $perfil['name'] ?? '';
          }
          if ($perfilLogin !== '') {
            $perfil = $perfilLogin;
          }
          $perfilId = $perfilIdLogin !== '' ? $perfilIdLogin : ($user['perfil_id'] ?? $user['perfilId'] ?? '');
          $perfilNormalized = is_string($perfil) ? strtolower(trim($perfil)) : '';
          $perfilNormalized = str_replace(['-', ' '], '_', $perfilNormalized);
          $nameLower = is_string($name) ? strtolower($name) : '';
          $emailLower = is_string($email) ? strtolower($email) : '';
          if (strpos($perfilNormalized, 'cozinha') !== false) {
            $perfilNormalized = 'cozinha';
          } elseif (strpos($perfilNormalized, 'master') !== false) {
            $perfilNormalized = 'admin_master';
          } elseif (strpos($perfilNormalized, 'admin') !== false) {
            $perfilNormalized = 'admin';
          }
          if ($perfilNormalized !== 'admin_master' && $perfilNormalized !== 'cozinha' && (strpos($nameLower, 'master') !== false || $emailLower === 'admin@clickresto.com')) {
            $perfilNormalized = 'admin_master';
          }
          if ($perfilNormalized === '') {
            $perfilNormalized = 'admin';
          }
          $_SESSION['user_name'] = is_string($name) ? $name : '';
          $_SESSION['user_email'] = is_string($email) ? $email : '';
          $_SESSION['user_role'] = $perfilNormalized;
          if ($perfil !== '') {
            $_SESSION['user_profile_name'] = is_string($perfil) ? $perfil : '';
          }
          if ($perfilId !== '') {
            $_SESSION['user_profile_id'] = (string)$perfilId;
          }
          if ($userId !== '') {
            $_SESSION['user_id'] = (string)$userId;
          }
          if ($empresaId !== '') {
            $_SESSION['empresa_id'] = (string)$empresaId;
          }
        } else {
          $perfilFallback = $perfilLogin;
          $perfilNormalized = is_string($perfilFallback) ? strtolower(trim($perfilFallback)) : '';
          $perfilNormalized = str_replace(['-', ' '], '_', $perfilNormalized);
          if ($perfilNormalized !== '') {
            if (strpos($perfilNormalized, 'cozinha') !== false) {
              $perfilNormalized = 'cozinha';
            } elseif (strpos($perfilNormalized, 'master') !== false) {
              $perfilNormalized = 'admin_master';
            } elseif (strpos($perfilNormalized, 'admin') !== false) {
              $perfilNormalized = 'admin';
            }
            if ($perfilNormalized === '') {
              $perfilNormalized = 'admin';
            }
            $_SESSION['user_role'] = $perfilNormalized;
          }
          if ($loginNome !== '') {
            $_SESSION['user_name'] = (string)$loginNome;
          }
          if ($loginEmail !== '') {
            $_SESSION['user_email'] = (string)$loginEmail;
          }
          if ($loginUserId !== '') {
            $_SESSION['user_id'] = (string)$loginUserId;
          }
          if ($loginEmpresaId !== '') {
            $_SESSION['empresa_id'] = (string)$loginEmpresaId;
          }
          if ($perfilLogin !== '') {
            $_SESSION['user_profile_name'] = (string)$perfilLogin;
          }
          if ($perfilIdLogin !== '') {
            $_SESSION['user_profile_id'] = (string)$perfilIdLogin;
          }
        }

        if (empty($_SESSION['empresa_id'])) {
          $empresaIdToken = extractEmpresaIdFromToken($_SESSION['token']);
          if ($empresaIdToken !== '') {
            $_SESSION['empresa_id'] = $empresaIdToken;
          }
        }

        if (empty($_SESSION['user_id'])) {
          $userIdToken = extractUserIdFromToken($_SESSION['token']);
          if ($userIdToken !== '') {
            $_SESSION['user_id'] = $userIdToken;
          }
        }

        $role = strtolower((string)($_SESSION['user_role'] ?? ''));
        if ($role === 'cozinha') {
          header('Location: index.php?paginas=gertao_cozinha');
        } elseif ($role === 'admin') {
          header('Location: index.php?paginas=HomeResto');
        } else {
          header('Location: index.php?paginas=home');
        }
          exit;
        }
      }
      if ($loginError === '') {
        $loginError = $data['message'] ?? 'Credenciais invalidas.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- head.php simplificado para este exemplo -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login clean & professional</title>
  <!-- Bootstrap + Tela icons (versão simplificada, sem vendor) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/themify-icons@0.1.2/css/themify-icons.css">
  <style>
    /* Ajustes finos para um visual mais elegante */
    html, body {
      height: 100%;
    }
    body {
      margin: 0;
      background-color: #f9fafc;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }
    .container-scroller,
    .page-body-wrapper,
    .full-page-wrapper,
    .content-wrapper {
      height: 100%;
      min-height: 100vh;
    }
    .content-wrapper {
      overflow: hidden;
    }
    .row.flex-grow.g-0.w-100 {
      min-height: 100vh;
    }
    .auth-img-bg {
      background-color: #ffffff;
    }
    .login-half-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      position: relative;
      overflow: hidden;
      border-radius: 0 0 0 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-half-bg::before {
      content: "";
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      background: url('https://images.unsplash.com/photo-1557804506-669a67965ba0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1267&q=80') center/cover no-repeat;
      opacity: 0.25;
      mix-blend-mode: overlay;
    }
    .right-image-content {
      position: relative;
      z-index: 2;
      text-align: center;
      color: white;
      max-width: 80%;
      margin: 0 auto;
    }
    .right-image-content h2 {
      font-weight: 300;
      font-size: 2.2rem;
      letter-spacing: 1px;
      text-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
    .right-image-content p {
      font-weight: 300;
      opacity: 0.9;
      margin-top: 1rem;
    }
    .brand-logo img {
      max-height: 45px;
      margin-bottom: 0.5rem;
    }
    .auth-form-transparent {
      max-width: 380px;
      width: 100%;
      padding: 2rem 1.5rem !important;
      background: rgba(255,255,255,0.9);
      border-radius: 24px;
      box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08);
      backdrop-filter: blur(4px);
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    .form-group label {
      font-weight: 500;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.02em;
      color: #4a4a4a;
      margin-bottom: 0.4rem;
    }
    .input-group-text {
      border: 1px solid #e0e4e8;
      border-right: none;
      background: white;
      padding: 0.75rem 1rem;
    }
    .form-control {
      border: 1px solid #e0e4e8;
      border-left: none;
      padding: 0.75rem 1rem;
      font-size: 0.95rem;
      background: white;
      transition: border-color 0.2s;
    }
    .form-control:focus {
      border-color: #667eea;
      box-shadow: none;
      outline: none;
    }
    .input-group:focus-within .input-group-text {
      border-color: #667eea;
    }
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 12px;
      padding: 0.8rem;
      font-weight: 500;
      letter-spacing: 0.02em;
      box-shadow: 0 8px 15px -5px rgba(102, 126, 234, 0.4);
      transition: all 0.2s;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #5a6fd6 0%, #6a4292 100%);
      transform: translateY(-2px);
      box-shadow: 0 12px 20px -8px rgba(102, 126, 234, 0.5);
    }
    .btn-facebook, .btn-google {
      border-radius: 30px;
      font-size: 0.9rem;
      padding: 0.5rem;
      border: 1px solid #e4e6eb;
      background: white;
      color: #333;
      transition: background 0.2s;
    }
    .btn-facebook:hover {
      background: #f0f2f5;
    }
    .btn-google:hover {
      background: #f0f2f5;
    }
    .form-check-label {
      font-size: 0.9rem;
      color: #555;
    }
    .auth-link {
      color: #667eea;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
    }
    .auth-link:hover {
      text-decoration: underline;
    }
    .text-primary {
      color: #667eea !important;
      font-weight: 500;
    }
    .fw-light {
      font-weight: 350;
      color: #5f5f5f;
    }
    .my-2.d-flex {
      margin-top: 1.2rem !important;
    }
    /* responsividade */
    @media (max-width: 991px) {
      .login-half-bg { min-height: 250px; border-radius: 0; }
      .auth-form-transparent { max-width: 90%; margin: 0 auto; }
    }
  </style>
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg p-0">
        <div class="row flex-grow g-0 w-100">
          <!-- Coluna esquerda: formulário refinado -->
          <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="auth-form-transparent text-left p-4">
              <div class="brand-logo mb-4">
                <img src="https://via.placeholder.com/150x45/667eea/ffffff?text=Your+Logo" alt="logo" style="background: #667eea; padding: 8px 15px; border-radius: 30px;">
                <!-- Substitua pelo seu logo real -->
              </div>
              <h4 class="mb-1 fw-semibold" style="color: #1e1e2f;">Bem-vindo de volta!</h4>
              <p class="text-secondary mb-4" style="font-size: 0.95rem;">Entre com suas credenciais para continuar.</p>

              <?php if ($loginError !== ''): ?>
                <div class="alert alert-danger py-2" role="alert" style="font-size: 0.9rem;">
                  <?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?>
                </div>
              <?php endif; ?>

              <form class="pt-2" method="POST" action="">
                <div class="form-group">
                  <label for="email" class="text-muted">E-mail</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                      <i class="ti-user text-primary" style="font-size: 1.1rem;"></i>
                    </span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" id="email" placeholder="nome@empresa.com" value="<?php echo htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8'); ?>" style="background: white;">
                  </div>
                </div>
                <div class="form-group">
                  <label for="password" class="text-muted">Senha</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                      <i class="ti-lock text-primary" style="font-size: 1.1rem;"></i>
                    </span>
                    <input type="password" name="senha" class="form-control border-start-0 ps-0" id="password" placeholder="********" style="background: white;">
                  </div>
                </div>

                <div class="d-flex justify-content-between align-items-center my-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" style="border-color: #ccc;">
                    <label class="form-check-label text-muted" for="remember" style="font-size: 0.9rem;">
                      Keep me signed in
                    </label>
                  </div>
                  <a href="recuperar_senha.php" class="auth-link">Forgot password?</a>
                </div>

                <button class="btn btn-primary w-100 mb-4 py-2" type="submit">ENTRAR</button>

                <div class="d-flex gap-2 mb-4">
                  <button class="btn btn-facebook auth-form-btn flex-grow-1 d-flex align-items-center justify-content-center gap-2" type="button">
                    <i class="ti-facebook"></i> Facebook
                  </button>
                  <button class="btn btn-google auth-form-btn flex-grow-1 d-flex align-items-center justify-content-center gap-2" type="button">
                    <i class="ti-google"></i> Google
                  </button>
                </div>

                <div class="text-center">
                  <span class="text-muted" style="font-size: 0.95rem;">Nao tem uma conta?</span>
                  <a href="novo_acesso.php" class="text-primary ms-1 fw-semibold">Criar acesso</a>
                </div>
              </form>
            </div>
          </div>

          <!-- Coluna direita: imagem profissional e elegante (sem poluição) -->
          <div class="col-lg-6 login-half-bg d-flex flex-column align-items-center justify-content-center">
            <div class="right-image-content">
              <!-- Imagem de fundo já está no CSS, mas podemos colocar um conteúdo leve sobre ela -->
              <h2 class="display-6 fw-light text-white">Secure & modern</h2>
              <p class="lead text-white-50">Access your dashboard with confidence</p>
              <div style="margin-top: 2rem;">
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill me-2">#encrypted</span>
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill">#cloud</span>
              </div>
              <!-- copy ajustado para ficar integrado -->
            </div>
            <p class="text-white-50 small position-absolute bottom-0 mb-3">Copyright &copy; 2025 All rights reserved.</p>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->

  <!-- scripts mantidos apenas os essenciais (simulação) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Poderia incluir os scripts originais, mas para demo são opcionais -->
</body>
</html>


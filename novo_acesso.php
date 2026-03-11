<?php
require_once __DIR__ . '/classe/env.php';
loadEnvFile(__DIR__ . '/.env');

$apiBase = rtrim((string)env('API_BASE_URL', ''), '/');
$apiError = '';
$apiSuccess = '';
$apiResult = null;

$form = [
  'empresa' => [
    'nome' => '',
    'nome_fantasia' => '',
    'cnpj' => '',
    'telefone' => '',
    'email' => '',
    'endereco' => '',
    'cidade' => '',
    'estado' => '',
  ],
  'usuario' => [
    'nome' => '',
    'email' => '',
    'senha' => '',
    'confirmar' => '',
  ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form['empresa']['nome'] = trim((string)($_POST['empresa']['nome'] ?? ''));
  $form['empresa']['nome_fantasia'] = trim((string)($_POST['empresa']['nome_fantasia'] ?? ''));
  $form['empresa']['cnpj'] = trim((string)($_POST['empresa']['cnpj'] ?? ''));
  $form['empresa']['telefone'] = trim((string)($_POST['empresa']['telefone'] ?? ''));
  $form['empresa']['email'] = trim((string)($_POST['empresa']['email'] ?? ''));
  $form['empresa']['endereco'] = trim((string)($_POST['empresa']['endereco'] ?? ''));
  $form['empresa']['cidade'] = trim((string)($_POST['empresa']['cidade'] ?? ''));
  $form['empresa']['estado'] = trim((string)($_POST['empresa']['estado'] ?? ''));

  $form['usuario']['nome'] = trim((string)($_POST['usuario']['nome'] ?? ''));
  $form['usuario']['email'] = trim((string)($_POST['usuario']['email'] ?? ''));
  $form['usuario']['senha'] = (string)($_POST['usuario']['senha'] ?? '');
  $form['usuario']['confirmar'] = (string)($_POST['usuario']['confirmar'] ?? '');

  $requiredEmpresa = [
    'nome',
    'nome_fantasia',
    'cnpj',
    'telefone',
    'email',
    'endereco',
    'cidade',
    'estado',
  ];
  $requiredUsuario = [
    'nome',
    'email',
    'senha',
    'confirmar',
  ];

  $missing = false;
  foreach ($requiredEmpresa as $field) {
    if ($form['empresa'][$field] === '') {
      $missing = true;
      break;
    }
  }
  if (!$missing) {
    foreach ($requiredUsuario as $field) {
      if ($form['usuario'][$field] === '') {
        $missing = true;
        break;
      }
    }
  }

  if ($apiBase === '') {
    $apiError = 'API_BASE_URL nao configurada no arquivo .env.';
  } elseif ($missing) {
    $apiError = 'Preencha todos os campos obrigatorios.';
  } elseif ($form['usuario']['senha'] !== $form['usuario']['confirmar']) {
    $apiError = 'As senhas nao conferem.';
  } else {
    $payload = json_encode([
      'empresa' => [
        'nome' => $form['empresa']['nome'],
        'nome_fantasia' => $form['empresa']['nome_fantasia'],
        'cnpj' => $form['empresa']['cnpj'],
        'telefone' => $form['empresa']['telefone'],
        'email' => $form['empresa']['email'],
        'endereco' => $form['empresa']['endereco'],
        'cidade' => $form['empresa']['cidade'],
        'estado' => $form['empresa']['estado'],
      ],
      'usuario' => [
        'nome' => $form['usuario']['nome'],
        'email' => $form['usuario']['email'],
        'senha' => $form['usuario']['senha'],
      ],
    ]);

    $ch = curl_init($apiBase . '/public/cadastro-trial');
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
      $apiError = 'Falha ao conectar na API. ' . $curlError;
    } else {
      $decoded = json_decode($response, true);
      if ($httpCode >= 200 && $httpCode < 300 && is_array($decoded)) {
        $apiResult = $decoded;
        $apiSuccess = 'Cadastro realizado com sucesso.';
      } else {
        $apiError = $decoded['message'] ?? 'Nao foi possivel concluir o cadastro.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Novo acesso - Cadastro Trial</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/themify-icons@0.1.2/css/themify-icons.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100vh;
      overflow: hidden;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .container-scroller,
    .page-body-wrapper,
    .full-page-wrapper,
    .content-wrapper,
    .row.flex-grow {
      height: 100vh;
      max-height: 100vh;
      overflow: hidden;
    }

    .auth-img-bg {
      background-color: #ffffff;
    }

    .login-half-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      position: relative;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
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

    .form-container {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      overflow: hidden;
    }

    .auth-form-transparent {
      width: 100%;
      max-width: 820px;
      margin: 0 auto;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 28px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      backdrop-filter: blur(8px);
      padding: 1.25rem !important;
      max-height: calc(100vh - 2rem);
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: #cbd5e0 #f1f5f9;
    }

    .auth-form-transparent::-webkit-scrollbar {
      width: 4px;
    }

    .auth-form-transparent::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 10px;
    }

    .auth-form-transparent::-webkit-scrollbar-thumb {
      background: #cbd5e0;
      border-radius: 10px;
    }

    .brand-logo {
      margin-bottom: 0.75rem;
    }

    .brand-logo img {
      max-height: 38px;
      border-radius: 30px;
    }

    .wizard-steps {
      display: flex;
      gap: 0.4rem;
      flex-wrap: wrap;
      margin-bottom: 0.75rem;
    }

    .wizard-step {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.25rem 0.6rem;
      border-radius: 999px;
      background: #eef1f6;
      color: #5b6370;
      font-size: 0.75rem;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .wizard-step.active {
      background: #667eea;
      color: #fff;
      transform: scale(1.02);
    }

    .wizard-step.completed {
      background: #10b981;
      color: #fff;
    }

    .wizard-step .step-index {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: rgba(255,255,255,0.2);
      font-size: 0.7rem;
      font-weight: 700;
    }

    .wizard-content {
      position: relative;
      min-height: 320px;
    }

    .wizard-pane {
      display: none;
      animation: fadeIn 0.5s ease;
    }

    .wizard-pane.active {
      display: block;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .wizard-section {
      border: 1px solid #eef1f6;
      border-radius: 16px;
      padding: 0.75rem;
      margin-bottom: 0.75rem;
      background: #ffffff;
    }

    .wizard-section h6 {
      font-weight: 700;
      margin-bottom: 0.5rem;
      color: #1e1e2f;
      font-size: 0.9rem;
    }

    .form-group {
      margin-bottom: 0.75rem;
    }

    .form-group label {
      font-weight: 500;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.02em;
      color: #4a4a4a;
      margin-bottom: 0.2rem;
    }

    .input-group-text {
      border: 1px solid #e0e4e8;
      border-right: none;
      background: white;
      padding: 0.4rem 0.8rem;
    }

    .form-control {
      border: 1px solid #e0e4e8;
      border-left: none;
      padding: 0.4rem 0.8rem;
      font-size: 0.85rem;
      background: white;
      transition: all 0.2s;
    }

    .form-control:focus {
      border-color: #667eea;
      box-shadow: none;
      outline: none;
    }

    .form-control.is-invalid {
      border-color: #dc3545;
    }

    .input-group:focus-within .input-group-text {
      border-color: #667eea;
    }

    .invalid-feedback {
      font-size: 0.7rem;
      margin-top: 0.2rem;
    }

    .wizard-actions {
      display: flex;
      gap: 0.75rem;
      align-items: center;
      justify-content: space-between;
      margin-top: 0.75rem;
    }

    .btn {
      padding: 0.5rem 1rem;
      font-size: 0.85rem;
      border-radius: 12px;
      font-weight: 500;
      transition: all 0.2s;
    }

    .btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      box-shadow: 0 8px 15px -5px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:hover:not(:disabled) {
      background: linear-gradient(135deg, #5a6fd6 0%, #6a4292 100%);
      transform: translateY(-1px);
      box-shadow: 0 12px 20px -8px rgba(102, 126, 234, 0.5);
    }

    .btn-outline-primary {
      border-color: #667eea;
      color: #667eea;
    }

    .btn-outline-primary:hover:not(:disabled) {
      background: #667eea;
      color: white;
    }

    .btn-success {
      background: #10b981;
      border: none;
    }

    .progress {
      height: 4px;
      margin: 0.5rem 0;
      border-radius: 2px;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      padding: 0.4rem 0;
      border-bottom: 1px dashed #eef1f6;
      font-size: 0.85rem;
    }

    .summary-item:last-child {
      border-bottom: none;
    }

    .summary-label {
      color: #6c757d;
      font-weight: 500;
    }

    .summary-value {
      color: #1e1e2f;
      font-weight: 600;
    }

    .info-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: #f5f6ff;
      color: #4f46e5;
      padding: 0.35rem 0.6rem;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    @media (max-width: 991px) {
      .login-half-bg {
        display: none;
      }

      .col-lg-6 {
        width: 100%;
      }

      .auth-form-transparent {
        max-width: 600px;
      }
    }

    h4 {
      font-size: 1.25rem;
      margin-bottom: 0.2rem !important;
    }

    .text-secondary {
      font-size: 0.8rem;
      margin-bottom: 0.75rem !important;
    }
  </style>
</head>
<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg p-0">
        <div class="row flex-grow g-0 w-100">
          <div class="col-lg-6">
            <div class="form-container">
              <div class="auth-form-transparent text-left p-3">
                <div class="brand-logo">
                  <img src="https://via.placeholder.com/130x38/667eea/ffffff?text=Your+Logo" alt="logo">
                </div>

                <h4 class="fw-semibold" style="color: #1e1e2f;">Criar novo acesso</h4>
                <p class="text-secondary mb-2">Preencha os dados para iniciar sua conta trial.</p>

                <?php if ($apiError !== ''): ?>
                  <div class="alert alert-danger py-2" role="alert" style="font-size: 0.85rem;">
                    <?php echo htmlspecialchars($apiError, ENT_QUOTES, 'UTF-8'); ?>
                  </div>
                <?php endif; ?>

                <?php if ($apiSuccess !== '' && is_array($apiResult)): ?>
                  <div class="wizard-section">
                    <h6>Cadastro concluido com sucesso</h6>
                    <p class="text-secondary" style="font-size: 0.85rem;">
                      Sua conta foi criada. Agora voce pode acessar o sistema usando seus dados.
                    </p>
                    <a class="btn btn-success mt-2" href="login.php">Ir para login</a>
                  </div>
                <?php else: ?>
                  <div class="progress">
                    <div class="progress-bar bg-primary" id="progressBar" role="progressbar" style="width: 25%"></div>
                  </div>

                <div class="wizard-steps" id="wizardSteps">
                  <div class="wizard-step active" data-step="1">
                    <span class="step-index">1</span> Empresa
                  </div>
                  <div class="wizard-step" data-step="2">
                    <span class="step-index">2</span> Usuario
                  </div>
                  <div class="wizard-step" data-step="3">
                    <span class="step-index">3</span> Plano & Licenca
                  </div>
                  <div class="wizard-step" data-step="4">
                    <span class="step-index">4</span> Confirmacao
                  </div>
                </div>

                <form id="wizardForm" method="POST" action="">
                  <div class="wizard-content">
                    <div class="wizard-pane active" id="step1">
                      <div class="wizard-section">
                        <h6>Dados da Empresa</h6>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Nome *</label>
                              <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 p-2">
                                  <i class="ti-briefcase text-primary" style="font-size: 1rem;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-1" id="empresaNome" name="empresa[nome]" placeholder="Razao social" value="<?php echo htmlspecialchars($form['empresa']['nome'], ENT_QUOTES, 'UTF-8'); ?>" required>
                              </div>
                              <div class="invalid-feedback" id="empresaNomeError"></div>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Nome Fantasia *</label>
                              <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 p-2">
                                  <i class="ti-id-badge text-primary" style="font-size: 1rem;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-1" id="empresaFantasia" name="empresa[nome_fantasia]" placeholder="Nome fantasia" value="<?php echo htmlspecialchars($form['empresa']['nome_fantasia'], ENT_QUOTES, 'UTF-8'); ?>" required>
                              </div>
                              <div class="invalid-feedback" id="empresaFantasiaError"></div>
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>CNPJ *</label>
                              <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 p-2">
                                  <i class="ti-receipt text-primary" style="font-size: 1rem;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-1" id="empresaCnpj" name="empresa[cnpj]" placeholder="00.000.000/0000-00" value="<?php echo htmlspecialchars($form['empresa']['cnpj'], ENT_QUOTES, 'UTF-8'); ?>" required>
                              </div>
                              <div class="invalid-feedback" id="empresaCnpjError"></div>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Telefone *</label>
                              <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 p-2">
                                  <i class="ti-mobile text-primary" style="font-size: 1rem;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-1" id="empresaTelefone" name="empresa[telefone]" placeholder="(00) 00000-0000" value="<?php echo htmlspecialchars($form['empresa']['telefone'], ENT_QUOTES, 'UTF-8'); ?>" required>
                              </div>
                              <div class="invalid-feedback" id="empresaTelefoneError"></div>
                            </div>
                          </div>
                        </div>

                        <div class="form-group">
                          <label>Email da Empresa *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-email text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="email" class="form-control border-start-0 ps-1" id="empresaEmail" name="empresa[email]" placeholder="contato@empresa.com" value="<?php echo htmlspecialchars($form['empresa']['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                          </div>
                          <div class="invalid-feedback" id="empresaEmailError"></div>
                        </div>

                        <div class="form-group">
                          <label>Endereco *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-location-pin text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-1" id="empresaEndereco" name="empresa[endereco]" placeholder="Rua, numero, bairro" value="<?php echo htmlspecialchars($form['empresa']['endereco'], ENT_QUOTES, 'UTF-8'); ?>" required>
                          </div>
                          <div class="invalid-feedback" id="empresaEnderecoError"></div>
                        </div>

                        <div class="row">
                          <div class="col-md-7">
                            <div class="form-group">
                              <label>Cidade *</label>
                              <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 p-2">
                                  <i class="ti-map-alt text-primary" style="font-size: 1rem;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-1" id="empresaCidade" name="empresa[cidade]" placeholder="Cidade" value="<?php echo htmlspecialchars($form['empresa']['cidade'], ENT_QUOTES, 'UTF-8'); ?>" required>
                              </div>
                              <div class="invalid-feedback" id="empresaCidadeError"></div>
                            </div>
                          </div>
                          <div class="col-md-5">
                            <div class="form-group">
                              <label>Estado *</label>
                              <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 p-2">
                                  <i class="ti-flag text-primary" style="font-size: 1rem;"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 ps-1" id="empresaEstado" name="empresa[estado]" placeholder="UF" value="<?php echo htmlspecialchars($form['empresa']['estado'], ENT_QUOTES, 'UTF-8'); ?>" required>
                              </div>
                              <div class="invalid-feedback" id="empresaEstadoError"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="wizard-pane" id="step2">
                      <div class="wizard-section">
                        <h6>Dados do Usuario Administrador</h6>
                        <div class="form-group">
                          <label>Nome *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-user text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-1" id="usuarioNome" name="usuario[nome]" placeholder="Nome completo" value="<?php echo htmlspecialchars($form['usuario']['nome'], ENT_QUOTES, 'UTF-8'); ?>" required>
                          </div>
                          <div class="invalid-feedback" id="usuarioNomeError"></div>
                        </div>

                        <div class="form-group">
                          <label>Email *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-email text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="email" class="form-control border-start-0 ps-1" id="usuarioEmail" name="usuario[email]" placeholder="admin@empresa.com" value="<?php echo htmlspecialchars($form['usuario']['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                          </div>
                          <div class="invalid-feedback" id="usuarioEmailError"></div>
                        </div>

                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Senha *</label>
                              <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 p-2">
                                  <i class="ti-lock text-primary" style="font-size: 1rem;"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-1" id="usuarioSenha" name="usuario[senha]" placeholder="Crie uma senha" required>
                              </div>
                              <div class="invalid-feedback" id="usuarioSenhaError"></div>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label>Confirmar Senha *</label>
                              <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 p-2">
                                  <i class="ti-lock text-primary" style="font-size: 1rem;"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 ps-1" id="usuarioConfirmar" name="usuario[confirmar]" placeholder="Repita a senha" required>
                              </div>
                              <div class="invalid-feedback" id="usuarioConfirmarError"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="wizard-pane" id="step3">
                      <div class="wizard-section">
                        <h6>Plano e Licenca</h6>
                        <div class="d-flex flex-wrap gap-2">
                          <span class="info-pill"><i class="ti-star"></i> Plano Trial Automatico</span>
                          <span class="info-pill"><i class="ti-time"></i> Licenca gerada no cadastro</span>
                          <span class="info-pill"><i class="ti-check"></i> Ativacao imediata</span>
                        </div>
                        <div class="mt-3">
                          <p class="text-secondary mb-2" style="font-size: 0.85rem;">
                            Ao finalizar, sua empresa sera criada no plano trial e a licenca sera ativada automaticamente.
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="wizard-pane" id="step4">
                      <div class="wizard-section">
                        <h6>Resumo e Confirmacao</h6>
                        <div id="summary"></div>
                        <?php if (is_array($apiResult)): ?>
                          <div class="mt-3">
                            <div class="summary-item">
                              <span class="summary-label">Plano:</span>
                              <span class="summary-value"><?php echo htmlspecialchars((string)($apiResult['plano']['nome'] ?? 'Trial'), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="summary-item">
                              <span class="summary-label">Status assinatura:</span>
                              <span class="summary-value"><?php echo htmlspecialchars((string)($apiResult['assinatura']['status'] ?? 'trial'), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="summary-item">
                              <span class="summary-label">Periodo:</span>
                              <span class="summary-value"><?php echo htmlspecialchars((string)($apiResult['assinatura']['data_inicio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> ate <?php echo htmlspecialchars((string)($apiResult['assinatura']['data_fim'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <?php if (!empty($apiResult['confirmacao_url'])): ?>
                              <div class="summary-item">
                                <span class="summary-label">Confirmacao:</span>
                                <span class="summary-value">Email pendente</span>
                              </div>
                            <?php endif; ?>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>

                  <div class="wizard-actions">
                    <button type="button" class="btn btn-outline-primary px-3" id="prevBtn" onclick="changeStep(-1)" disabled>Anterior</button>
                    <button type="button" class="btn btn-primary px-4" id="nextBtn" onclick="changeStep(1)">PROXIMA ETAPA</button>
                    <button type="button" class="btn btn-success px-4" id="submitBtn" style="display: none;" onclick="submitForm()">FINALIZAR</button>
                  </div>
                </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-lg-6 login-half-bg d-flex flex-column align-items-center justify-content-center">
            <div class="right-image-content">
              <h2 class="display-6 fw-light text-white">Secure & modern</h2>
              <p class="lead text-white-50">Access your dashboard with confidence</p>
              <div style="margin-top: 2rem;">
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill me-2">#encrypted</span>
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill">#cloud</span>
              </div>
            </div>
            <p class="text-white-50 small position-absolute bottom-0 mb-3">Copyright &copy; 2026 All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let currentStep = 1;
    const totalSteps = 4;

    const steps = document.querySelectorAll('.wizard-step');
    const panes = document.querySelectorAll('.wizard-pane');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');
    const form = document.getElementById('wizardForm');

    const startStep = <?php echo is_array($apiResult) ? 4 : 1; ?>;

    function updateDisplay() {
      steps.forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('active', 'completed');

        if (stepNum === currentStep) {
          step.classList.add('active');
        } else if (stepNum < currentStep) {
          step.classList.add('completed');
        }
      });

      panes.forEach((pane, index) => {
        if (index + 1 === currentStep) {
          pane.classList.add('active');
        } else {
          pane.classList.remove('active');
        }
      });

      prevBtn.disabled = currentStep === 1;

      if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'block';
      } else {
        nextBtn.style.display = 'block';
        submitBtn.style.display = 'none';
      }

      const progress = (currentStep / totalSteps) * 100;
      progressBar.style.width = progress + '%';

      if (currentStep === totalSteps) {
        updateSummary();
      }
    }

    function validateStep(step) {
      let isValid = true;

      document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
      });

      if (step === 1) {
        const empresaNome = document.getElementById('empresaNome');
        const empresaFantasia = document.getElementById('empresaFantasia');
        const empresaCnpj = document.getElementById('empresaCnpj');
        const empresaTelefone = document.getElementById('empresaTelefone');
        const empresaEmail = document.getElementById('empresaEmail');
        const empresaEndereco = document.getElementById('empresaEndereco');
        const empresaCidade = document.getElementById('empresaCidade');
        const empresaEstado = document.getElementById('empresaEstado');

        if (!empresaNome.value.trim()) {
          empresaNome.classList.add('is-invalid');
          document.getElementById('empresaNomeError').textContent = 'Nome obrigatorio';
          isValid = false;
        }

        if (!empresaFantasia.value.trim()) {
          empresaFantasia.classList.add('is-invalid');
          document.getElementById('empresaFantasiaError').textContent = 'Nome fantasia obrigatorio';
          isValid = false;
        }

        if (!empresaCnpj.value.trim()) {
          empresaCnpj.classList.add('is-invalid');
          document.getElementById('empresaCnpjError').textContent = 'CNPJ obrigatorio';
          isValid = false;
        }

        if (!empresaTelefone.value.trim()) {
          empresaTelefone.classList.add('is-invalid');
          document.getElementById('empresaTelefoneError').textContent = 'Telefone obrigatorio';
          isValid = false;
        }

        if (!empresaEmail.value.trim()) {
          empresaEmail.classList.add('is-invalid');
          document.getElementById('empresaEmailError').textContent = 'Email obrigatorio';
          isValid = false;
        } else if (!isValidEmail(empresaEmail.value)) {
          empresaEmail.classList.add('is-invalid');
          document.getElementById('empresaEmailError').textContent = 'Email invalido';
          isValid = false;
        }

        if (!empresaEndereco.value.trim()) {
          empresaEndereco.classList.add('is-invalid');
          document.getElementById('empresaEnderecoError').textContent = 'Endereco obrigatorio';
          isValid = false;
        }

        if (!empresaCidade.value.trim()) {
          empresaCidade.classList.add('is-invalid');
          document.getElementById('empresaCidadeError').textContent = 'Cidade obrigatoria';
          isValid = false;
        }

        if (!empresaEstado.value.trim()) {
          empresaEstado.classList.add('is-invalid');
          document.getElementById('empresaEstadoError').textContent = 'Estado obrigatorio';
          isValid = false;
        }
      }

      if (step === 2) {
        const usuarioNome = document.getElementById('usuarioNome');
        const usuarioEmail = document.getElementById('usuarioEmail');
        const usuarioSenha = document.getElementById('usuarioSenha');
        const usuarioConfirmar = document.getElementById('usuarioConfirmar');

        if (!usuarioNome.value.trim()) {
          usuarioNome.classList.add('is-invalid');
          document.getElementById('usuarioNomeError').textContent = 'Nome obrigatorio';
          isValid = false;
        }

        if (!usuarioEmail.value.trim()) {
          usuarioEmail.classList.add('is-invalid');
          document.getElementById('usuarioEmailError').textContent = 'Email obrigatorio';
          isValid = false;
        } else if (!isValidEmail(usuarioEmail.value)) {
          usuarioEmail.classList.add('is-invalid');
          document.getElementById('usuarioEmailError').textContent = 'Email invalido';
          isValid = false;
        }

        if (!usuarioSenha.value) {
          usuarioSenha.classList.add('is-invalid');
          document.getElementById('usuarioSenhaError').textContent = 'Senha obrigatoria';
          isValid = false;
        } else if (usuarioSenha.value.length < 6) {
          usuarioSenha.classList.add('is-invalid');
          document.getElementById('usuarioSenhaError').textContent = 'Senha deve ter pelo menos 6 caracteres';
          isValid = false;
        }

        if (!usuarioConfirmar.value) {
          usuarioConfirmar.classList.add('is-invalid');
          document.getElementById('usuarioConfirmarError').textContent = 'Confirme a senha';
          isValid = false;
        } else if (usuarioSenha.value !== usuarioConfirmar.value) {
          usuarioConfirmar.classList.add('is-invalid');
          document.getElementById('usuarioConfirmarError').textContent = 'Senhas nao conferem';
          isValid = false;
        }
      }

      return isValid;
    }

    function isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function changeStep(direction) {
      if (direction > 0) {
        if (!validateStep(currentStep)) {
          return;
        }
      }

      currentStep += direction;

      if (currentStep < 1) currentStep = 1;
      if (currentStep > totalSteps) currentStep = totalSteps;

      updateDisplay();
    }

    function updateSummary() {
      const summary = document.getElementById('summary');
      summary.innerHTML = `
        <div class="summary-item">
          <span class="summary-label">Empresa:</span>
          <span class="summary-value">${document.getElementById('empresaNome').value || 'Nao informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Nome Fantasia:</span>
          <span class="summary-value">${document.getElementById('empresaFantasia').value || 'Nao informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">CNPJ:</span>
          <span class="summary-value">${document.getElementById('empresaCnpj').value || 'Nao informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Email Empresa:</span>
          <span class="summary-value">${document.getElementById('empresaEmail').value || 'Nao informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Telefone:</span>
          <span class="summary-value">${document.getElementById('empresaTelefone').value || 'Nao informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Cidade/Estado:</span>
          <span class="summary-value">${document.getElementById('empresaCidade').value || 'Nao informado'} - ${document.getElementById('empresaEstado').value || ''}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Usuario:</span>
          <span class="summary-value">${document.getElementById('usuarioNome').value || 'Nao informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Email Usuario:</span>
          <span class="summary-value">${document.getElementById('usuarioEmail').value || 'Nao informado'}</span>
        </div>
      `;
    }

    function submitForm() {
      if (validateStep(currentStep)) {
        form.submit();
      }
    }

    updateDisplay();
    if (startStep > 1) {
      currentStep = startStep;
      updateDisplay();
    }

    const telefoneInput = document.getElementById('empresaTelefone');
    if (telefoneInput) {
      telefoneInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);

        if (value.length > 6) {
          value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length > 2) {
          value = value.replace(/(\d{2})(\d+)/, '($1) $2');
        }

        e.target.value = value;
      });
    }
  </script>
</body>
</html>

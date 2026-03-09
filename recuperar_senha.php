<!DOCTYPE html>
<html lang="en">
<head>
  <!-- head.php simplificado para este exemplo -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar senha</title>
  <!-- Bootstrap + Tela icons (versao simplificada, sem vendor) -->
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
          <!-- Coluna esquerda: formulario refinado -->
          <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="auth-form-transparent text-left p-4">
              <div class="brand-logo mb-4">
                <img src="https://via.placeholder.com/150x45/667eea/ffffff?text=Your+Logo" alt="logo" style="background: #667eea; padding: 8px 15px; border-radius: 30px;">
                <!-- Substitua pelo seu logo real -->
              </div>
              <h4 class="mb-1 fw-semibold" style="color: #1e1e2f;">Recuperar senha</h4>
              <p class="text-secondary mb-4" style="font-size: 0.95rem;">Enviaremos um link de redefinicao para seu e-mail.</p>

              <form class="pt-2">
                <div class="form-group">
                  <label for="email" class="text-muted">E-mail</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                      <i class="ti-email text-primary" style="font-size: 1.1rem;"></i>
                    </span>
                    <input type="email" class="form-control border-start-0 ps-0" id="email" placeholder="nome@empresa.com" style="background: white;">
                  </div>
                </div>

                <div class="d-flex justify-content-between align-items-center my-3">
                  <span class="text-muted" style="font-size: 0.9rem;">O link expira em 30 min.</span>
                  <a href="login.php" class="auth-link">Voltar</a>
                </div>

                <button class="btn btn-primary w-100 mb-4 py-2" type="button">ENVIAR LINK</button>

                <div class="d-flex gap-2 mb-4">
                  <button class="btn btn-facebook auth-form-btn flex-grow-1 d-flex align-items-center justify-content-center gap-2" type="button">
                    <i class="ti-headphone"></i> Suporte
                  </button>
                  <button class="btn btn-google auth-form-btn flex-grow-1 d-flex align-items-center justify-content-center gap-2" type="button">
                    <i class="ti-book"></i> FAQ
                  </button>
                </div>

                <div class="text-center">
                  <span class="text-muted" style="font-size: 0.95rem;">Nao tem acesso?</span>
                  <a href="novo_acesso.php" class="text-primary ms-1 fw-semibold">Criar conta</a>
                </div>
              </form>
            </div>
          </div>

          <!-- Coluna direita: imagem profissional e elegante (sem poluicao) -->
          <div class="col-lg-6 login-half-bg d-flex flex-column align-items-center justify-content-center">
            <div class="right-image-content">
              <h2 class="display-6 fw-light text-white">Secure & modern</h2>
              <p class="lead text-white-50">Access your dashboard with confidence</p>
              <div style="margin-top: 2rem;">
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill me-2">#encrypted</span>
                <span class="badge bg-white text-dark px-3 py-2 rounded-pill">#cloud</span>
              </div>
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

  <!-- scripts mantidos apenas os essenciais (simulacao) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

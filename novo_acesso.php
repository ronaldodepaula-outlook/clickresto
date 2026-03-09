<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Novo acesso - Wizard funcional</title>
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
      max-width: 800px;
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
    
    textarea.form-control {
      padding: 0.4rem 0.8rem;
      font-size: 0.85rem;
      resize: none;
    }
    
    small.text-muted {
      font-size: 0.7rem;
      display: block;
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
          <!-- Coluna esquerda: formulário com wizard funcional -->
          <div class="col-lg-6">
            <div class="form-container">
              <div class="auth-form-transparent text-left p-3">
                <div class="brand-logo">
                  <img src="https://via.placeholder.com/130x38/667eea/ffffff?text=Your+Logo" alt="logo">
                </div>
                
                <h4 class="fw-semibold" style="color: #1e1e2f;">Criar novo acesso</h4>
                <p class="text-secondary mb-2">Preencha os dados para iniciar sua conta.</p>

                <!-- Barra de progresso -->
                <div class="progress">
                  <div class="progress-bar bg-primary" id="progressBar" role="progressbar" style="width: 25%"></div>
                </div>

                <!-- Wizard Steps -->
                <div class="wizard-steps" id="wizardSteps">
                  <div class="wizard-step active" data-step="1">
                    <span class="step-index">1</span> Account
                  </div>
                  <div class="wizard-step" data-step="2">
                    <span class="step-index">2</span> Profile
                  </div>
                  <div class="wizard-step" data-step="3">
                    <span class="step-index">3</span> Comments
                  </div>
                  <div class="wizard-step" data-step="4">
                    <span class="step-index">4</span> Finish
                  </div>
                </div>

                <form id="wizardForm">
                  <!-- Wizard Content -->
                  <div class="wizard-content">
                    <!-- Step 1: Account -->
                    <div class="wizard-pane active" id="step1">
                      <div class="wizard-section">
                        <h6>Account Information</h6>
                        <div class="form-group">
                          <label>Username *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-user text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-1" id="username" placeholder="Seu usuario" required>
                          </div>
                          <div class="invalid-feedback" id="usernameError"></div>
                        </div>
                        
                        <div class="form-group">
                          <label>Email address *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-email text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="email" class="form-control border-start-0 ps-1" id="email" placeholder="nome@empresa.com" required>
                          </div>
                          <small class="text-muted">Não compartilhamos seu email</small>
                          <div class="invalid-feedback" id="emailError"></div>
                        </div>
                        
                        <div class="form-group">
                          <label>Password *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-lock text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 ps-1" id="password" placeholder="Crie uma senha" required>
                          </div>
                          <div class="invalid-feedback" id="passwordError"></div>
                        </div>
                        
                        <div class="form-group mb-0">
                          <label>Confirm Password *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-lock text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 ps-1" id="confirmPassword" placeholder="Repita a senha" required>
                          </div>
                          <div class="invalid-feedback" id="confirmPasswordError"></div>
                        </div>
                      </div>
                    </div>

                    <!-- Step 2: Profile -->
                    <div class="wizard-pane" id="step2">
                      <div class="wizard-section">
                        <h6>Profile Information</h6>
                        <div class="form-group">
                          <label>Nome completo *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-user text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-1" id="fullName" placeholder="Seu nome completo" required>
                          </div>
                        </div>
                        
                        <div class="form-group">
                          <label>Empresa</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-briefcase text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-1" id="company" placeholder="Nome da empresa">
                          </div>
                        </div>
                        
                        <div class="form-group mb-0">
                          <label>Telefone *</label>
                          <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 p-2">
                              <i class="ti-mobile text-primary" style="font-size: 1rem;"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-1" id="phone" placeholder="(00) 00000-0000" required>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Step 3: Comments -->
                    <div class="wizard-pane" id="step3">
                      <div class="wizard-section">
                        <h6>Additional Information</h6>
                        <div class="form-group mb-0">
                          <label>Comments</label>
                          <textarea class="form-control" id="comments" rows="3" placeholder="Conte um pouco sobre o seu uso previsto."></textarea>
                        </div>
                      </div>
                    </div>

                    <!-- Step 4: Finish -->
                    <div class="wizard-pane" id="step4">
                      <div class="wizard-section">
                        <h6>Review Your Information</h6>
                        <div id="summary"></div>
                      </div>
                    </div>
                  </div>

                  <!-- Navigation Buttons -->
                  <div class="wizard-actions">
                    <button type="button" class="btn btn-outline-primary px-3" id="prevBtn" onclick="changeStep(-1)" disabled>Anterior</button>
                    <button type="button" class="btn btn-primary px-4" id="nextBtn" onclick="changeStep(1)">PRÓXIMA ETAPA</button>
                    <button type="button" class="btn btn-success px-4" id="submitBtn" style="display: none;" onclick="submitForm()">FINALIZAR</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Coluna direita: mantida igual -->
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
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let currentStep = 1;
    const totalSteps = 4;

    // Elementos do DOM
    const steps = document.querySelectorAll('.wizard-step');
    const panes = document.querySelectorAll('.wizard-pane');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('progressBar');

    function updateDisplay() {
      // Atualiza steps
      steps.forEach((step, index) => {
        const stepNum = index + 1;
        step.classList.remove('active', 'completed');
        
        if (stepNum === currentStep) {
          step.classList.add('active');
        } else if (stepNum < currentStep) {
          step.classList.add('completed');
        }
      });

      // Atualiza panes
      panes.forEach((pane, index) => {
        if (index + 1 === currentStep) {
          pane.classList.add('active');
        } else {
          pane.classList.remove('active');
        }
      });

      // Atualiza botões
      prevBtn.disabled = currentStep === 1;
      
      if (currentStep === totalSteps) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'block';
      } else {
        nextBtn.style.display = 'block';
        submitBtn.style.display = 'none';
      }

      // Atualiza barra de progresso
      const progress = (currentStep / totalSteps) * 100;
      progressBar.style.width = progress + '%';

      // Se for última etapa, atualiza resumo
      if (currentStep === totalSteps) {
        updateSummary();
      }
    }

    function validateStep(step) {
      let isValid = true;
      
      // Remove erros anteriores
      document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
      });

      if (step === 1) {
        // Valida step 1
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');

        if (!username.value.trim()) {
          username.classList.add('is-invalid');
          document.getElementById('usernameError').textContent = 'Username é obrigatório';
          isValid = false;
        }

        if (!email.value.trim()) {
          email.classList.add('is-invalid');
          document.getElementById('emailError').textContent = 'Email é obrigatório';
          isValid = false;
        } else if (!isValidEmail(email.value)) {
          email.classList.add('is-invalid');
          document.getElementById('emailError').textContent = 'Email inválido';
          isValid = false;
        }

        if (!password.value) {
          password.classList.add('is-invalid');
          document.getElementById('passwordError').textContent = 'Senha é obrigatória';
          isValid = false;
        } else if (password.value.length < 6) {
          password.classList.add('is-invalid');
          document.getElementById('passwordError').textContent = 'Senha deve ter pelo menos 6 caracteres';
          isValid = false;
        }

        if (!confirmPassword.value) {
          confirmPassword.classList.add('is-invalid');
          document.getElementById('confirmPasswordError').textContent = 'Confirme sua senha';
          isValid = false;
        } else if (password.value !== confirmPassword.value) {
          confirmPassword.classList.add('is-invalid');
          document.getElementById('confirmPasswordError').textContent = 'Senhas não conferem';
          isValid = false;
        }
      }
      
      if (step === 2) {
        // Valida step 2
        const fullName = document.getElementById('fullName');
        const phone = document.getElementById('phone');

        if (!fullName.value.trim()) {
          fullName.classList.add('is-invalid');
          isValid = false;
        }

        if (!phone.value.trim()) {
          phone.classList.add('is-invalid');
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
        // Próxima etapa - valida antes
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
          <span class="summary-label">Username:</span>
          <span class="summary-value">${document.getElementById('username').value || 'Não informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Email:</span>
          <span class="summary-value">${document.getElementById('email').value || 'Não informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Nome completo:</span>
          <span class="summary-value">${document.getElementById('fullName').value || 'Não informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Empresa:</span>
          <span class="summary-value">${document.getElementById('company').value || 'Não informada'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Telefone:</span>
          <span class="summary-value">${document.getElementById('phone').value || 'Não informado'}</span>
        </div>
        <div class="summary-item">
          <span class="summary-label">Comments:</span>
          <span class="summary-value">${document.getElementById('comments').value || 'Sem comentários'}</span>
        </div>
      `;
    }

    function submitForm() {
      // Valida último passo se necessário
      if (validateStep(currentStep)) {
        // Aqui você pode enviar os dados para o servidor
        const formData = {
          username: document.getElementById('username').value,
          email: document.getElementById('email').value,
          password: document.getElementById('password').value,
          fullName: document.getElementById('fullName').value,
          company: document.getElementById('company').value,
          phone: document.getElementById('phone').value,
          comments: document.getElementById('comments').value
        };
        
        console.log('Dados do formulário:', formData);
        
        // Simula envio bem-sucedido
        alert('Cadastro realizado com sucesso!');
        
        // Redireciona para login (opcional)
        // window.location.href = 'login.php';
      }
    }

    // Inicializa
    updateDisplay();

    // Adiciona máscara de telefone simples
    document.getElementById('phone').addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 11) value = value.slice(0, 11);
      
      if (value.length > 6) {
        value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
      } else if (value.length > 2) {
        value = value.replace(/(\d{2})(\d+)/, '($1) $2');
      }
      
      e.target.value = value;
    });
  </script>
</body>
</html>
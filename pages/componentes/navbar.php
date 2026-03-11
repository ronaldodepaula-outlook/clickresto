<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$userName = $_SESSION['user_name'] ?? 'Usuario';
$userEmail = $_SESSION['user_email'] ?? '';
$userNameSafe = htmlspecialchars($userName, ENT_QUOTES, 'UTF-8');
$userEmailSafe = htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8');
$licencaStatus = $_SESSION['licenca_status'] ?? '';
$licencaMensagem = $_SESSION['licenca_mensagem'] ?? '';
$licencaDiasRestantes = $_SESSION['licenca_dias_restantes'] ?? null;
$licencaDiasExpirados = $_SESSION['licenca_dias_expirados'] ?? null;
$licencaDuracaoDias = $_SESSION['licenca_duracao_dias'] ?? null;
$licencaDuracaoMeses = $_SESSION['licenca_duracao_meses'] ?? null;
$licencaDuracaoAnos = $_SESSION['licenca_duracao_anos'] ?? null;
$licencaStatusNormalized = strtolower(trim((string)$licencaStatus));
$licencaMensagemSafe = $licencaMensagem !== '' ? htmlspecialchars($licencaMensagem, ENT_QUOTES, 'UTF-8') : '';
$licencaDiasRestantesSafe = is_numeric($licencaDiasRestantes) ? (string)(int)$licencaDiasRestantes : '';
$licencaStatusLabel = '';
if ($licencaStatusNormalized === 'expirada') {
  $licencaStatusLabel = 'Licenca expirada';
} elseif ($licencaStatusNormalized === 'ok') {
  $licencaStatusLabel = 'Licenca ok';
} elseif ($licencaStatus !== '') {
  $licencaStatusLabel = 'Licenca ' . (string)$licencaStatus;
}
$licencaStatusLabelSafe = $licencaStatusLabel !== '' ? htmlspecialchars($licencaStatusLabel, ENT_QUOTES, 'UTF-8') : '';
$licencaBadgeClass = ($licencaStatusNormalized === 'expirada') ? 'badge-danger' : 'badge-success';
$licencaIcon = ($licencaStatusNormalized === 'expirada') ? 'mdi-alert-circle-outline' : 'mdi-check-circle-outline';
$showLicenca = ($licencaStatusLabelSafe !== '' || $licencaMensagemSafe !== '' || $licencaDiasRestantesSafe !== '');
$hour = (int)date('H');
if ($hour < 12) {
  $greeting = 'Bom dia';
} elseif ($hour < 18) {
  $greeting = 'Boa tarde';
} else {
  $greeting = 'Boa noite';
}
?>
<style>
  body.sidebar-icon-only .navbar .navbar-brand-wrapper {
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
  }
  body.sidebar-icon-only .navbar .navbar-brand-wrapper .me-3 {
    order: 2;
    margin: 0 !important;
  }
  body.sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo-mini {
    order: 1;
    margin: 0 0 0.35rem 0;
  }
  body.sidebar-icon-only .navbar .navbar-brand-wrapper .brand-logo {
    display: none;
  }
  .navbar .nav-link.count-indicator {
    position: relative;
  }
  .navbar .nav-link .licenca-count {
    position: absolute;
    top: -4px;
    right: -6px;
    font-size: 0.65rem;
    line-height: 1;
    padding: 0.2rem 0.35rem;
    min-width: 1.2rem;
    text-align: center;
  }
</style>
<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
          <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
              <span class="icon-menu"></span>
            </button>
          </div>
          <div>
            <a class="navbar-brand brand-logo" href="?paginas=home">
              Click<span style="color: #0b1f5e; font-weight: 700;">Resto</span> 
            </a>
            <a class="navbar-brand brand-logo-mini" href="?paginas=home">
              <small>C<span style="color: #0b1f5e; font-weight: 700;">R</span></small>
            </a>
          </div>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-top">
          <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
              <h1 class="welcome-text"><?php echo $greeting; ?>, <span class="text-black fw-bold"><?php echo $userNameSafe; ?></span></h1>
              <h3 class="welcome-sub-text">Nosso resumo de desempenho desta semana </h3>
            </li>
          </ul>
          <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown d-none d-lg-block">
              <a class="nav-link dropdown-bordered dropdown-toggle dropdown-toggle-split" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false"> Select Category </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="messageDropdown">
                <a class="dropdown-item py-3">
                  <p class="mb-0 fw-medium float-start">Select category</p>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Bootstrap Bundle </p>
                    <p class="fw-light small-text mb-0">This is a Bundle featuring 16 unique dashboards</p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Angular Bundle</p>
                    <p class="fw-light small-text mb-0">Everything you’ll ever need for your Angular projects</p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">VUE Bundle</p>
                    <p class="fw-light small-text mb-0">Bundle of 6 Premium Vue Admin Dashboard</p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">React Bundle</p>
                    <p class="fw-light small-text mb-0">Bundle of 8 Premium React Admin Dashboard</p>
                  </div>
                </a>
              </div>
            </li>
            <li class="nav-item d-none d-lg-block">
              <div id="datepicker-popup" class="input-group date datepicker navbar-date-picker">
                <span class="input-group-addon input-group-prepend border-right">
                  <span class="icon-calendar input-group-text calendar-icon"></span>
                </span>
                <input type="text" class="form-control">
              </div>
            </li>
            <li class="nav-item">
              <form class="search-form" action="#">
                <i class="icon-search"></i>
                <input type="search" class="form-control" placeholder="Search Here" title="Search here">
              </form>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                <i class="icon-bell"></i>
                <?php if ($licencaDiasRestantesSafe !== ''): ?>
                  <span class="badge badge-pill badge-warning licenca-count"><?php echo $licencaDiasRestantesSafe; ?></span>
                <?php else: ?>
                  <span class="count"></span>
                <?php endif; ?>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="notificationDropdown">
                <a class="dropdown-item py-3 border-bottom">
                  <p class="mb-0 fw-medium float-start">Status da licenca</p>
                  <?php if ($licencaDiasRestantesSafe !== ''): ?>
                    <span class="badge badge-pill <?php echo $licencaBadgeClass; ?> float-end"><?php echo $licencaDiasRestantesSafe; ?> dias</span>
                  <?php endif; ?>
                </a>
                <?php if ($showLicenca): ?>
                  <a class="dropdown-item preview-item py-3">
                    <div class="preview-thumbnail">
                      <i class="mdi <?php echo $licencaIcon; ?> m-auto text-<?php echo $licencaStatusNormalized === 'expirada' ? 'danger' : 'success'; ?>"></i>
                    </div>
                    <div class="preview-item-content">
                      <?php if ($licencaStatusLabelSafe !== ''): ?>
                        <h6 class="preview-subject fw-normal text-dark mb-1"><?php echo $licencaStatusLabelSafe; ?></h6>
                      <?php endif; ?>
                      <?php if ($licencaMensagemSafe !== ''): ?>
                        <p class="fw-light small-text mb-0"><?php echo $licencaMensagemSafe; ?></p>
                      <?php endif; ?>
                      <?php if ($licencaDiasRestantesSafe !== ''): ?>
                        <p class="fw-light small-text mb-0">Dias restantes: <?php echo $licencaDiasRestantesSafe; ?></p>
                      <?php endif; ?>
                      <?php if (is_numeric($licencaDiasExpirados) && (int)$licencaDiasExpirados > 0): ?>
                        <p class="fw-light small-text mb-0">Dias expirados: <?php echo (int)$licencaDiasExpirados; ?></p>
                      <?php endif; ?>
                      <?php if (is_numeric($licencaDuracaoDias) && (int)$licencaDuracaoDias > 0): ?>
                        <p class="fw-light small-text mb-0">Duracao: <?php echo (int)$licencaDuracaoDias; ?> dias</p>
                      <?php elseif (is_numeric($licencaDuracaoMeses) && (int)$licencaDuracaoMeses > 0): ?>
                        <p class="fw-light small-text mb-0">Duracao: <?php echo (int)$licencaDuracaoMeses; ?> meses</p>
                      <?php elseif (is_numeric($licencaDuracaoAnos) && (int)$licencaDuracaoAnos > 0): ?>
                        <p class="fw-light small-text mb-0">Duracao: <?php echo (int)$licencaDuracaoAnos; ?> anos</p>
                      <?php endif; ?>
                    </div>
                  </a>
                <?php else: ?>
                  <a class="dropdown-item preview-item py-3">
                    <div class="preview-thumbnail">
                      <i class="mdi mdi-information-outline m-auto text-muted"></i>
                    </div>
                    <div class="preview-item-content">
                      <h6 class="preview-subject fw-normal text-dark mb-1">Licenca</h6>
                      <p class="fw-light small-text mb-0">Sem informacoes de licenca</p>
                    </div>
                  </a>
                <?php endif; ?>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator" id="countDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="icon-mail icon-lg"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list pb-0" aria-labelledby="countDropdown">
                <a class="dropdown-item py-3">
                  <p class="mb-0 fw-medium float-start">You have 7 unread mails </p>
                  <span class="badge badge-pill badge-primary float-end">View all</span>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face10.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Marian Garner </p>
                    <p class="fw-light small-text mb-0"> The meeting is cancelled </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face12.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">David Grey </p>
                    <p class="fw-light small-text mb-0"> The meeting is cancelled </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="assets/images/faces/face1.jpg" alt="image" class="img-sm profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow py-2">
                    <p class="preview-subject ellipsis fw-medium text-dark">Travis Jenkins </p>
                    <p class="fw-light small-text mb-0"> The meeting is cancelled </p>
                  </div>
                </a>
              </div>
            </li>
            <li class="nav-item dropdown d-none d-lg-block user-dropdown">
              <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <img class="img-xs rounded-circle" src="assets/images/faces/face8.jpg" alt="Profile image"> </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                <div class="dropdown-header text-center">
                  <img class="img-md rounded-circle" src="assets/images/faces/face8.jpg" alt="Profile image">
                  <p class="mb-1 mt-3 fw-semibold"><?php echo $userNameSafe; ?></p>
                  <p class="fw-light text-muted mb-0"><?php echo $userEmailSafe; ?></p>
                </div>
                <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> My Profile <span class="badge badge-pill badge-danger">1</span></a>
                <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-message-text-outline text-primary me-2"></i> Messages</a>
                <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-calendar-check-outline text-primary me-2"></i> Activity</a>
                <a class="dropdown-item"><i class="dropdown-item-icon mdi mdi-help-circle-outline text-primary me-2"></i> FAQ</a>
                <a class="dropdown-item" href="logout.php"><i class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Sign Out</a>
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>
      <script>
        document.addEventListener('DOMContentLoaded', function () {
          document.body.classList.add('sidebar-icon-only');
        });
      </script>

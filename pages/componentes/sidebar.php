<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$role = strtolower((string)($_SESSION['user_role'] ?? ''));
$role = str_replace(['-', ' '], '_', $role);
if (strpos($role, 'cozinha') !== false) {
  $role = 'cozinha';
} elseif (strpos($role, 'master') !== false) {
  $role = 'admin_master';
} elseif (strpos($role, 'admin') !== false) {
  $role = 'admin';
} else {
  $role = 'admin';
}


$nameLower = strtolower((string)($_SESSION['user_name'] ?? ''));
$emailLower = strtolower((string)($_SESSION['user_email'] ?? ''));
if ($role !== 'admin_master' && $role !== 'cozinha' && (strpos($nameLower, 'master') !== false || $emailLower === 'admin@clickresto.com')) {
  $role = 'admin_master';
}
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <?php if ($role === 'cozinha'): ?>
              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=gertao_cozinha">
                  <i class="mdi mdi-view-dashboard menu-icon"></i>
                  <span class="menu-title">Gestao Cozinha</span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=painel_atendimento_cozinha">
                  <i class="mdi mdi-silverware-fork-knife menu-icon"></i>
                  <span class="menu-title">Painel Atendimento</span>
                </a>
              </li>
            <?php elseif ($role === 'admin'): ?>
              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=HomeResto">
                  <i class="mdi mdi-silverware-fork-knife menu-icon"></i>
                  <span class="menu-title">Dashboard Resto</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#operacao-menu" aria-expanded="false" aria-controls="operacao-menu">
                  <i class="menu-icon mdi mdi-fire"></i>
                  <span class="menu-title">Operacao</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="operacao-menu">
                  <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=pedidos">Pedidos</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=comandas">Comandas</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=mesas">Mesas</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=cozinha">Cozinha</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=delivery">Delivery</a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#catalogo-menu" aria-expanded="false" aria-controls="catalogo-menu">
                  <i class="menu-icon mdi mdi-food"></i>
                  <span class="menu-title">Catalogo</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="catalogo-menu">
                  <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=categorias">Categorias</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=produtos">Produtos</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=produto_imagens">Imagens</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=produto_opcoes">Opcoes</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=produto_opcao_itens">Itens de opcoes</a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=estoque">
                  <i class="menu-icon mdi mdi-warehouse"></i>
                  <span class="menu-title">Estoque</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#financeiro-menu" aria-expanded="false" aria-controls="financeiro-menu">
                  <i class="menu-icon mdi mdi-cash-multiple"></i>
                  <span class="menu-title">Financeiro</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="financeiro-menu">
                  <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=caixa">Caixa</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=pagamentos">Pagamentos</a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=clientes">
                  <i class="menu-icon mdi mdi-account-group-outline"></i>
                  <span class="menu-title">Clientes</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=usuarios_empresa_admin">
                  <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                  <span class="menu-title">Usuarios</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=relatorios_resto">
                  <i class="menu-icon mdi mdi-chart-areaspline"></i>
                  <span class="menu-title">Relatorios</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=configuracoes_resto">
                  <i class="menu-icon mdi mdi-cog-outline"></i>
                  <span class="menu-title">Configuracoes</span>
                </a>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=home">
                  <i class="mdi mdi-view-dashboard menu-icon"></i>
                  <span class="menu-title">Dashboard SaaS</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#empresas-menu" aria-expanded="false" aria-controls="empresas-menu">
                  <i class="menu-icon mdi mdi-office-building"></i>
                  <span class="menu-title">Empresas</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="empresas-menu">
                  <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=empresas">Listar empresas</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=empresa_form">Criar empresa</a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#licencas-menu" aria-expanded="false" aria-controls="licencas-menu">
                  <i class="menu-icon mdi mdi-license"></i>
                  <span class="menu-title">Licencas</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="licencas-menu">
                  <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=planos">Planos</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=licencas">Assinaturas</a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=usuarios">
                  <i class="menu-icon mdi mdi-account-multiple-outline"></i>
                  <span class="menu-title">Usuarios</span>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#relatorios-menu" aria-expanded="false" aria-controls="relatorios-menu">
                  <i class="menu-icon mdi mdi-chart-areaspline"></i>
                  <span class="menu-title">Relatorios</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="relatorios-menu">
                  <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=relatorios">Receita</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=relatorios">Empresas</a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#config-menu" aria-expanded="false" aria-controls="config-menu">
                  <i class="menu-icon mdi mdi-cog-outline"></i>
                  <span class="menu-title">Configuracoes</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="config-menu">
                  <ul class="nav flex-column sub-menu">
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=configuracoes">Sistema</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?paginas=permissoes">Permissoes</a></li>
                  </ul>
                </div>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="index.php?paginas=monitoramento">
                  <i class="menu-icon mdi mdi-radar"></i>
                  <span class="menu-title">Monitoramento</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>

<?php
$rowsSerie = is_array($dashboard['serie_diaria'] ?? null) ? $dashboard['serie_diaria'] : [];
$rowsSerieForma = is_array($dashboard['serie_diaria_por_forma'] ?? null) ? $dashboard['serie_diaria_por_forma'] : [];
$rowsFormas = is_array($dashboard['totais_por_forma_pagamento'] ?? null) ? $dashboard['totais_por_forma_pagamento'] : [];
$rowsR16 = is_array($r16Rows ?? null) ? $r16Rows : [];
$rowsR18 = is_array($r18Rows ?? null) ? $r18Rows : [];
$hasUserIndicatorData = !empty($rowsR16) || !empty($r17Rows) || !empty($rowsR18);

$formatDateLabel = static function ($value) {
    $timestamp = strtotime((string)$value);
    return $timestamp === false ? (string)$value : date('d/m/Y', $timestamp);
};

$periodoOptions = [
    'dia' => 'Dia',
    'semana' => 'Semana',
    'mes' => 'Mes',
    'ano' => 'Ano',
    'periodo' => 'Periodo',
];

$chartSerieLabels = [];
foreach ($serieLabels as $label) {
    $chartSerieLabels[] = $formatDateLabel($label);
}
?>
<div class="main-panel">
  <div class="content-wrapper homeresto-clean">
    <style>
      .homeresto-clean {
        --hr-bg: #f4f7fb;
        --hr-surface: rgba(255, 255, 255, 0.94);
        --hr-border: rgba(15, 23, 42, 0.08);
        --hr-text: #172033;
        --hr-muted: #64748b;
        --hr-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
        --hr-radius: 22px;
        background:
          radial-gradient(circle at top left, rgba(8, 145, 178, 0.08), transparent 28%),
          radial-gradient(circle at top right, rgba(245, 158, 11, 0.08), transparent 24%),
          linear-gradient(180deg, #f8fbff 0%, #f4f7fb 100%);
      }

      .homeresto-shell {
        color: var(--hr-text);
      }

      .hr-hero,
      .hr-panel,
      .hr-metric-card,
      .hr-chart-card,
      .hr-table-card {
        border: 0;
        border-radius: var(--hr-radius);
        background: var(--hr-surface);
        box-shadow: var(--hr-shadow);
      }

      .hr-hero {
        overflow: hidden;
        background:
          linear-gradient(135deg, rgba(12, 74, 110, 0.96), rgba(15, 23, 42, 0.98)),
          linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.01));
      }

      .hr-hero .card-body {
        padding: 1.6rem 1.7rem;
        color: #f8fafc;
      }

      .hr-hero-head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
      }

      .hr-kicker {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.14);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
      }

      .hr-hero h2 {
        margin: 0.8rem 0 0.45rem;
        font-size: 1.9rem;
        font-weight: 700;
        letter-spacing: -0.03em;
      }

      .hr-hero p {
        margin-bottom: 0;
        max-width: 700px;
        color: rgba(226, 232, 240, 0.86);
      }

      .hr-pill-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        align-items: center;
      }

      .hr-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.55rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #f8fafc;
        font-size: 0.82rem;
        text-decoration: none;
      }

      .hr-pill:hover {
        color: #f8fafc;
        background: rgba(255, 255, 255, 0.14);
        text-decoration: none;
      }

      .hr-panel .card-body,
      .hr-chart-card .card-body,
      .hr-table-card .card-body,
      .hr-metric-card .card-body {
        padding: 1.3rem;
      }

      .hr-section-head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.8rem;
        align-items: flex-end;
        margin-bottom: 1rem;
      }

      .hr-section-head h4,
      .hr-card-title {
        margin-bottom: 0.2rem;
        font-size: 1.02rem;
        font-weight: 700;
      }

      .hr-section-head p,
      .hr-card-subtitle,
      .hr-empty,
      .hr-helper {
        margin-bottom: 0;
        color: var(--hr-muted);
      }

      .hr-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        background: #eef4ff;
        color: #18457b;
        font-size: 0.78rem;
        font-weight: 700;
      }

      .hr-filter-grid .form-label {
        margin-bottom: 0.35rem;
        color: #475569;
        font-size: 0.83rem;
        font-weight: 600;
      }

      .hr-filter-grid .form-control,
      .hr-filter-grid .form-select {
        min-height: 44px;
        border-radius: 14px;
        border-color: rgba(148, 163, 184, 0.28);
        box-shadow: none;
      }

      .hr-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: center;
        margin-top: 1rem;
      }

      .hr-filter-actions .btn {
        border-radius: 999px;
        padding-inline: 1rem;
      }

      .hr-metric-card {
        position: relative;
        overflow: hidden;
        min-height: 100%;
      }

      .hr-metric-card::before {
        content: "";
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 4px;
        background: var(--tone-color, #0f766e);
      }

      .hr-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.95rem;
        border-radius: 15px;
        background: var(--tone-soft, rgba(15, 118, 110, 0.12));
        color: var(--tone-color, #0f766e);
        font-size: 1.3rem;
      }

      .hr-metric-label {
        margin-bottom: 0.35rem;
        color: #64748b;
        font-size: 0.86rem;
        font-weight: 600;
      }

      .hr-metric-value {
        margin-bottom: 0.15rem;
        font-size: 1.65rem;
        font-weight: 700;
        letter-spacing: -0.03em;
        line-height: 1.08;
      }

      .hr-metric-meta {
        margin-bottom: 0;
        color: #475569;
        font-size: 0.84rem;
      }

      .tone-sky { --tone-color: #0f6cbd; --tone-soft: rgba(15, 108, 189, 0.12); }
      .tone-teal { --tone-color: #0f766e; --tone-soft: rgba(15, 118, 110, 0.12); }
      .tone-amber { --tone-color: #c27b0a; --tone-soft: rgba(194, 123, 10, 0.14); }
      .tone-mint { --tone-color: #047857; --tone-soft: rgba(4, 120, 87, 0.12); }
      .tone-coral { --tone-color: #d9485f; --tone-soft: rgba(217, 72, 95, 0.12); }
      .tone-rose { --tone-color: #be123c; --tone-soft: rgba(190, 18, 60, 0.1); }
      .tone-indigo { --tone-color: #4338ca; --tone-soft: rgba(67, 56, 202, 0.1); }
      .tone-slate { --tone-color: #334155; --tone-soft: rgba(51, 65, 85, 0.12); }
      .tone-navy { --tone-color: #1d4ed8; --tone-soft: rgba(29, 78, 216, 0.1); }
      .tone-ocean { --tone-color: #0891b2; --tone-soft: rgba(8, 145, 178, 0.12); }
      .tone-moss { --tone-color: #4d7c0f; --tone-soft: rgba(77, 124, 15, 0.12); }
      .tone-sand { --tone-color: #b45309; --tone-soft: rgba(180, 83, 9, 0.12); }
      .tone-violet { --tone-color: #7c3aed; --tone-soft: rgba(124, 58, 237, 0.1); }

      .hr-chart-head {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 0.8rem;
        align-items: flex-start;
        margin-bottom: 1rem;
      }

      .hr-chart-wrap {
        position: relative;
        min-height: 310px;
      }

      .hr-chart-wrap.hr-chart-wrap-sm {
        min-height: 290px;
      }

      .hr-chart-note {
        min-height: 1.2rem;
        margin-bottom: 0.75rem;
        color: var(--hr-muted);
        font-size: 0.82rem;
      }

      .hr-data-table {
        width: 100%;
        margin-bottom: 0;
      }

      .hr-data-table thead th {
        border-bottom-width: 1px;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
      }

      .hr-data-table tbody td {
        color: #0f172a;
        font-size: 0.86rem;
        vertical-align: middle;
      }

      .hr-data-table tbody tr:last-child td {
        border-bottom: 0;
      }

      .hr-empty {
        padding: 1rem 0;
        text-align: center;
      }

      .alert.hr-alert {
        border: 0;
        border-radius: 18px;
        box-shadow: var(--hr-shadow);
      }

      .hr-badge-soft {
        display: inline-flex;
        align-items: center;
        padding: 0.38rem 0.68rem;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: 0.76rem;
        font-weight: 700;
      }

      .hr-numeric {
        font-variant-numeric: tabular-nums;
      }

      @media (max-width: 991px) {
        .hr-hero h2 {
          font-size: 1.6rem;
        }

        .hr-hero .card-body,
        .hr-panel .card-body,
        .hr-chart-card .card-body,
        .hr-table-card .card-body,
        .hr-metric-card .card-body {
          padding: 1.1rem;
        }

        .hr-chart-wrap,
        .hr-chart-wrap.hr-chart-wrap-sm {
          min-height: 260px;
        }
      }
    </style>

    <div class="homeresto-shell">
      <div class="card hr-hero mb-4">
        <div class="card-body">
          <div class="hr-hero-head">
            <div>
              <span class="hr-kicker">Dashboard Operacional</span>
              <h2>Pagamentos, pedidos e operacao em uma leitura unica</h2>
              <p>Visao limpa do restaurante com foco no endpoint de pagamentos, filtros por periodo e os cards operacionais mantidos na mesma tela.</p>
            </div>
            <div class="hr-pill-list">
              <span class="hr-pill"><i class="mdi mdi-calendar-range"></i> <?php echo $esc($periodLabel); ?></span>
              <span class="hr-pill"><i class="mdi mdi-finance"></i> Total apurado: <?php echo $esc(homeresto_format_money($dashboard['total_apurado'])); ?></span>
            </div>
          </div>
          <div class="hr-pill-list mt-3">
            <?php foreach ($quickLinks as $link): ?>
              <a href="<?php echo $esc($link['url']); ?>" class="hr-pill">
                <i class="mdi mdi-arrow-top-right"></i>
                <?php echo $esc($link['label']); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <?php foreach ($errors as $message): ?>
        <div class="alert alert-danger hr-alert mb-3" role="alert">
          <strong>Erro:</strong> <?php echo $esc($message); ?>
        </div>
      <?php endforeach; ?>

      <?php foreach ($warnings as $message): ?>
        <div class="alert alert-warning hr-alert mb-3" role="alert">
          <strong>Aviso:</strong> <?php echo $esc($message); ?>
        </div>
      <?php endforeach; ?>

      <div class="card hr-panel mb-4">
        <div class="card-body">
          <div class="hr-section-head">
            <div>
              <h4>Filtros do dashboard</h4>
              <p>Selecione a janela de analise. Para semana e periodo a API trabalha com intervalo de datas.</p>
            </div>
            <span class="hr-chip"><i class="mdi mdi-tune-vertical"></i> Filtro ativo: <?php echo $esc($periodoOptions[$periodo] ?? ucfirst($periodo)); ?></span>
          </div>

          <form method="GET" action="">
            <input type="hidden" name="paginas" value="HomeResto">
            <div class="row g-3 hr-filter-grid">
              <div class="col-md-3">
                <label class="form-label" for="homeRestoPeriodo">Periodo</label>
                <select class="form-select" id="homeRestoPeriodo" name="periodo">
                  <?php foreach ($periodoOptions as $value => $label): ?>
                    <option value="<?php echo $esc($value); ?>" <?php echo $periodo === $value ? 'selected' : ''; ?>>
                      <?php echo $esc($label); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3" id="fieldData">
                <label class="form-label" for="homeRestoData">Data</label>
                <input class="form-control" type="date" id="homeRestoData" name="data" value="<?php echo $esc($data); ?>">
              </div>

              <div class="col-md-3" id="fieldMes">
                <label class="form-label" for="homeRestoMes">Mes</label>
                <input class="form-control" type="month" id="homeRestoMes" name="mes" value="<?php echo $esc($mes); ?>">
              </div>

              <div class="col-md-3" id="fieldAno">
                <label class="form-label" for="homeRestoAno">Ano</label>
                <input class="form-control" type="number" min="2000" max="2100" step="1" id="homeRestoAno" name="ano" value="<?php echo $esc($ano); ?>">
              </div>

              <div class="col-md-3" id="fieldInicio">
                <label class="form-label" for="homeRestoInicio">Data inicio</label>
                <input class="form-control" type="date" id="homeRestoInicio" name="data_inicio" value="<?php echo $esc($dataInicio); ?>">
              </div>

              <div class="col-md-3" id="fieldFim">
                <label class="form-label" for="homeRestoFim">Data fim</label>
                <input class="form-control" type="date" id="homeRestoFim" name="data_fim" value="<?php echo $esc($dataFim); ?>">
              </div>
            </div>

            <div class="hr-filter-actions">
              <button class="btn btn-primary" type="submit">
                <i class="mdi mdi-magnify"></i> Atualizar dashboard
              </button>
              <a class="btn btn-outline-secondary" href="index.php?paginas=HomeResto">
                <i class="mdi mdi-refresh"></i> Limpar filtros
              </a>
              <span class="hr-helper" id="homeRestoFilterHelper">Dia usa a data selecionada.</span>
            </div>
          </form>
        </div>
      </div>

      <div class="hr-section-head">
        <div>
          <h4>Cards operacionais</h4>
          <p>O bloco original foi preservado e recebeu um layout mais leve e consistente.</p>
        </div>
        <span class="hr-chip"><i class="mdi mdi-storefront-outline"></i> Restaurantes, pizzarias e churrascarias no mesmo fluxo</span>
      </div>

      <div class="row">
        <?php foreach ($operationalCards as $card): ?>
          <div class="col-sm-6 col-xl-3 mb-4">
            <div class="card hr-metric-card tone-<?php echo $esc($card['tone']); ?>">
              <div class="card-body">
                <div class="hr-icon">
                  <i class="mdi <?php echo $esc($card['icon']); ?>"></i>
                </div>
                <div class="hr-metric-label"><?php echo $esc($card['label']); ?></div>
                <div class="hr-metric-value hr-numeric"><?php echo $esc($card['value']); ?></div>
                <p class="hr-metric-meta"><?php echo $esc($card['meta']); ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="hr-section-head mt-2">
        <div>
          <h4>Resumo financeiro</h4>
          <p>Indicadores principais retornados pelo endpoint `pagamentos-dashboard`.</p>
        </div>
        <span class="hr-chip"><i class="mdi mdi-chart-line"></i> Janela analisada: <?php echo $esc($periodLabel); ?></span>
      </div>

      <div class="row">
        <?php foreach ($financialCards as $card): ?>
          <div class="col-md-6 col-xl mb-4">
            <div class="card hr-metric-card tone-<?php echo $esc($card['tone']); ?>">
              <div class="card-body">
                <div class="hr-metric-label"><?php echo $esc($card['label']); ?></div>
                <div class="hr-metric-value hr-numeric"><?php echo $esc($card['value']); ?></div>
                <p class="hr-metric-meta"><?php echo $esc($card['meta']); ?></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="row">
        <div class="col-xl-8 mb-4">
          <div class="card hr-chart-card">
            <div class="card-body">
              <div class="hr-chart-head">
                <div>
                  <h5 class="hr-card-title">Evolucao diaria</h5>
                  <p class="hr-card-subtitle">Total apurado e ticket medio ao longo da serie retornada pela API.</p>
                </div>
                <span class="hr-badge-soft"><?php echo $esc(count($rowsSerie)); ?> pontos</span>
              </div>
              <div class="hr-chart-note" id="serieChartStatus">Aguardando dados do periodo selecionado.</div>
              <div class="hr-chart-wrap">
                <canvas id="pagamentosSerieChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-4 mb-4">
          <div class="card hr-chart-card">
            <div class="card-body">
              <div class="hr-chart-head">
                <div>
                  <h5 class="hr-card-title">Mix por forma de pagamento</h5>
                  <p class="hr-card-subtitle">Participacao financeira por forma recebida.</p>
                </div>
                <span class="hr-badge-soft"><?php echo $esc(count($rowsFormas)); ?> formas</span>
              </div>
              <div class="hr-chart-note" id="formaChartStatus">Aguardando distribuicao por forma.</div>
              <div class="hr-chart-wrap hr-chart-wrap-sm">
                <canvas id="formasPagamentoChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-xl-7 mb-4">
          <div class="card hr-table-card">
            <div class="card-body">
              <div class="hr-section-head">
                <div>
                  <h4>Serie diaria</h4>
                  <p>Leitura tabular de faturamento, pedidos e ticket medio.</p>
                </div>
              </div>

              <?php if (!$rowsSerie): ?>
                <div class="hr-empty">Nenhuma linha retornada em `serie_diaria` para este filtro.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table hr-data-table">
                    <thead>
                      <tr>
                        <th>Data</th>
                        <th>Total apurado</th>
                        <th>Pedidos</th>
                        <th>Ticket medio</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($rowsSerie as $row): ?>
                        <tr>
                          <td><?php echo $esc($formatDateLabel($row['data'] ?? '')); ?></td>
                          <td class="hr-numeric"><?php echo $esc(homeresto_format_money($row['total_apurado'] ?? 0)); ?></td>
                          <td class="hr-numeric"><?php echo $esc(homeresto_format_number($row['total_pedidos'] ?? 0)); ?></td>
                          <td class="hr-numeric"><?php echo $esc(homeresto_format_money($row['ticket_medio'] ?? 0)); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="col-xl-5 mb-4">
          <div class="card hr-table-card">
            <div class="card-body">
              <div class="hr-section-head">
                <div>
                  <h4>Totais por forma de pagamento</h4>
                  <p>Distribuicao consolidada dentro do periodo analisado.</p>
                </div>
              </div>

              <?php if (!$rowsFormas): ?>
                <div class="hr-empty">Nenhum total por forma de pagamento foi retornado.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table hr-data-table">
                    <thead>
                      <tr>
                        <th>Forma</th>
                        <th>Total</th>
                        <th>Participacao</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($rowsFormas as $row): ?>
                        <?php
                        $formaTotal = (float)($row['total'] ?? $row['total_apurado'] ?? 0);
                        $share = (float)$dashboard['total_apurado'] > 0 ? ($formaTotal / (float)$dashboard['total_apurado']) * 100 : 0;
                        ?>
                        <tr>
                          <td><?php echo $esc($row['nome'] ?? $row['forma_pagamento'] ?? 'Sem nome'); ?></td>
                          <td class="hr-numeric"><?php echo $esc(homeresto_format_money($formaTotal)); ?></td>
                          <td class="hr-numeric"><?php echo $esc(homeresto_format_percent($share, 1)); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12 mb-4">
          <div class="card hr-table-card">
            <div class="card-body">
              <div class="hr-section-head">
                <div>
                  <h4>Serie diaria por forma</h4>
                  <p>Detalhe adicional quando a API devolve a abertura diaria por forma de pagamento.</p>
                </div>
              </div>

              <?php if (!$rowsSerieForma): ?>
                <div class="hr-empty">Sem linhas em `serie_diaria_por_forma` para o filtro atual.</div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table hr-data-table">
                    <thead>
                      <tr>
                        <th>Data</th>
                        <th>Forma</th>
                        <th>Total apurado</th>
                        <th>Pedidos</th>
                        <th>Ticket medio</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($rowsSerieForma as $row): ?>
                        <tr>
                          <td><?php echo $esc($formatDateLabel($row['data'] ?? '')); ?></td>
                          <td><?php echo $esc($row['forma_pagamento'] ?? $row['nome'] ?? 'Sem nome'); ?></td>
                          <td class="hr-numeric"><?php echo $esc(homeresto_format_money($row['total_apurado'] ?? 0)); ?></td>
                          <td class="hr-numeric"><?php echo $esc(homeresto_format_number($row['total_pedidos'] ?? 0)); ?></td>
                          <td class="hr-numeric"><?php echo $esc(homeresto_format_money($row['ticket_medio'] ?? 0)); ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="hr-section-head mt-2">
        <div>
          <h4>Indicadores por usuario</h4>
          <p>R16, R17 e R18 usam o mesmo filtro da tela para acompanhar faturamento, produtividade e mesas atendidas.</p>
        </div>
        <span class="hr-chip"><i class="mdi mdi-account-group-outline"></i> Relatorios analiticos R16, R17 e R18</span>
      </div>

      <?php if (($userIndicatorsNotice ?? '') !== '' && !$hasUserIndicatorData): ?>
        <div class="alert alert-info hr-alert mb-4" role="alert">
          <strong>Indicadores por usuario indisponiveis:</strong> <?php echo $esc($userIndicatorsNotice); ?>
        </div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($userCards as $card): ?>
            <div class="col-sm-6 col-xl-3 mb-4">
              <div class="card hr-metric-card tone-<?php echo $esc($card['tone']); ?>">
                <div class="card-body">
                  <div class="hr-icon">
                    <i class="mdi <?php echo $esc($card['icon']); ?>"></i>
                  </div>
                  <div class="hr-metric-label"><?php echo $esc($card['label']); ?></div>
                  <div class="hr-metric-value"><?php echo $esc($card['value']); ?></div>
                  <p class="hr-metric-meta"><?php echo $esc($card['meta']); ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="row">
          <div class="col-xl-7 mb-4">
            <div class="card hr-chart-card">
              <div class="card-body">
                <div class="hr-chart-head">
                  <div>
                    <h5 class="hr-card-title">Faturamento por usuario no periodo</h5>
                    <p class="hr-card-subtitle">Serie do R17 com ate quatro usuarios de maior faturamento no filtro aplicado.</p>
                  </div>
                  <span class="hr-badge-soft"><?php echo $esc(count($r17Rows)); ?> linhas</span>
                </div>
                <div class="hr-chart-note" id="userTrendStatus">Aguardando serie do R17.</div>
                <div class="hr-chart-wrap">
                  <canvas id="userTrendChart"></canvas>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-5 mb-4">
            <div class="card hr-chart-card">
              <div class="card-body">
                <div class="hr-chart-head">
                  <div>
                    <h5 class="hr-card-title">Ranking por usuario</h5>
                    <p class="hr-card-subtitle">R16 consolidado com faturamento e mesas atendidas por usuario.</p>
                  </div>
                  <span class="hr-badge-soft"><?php echo $esc(count($rowsR16)); ?> usuarios</span>
                </div>
                <div class="hr-chart-note" id="userRankingStatus">Aguardando ranking do R16.</div>
                <div class="hr-chart-wrap hr-chart-wrap-sm">
                  <canvas id="userRankingChart"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-xl-6 mb-4">
            <div class="card hr-table-card">
              <div class="card-body">
                <div class="hr-section-head">
                  <div>
                    <h4>R16 | Faturamento por usuario</h4>
                    <p>Consolidado de faturamento, mesas atendidas, ticket medio e ciclo medio.</p>
                  </div>
                </div>

                <?php if (!$rowsR16): ?>
                  <div class="hr-empty">Nenhuma linha retornada pelo R16 para o filtro atual.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table hr-data-table">
                      <thead>
                        <tr>
                          <th>Usuario</th>
                          <th>Pedidos</th>
                          <th>Mesas</th>
                          <th>Faturamento</th>
                          <th>Ticket medio</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($rowsR16 as $row): ?>
                          <tr>
                            <td><?php echo $esc($row['usuario'] ?? 'Sem nome'); ?></td>
                            <td class="hr-numeric"><?php echo $esc(homeresto_format_number($row['pedidos_fechados'] ?? 0)); ?></td>
                            <td class="hr-numeric"><?php echo $esc(homeresto_format_number($row['mesas_atendidas'] ?? 0)); ?></td>
                            <td class="hr-numeric"><?php echo $esc(homeresto_format_money($row['faturamento'] ?? 0)); ?></td>
                            <td class="hr-numeric"><?php echo $esc(homeresto_format_money($row['ticket_medio'] ?? 0)); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="col-xl-6 mb-4">
            <div class="card hr-table-card">
              <div class="card-body">
                <div class="hr-section-head">
                  <div>
                    <h4>R18 | Mesas atendidas por usuario</h4>
                    <p>Abertura por mesa para acompanhar o atendimento individual.</p>
                  </div>
                </div>

                <?php if (!$rowsR18): ?>
                  <div class="hr-empty">Nenhuma linha retornada pelo R18 para o filtro atual.</div>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table hr-data-table">
                      <thead>
                        <tr>
                          <th>Usuario</th>
                          <th>Mesa</th>
                          <th>Pedidos</th>
                          <th>Faturamento</th>
                          <th>Ticket medio</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($rowsR18 as $row): ?>
                          <tr>
                            <td><?php echo $esc($row['usuario'] ?? 'Sem nome'); ?></td>
                            <td class="hr-numeric"><?php echo $esc(homeresto_format_number($row['mesa_id'] ?? 0)); ?></td>
                            <td class="hr-numeric"><?php echo $esc(homeresto_format_number($row['pedidos_fechados'] ?? 0)); ?></td>
                            <td class="hr-numeric"><?php echo $esc(homeresto_format_money($row['faturamento'] ?? 0)); ?></td>
                            <td class="hr-numeric"><?php echo $esc(homeresto_format_money($row['ticket_medio'] ?? 0)); ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const periodoSelect = document.getElementById('homeRestoPeriodo');
        const helper = document.getElementById('homeRestoFilterHelper');
        const fields = {
          data: document.getElementById('fieldData'),
          mes: document.getElementById('fieldMes'),
          ano: document.getElementById('fieldAno'),
          inicio: document.getElementById('fieldInicio'),
          fim: document.getElementById('fieldFim')
        };

        function toggleField(element, visible) {
          if (!element) {
            return;
          }
          element.classList.toggle('d-none', !visible);
        }

        function updateFilters() {
          const mode = periodoSelect ? periodoSelect.value : 'dia';
          toggleField(fields.data, mode === 'dia');
          toggleField(fields.mes, mode === 'mes');
          toggleField(fields.ano, mode === 'ano');

          const usesRange = mode === 'semana' || mode === 'periodo';
          toggleField(fields.inicio, usesRange);
          toggleField(fields.fim, usesRange);

          if (!helper) {
            return;
          }

          if (mode === 'dia') {
            helper.textContent = 'Dia usa a data selecionada.';
          } else if (mode === 'semana') {
            helper.textContent = 'Semana usa data inicio e data fim porque a API recebe intervalo.';
          } else if (mode === 'mes') {
            helper.textContent = 'Mes envia o valor no formato AAAA-MM.';
          } else if (mode === 'ano') {
            helper.textContent = 'Ano envia apenas o ano numerico.';
          } else {
            helper.textContent = 'Periodo usa um intervalo livre e valida se a data inicial nao e maior que a final.';
          }
        }

        if (periodoSelect) {
          periodoSelect.addEventListener('change', updateFilters);
          updateFilters();
        }

        if (typeof Chart === 'undefined') {
          const serieStatus = document.getElementById('serieChartStatus');
          const formaStatus = document.getElementById('formaChartStatus');
          if (serieStatus) {
            serieStatus.textContent = 'Chart.js nao esta disponivel para renderizar o grafico.';
          }
          if (formaStatus) {
            formaStatus.textContent = 'Chart.js nao esta disponivel para renderizar o grafico.';
          }
          return;
        }

        const labelsSerie = <?php echo json_encode($chartSerieLabels, $jsonFlags); ?>;
        const totaisSerie = <?php echo json_encode($serieTotais, $jsonFlags); ?>;
        const ticketsSerie = <?php echo json_encode($serieTickets, $jsonFlags); ?>;
        const labelsFormas = <?php echo json_encode($formasLabels, $jsonFlags); ?>;
        const totaisFormas = <?php echo json_encode($formasTotais, $jsonFlags); ?>;
        const userTrendLabels = <?php echo json_encode($r17ChartLabelsFormatted, $jsonFlags); ?>;
        const userTrendDatasets = <?php echo json_encode($r17ChartDatasets, $jsonFlags); ?>;
        const userRankingLabels = <?php echo json_encode($r16ChartLabels, $jsonFlags); ?>;
        const userRankingRevenue = <?php echo json_encode($r16ChartRevenue, $jsonFlags); ?>;
        const userRankingTables = <?php echo json_encode($r16ChartTables, $jsonFlags); ?>;

        const colors = ['#0f6cbd', '#0891b2', '#0f766e', '#c27b0a', '#7c3aed', '#d9485f', '#334155'];
        const formaColors = labelsFormas.map(function (_, index) {
          return colors[index % colors.length];
        });

        const serieStatus = document.getElementById('serieChartStatus');
        const formaStatus = document.getElementById('formaChartStatus');
        const userTrendStatus = document.getElementById('userTrendStatus');
        const userRankingStatus = document.getElementById('userRankingStatus');

        if (labelsSerie.length) {
          const serieCtx = document.getElementById('pagamentosSerieChart');
          if (serieCtx) {
            new Chart(serieCtx, {
              type: 'line',
              data: {
                labels: labelsSerie,
                datasets: [
                  {
                    label: 'Total apurado',
                    data: totaisSerie,
                    borderColor: '#0f6cbd',
                    backgroundColor: 'rgba(15, 108, 189, 0.10)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    yAxisID: 'y'
                  },
                  {
                    label: 'Ticket medio',
                    data: ticketsSerie,
                    borderColor: '#c27b0a',
                    backgroundColor: 'rgba(194, 123, 10, 0.10)',
                    fill: false,
                    tension: 0.3,
                    borderWidth: 2,
                    yAxisID: 'y1'
                  }
                ]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                  legend: { position: 'bottom' }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    ticks: {
                      callback: function (value) {
                        return 'R$ ' + Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                      }
                    }
                  },
                  y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                      callback: function (value) {
                        return 'R$ ' + Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                      }
                    }
                  }
                }
              }
            });
            if (serieStatus) {
              serieStatus.textContent = 'Comparativo diario de total apurado e ticket medio.';
            }
          }
        } else if (serieStatus) {
          serieStatus.textContent = 'Sem pontos suficientes para montar a evolucao diaria.';
        }

        if (labelsFormas.length) {
          const formaCtx = document.getElementById('formasPagamentoChart');
          if (formaCtx) {
            new Chart(formaCtx, {
              type: 'doughnut',
              data: {
                labels: labelsFormas,
                datasets: [{
                  data: totaisFormas,
                  backgroundColor: formaColors,
                  borderWidth: 0
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: { position: 'bottom' }
                }
              }
            });
            if (formaStatus) {
              formaStatus.textContent = 'Distribuicao do valor recebido por forma de pagamento.';
            }
          }
        } else if (formaStatus) {
          formaStatus.textContent = 'Sem dados de forma de pagamento para o filtro atual.';
        }

        if (userTrendLabels.length && userTrendDatasets.length) {
          const trendCtx = document.getElementById('userTrendChart');
          if (trendCtx) {
            new Chart(trendCtx, {
              type: 'line',
              data: {
                labels: userTrendLabels,
                datasets: userTrendDatasets.map(function (dataset) {
                  return {
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: dataset.borderColor,
                    backgroundColor: dataset.backgroundColor,
                    fill: false,
                    tension: 0.32,
                    borderWidth: 2.4,
                    pointRadius: 3
                  };
                })
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                  legend: { position: 'bottom' }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    ticks: {
                      callback: function (value) {
                        return 'R$ ' + Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                      }
                    }
                  }
                }
              }
            });
            if (userTrendStatus) {
              userTrendStatus.textContent = 'R17 comparando o faturamento por usuario ao longo do periodo.';
            }
          }
        } else if (userTrendStatus) {
          userTrendStatus.textContent = 'Sem dados suficientes do R17 para montar a tendencia por usuario.';
        }

        if (userRankingLabels.length) {
          const rankingCtx = document.getElementById('userRankingChart');
          if (rankingCtx) {
            new Chart(rankingCtx, {
              data: {
                labels: userRankingLabels,
                datasets: [
                  {
                    type: 'bar',
                    label: 'Faturamento',
                    data: userRankingRevenue,
                    backgroundColor: 'rgba(15, 108, 189, 0.72)',
                    borderRadius: 10,
                    yAxisID: 'y'
                  },
                  {
                    type: 'line',
                    label: 'Mesas atendidas',
                    data: userRankingTables,
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, 0.14)',
                    tension: 0.28,
                    borderWidth: 2.2,
                    pointRadius: 3,
                    yAxisID: 'y1'
                  }
                ]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                  legend: { position: 'bottom' }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    ticks: {
                      callback: function (value) {
                        return 'R$ ' + Number(value || 0).toLocaleString('pt-BR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                      }
                    }
                  },
                  y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false }
                  }
                }
              }
            });
            if (userRankingStatus) {
              userRankingStatus.textContent = 'R16 consolidado por usuario com faturamento e mesas atendidas.';
            }
          }
        } else if (userRankingStatus) {
          userRankingStatus.textContent = 'Sem dados suficientes do R16 para montar o ranking por usuario.';
        }
      });
    </script>
  </div>
</div>

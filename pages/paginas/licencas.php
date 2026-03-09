<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Gestao de assinaturas</h3>
                    <p class="text-muted mb-0">Controle central de licencas e status.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-refresh"></i> Atualizar</button>
                    <button class="btn btn-primary text-white"><i class="mdi mdi-plus"></i> Renovar licenca</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Licencas ativas</p>
                    <h3 class="mb-0">96</h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Em trial</p>
                    <h3 class="mb-0 text-warning">17</h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Vencidas</p>
                    <h3 class="mb-0 text-danger">5</h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Canceladas</p>
                    <h3 class="mb-0">2</h3>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <div class="d-sm-flex justify-content-between align-items-start mb-3">
                      <div>
                        <h4 class="card-title card-title-dash">Assinaturas</h4>
                        <p class="card-subtitle card-subtitle-dash">Empresa, plano e periodo de licenca.</p>
                      </div>
                      <div class="btn-wrapper">
                        <button class="btn btn-outline-secondary btn-sm me-2"><i class="mdi mdi-filter"></i> Filtrar</button>
                        <button class="btn btn-outline-secondary btn-sm"><i class="mdi mdi-calendar"></i> Periodo</button>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Empresa</th>
                            <th>Plano</th>
                            <th>Inicio</th>
                            <th>Expiracao</th>
                            <th>Status</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>Resto Group</td>
                            <td>Pro</td>
                            <td>15/11/2025</td>
                            <td>22/03/2026</td>
                            <td><div class="badge badge-opacity-success">ativo</div></td>
                            <td>
                              <button class="btn btn-outline-primary btn-sm me-1"><i class="mdi mdi-refresh"></i> Renovar</button>
                              <button class="btn btn-outline-secondary btn-sm me-1"><i class="mdi mdi-credit-card-outline"></i> Alterar plano</button>
                              <button class="btn btn-outline-danger btn-sm"><i class="mdi mdi-lock"></i> Suspender</button>
                            </td>
                          </tr>
                          <tr>
                            <td>Mundo Foods</td>
                            <td>Growth</td>
                            <td>10/02/2026</td>
                            <td>28/03/2026</td>
                            <td><div class="badge badge-opacity-warning">trial</div></td>
                            <td>
                              <button class="btn btn-outline-primary btn-sm me-1"><i class="mdi mdi-refresh"></i> Renovar</button>
                              <button class="btn btn-outline-secondary btn-sm me-1"><i class="mdi mdi-credit-card-outline"></i> Alterar plano</button>
                              <button class="btn btn-outline-danger btn-sm"><i class="mdi mdi-cancel"></i> Cancelar</button>
                            </td>
                          </tr>
                          <tr>
                            <td>VidaMais</td>
                            <td>Starter</td>
                            <td>20/05/2023</td>
                            <td>28/02/2026</td>
                            <td><div class="badge badge-opacity-danger">vencido</div></td>
                            <td>
                              <button class="btn btn-outline-primary btn-sm me-1"><i class="mdi mdi-refresh"></i> Renovar</button>
                              <button class="btn btn-outline-secondary btn-sm me-1"><i class="mdi mdi-credit-card-outline"></i> Alterar plano</button>
                              <button class="btn btn-outline-danger btn-sm"><i class="mdi mdi-cancel"></i> Cancelar</button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

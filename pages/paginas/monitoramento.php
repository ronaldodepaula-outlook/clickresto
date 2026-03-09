<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Monitoramento do sistema</h3>
                    <p class="text-muted mb-0">Status operacional e uso da API.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-refresh"></i> Atualizar</button>
                    <button class="btn btn-primary text-white"><i class="mdi mdi-bell-ring-outline"></i> Alertas</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Empresas online</p>
                    <h3 class="mb-0 text-success">46</h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Pedidos em processamento</p>
                    <h3 class="mb-0">212</h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Consumo da API</p>
                    <h3 class="mb-0">78%</h3>
                  </div>
                </div>
              </div>
              <div class="col-sm-6 col-xl-3 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <p class="text-muted mb-1">Alertas ativos</p>
                    <h3 class="mb-0 text-danger">3</h3>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Logs recentes</h4>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Horario</th>
                            <th>Evento</th>
                            <th>Detalhes</th>
                            <th>Status</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>10:05</td>
                            <td>API</td>
                            <td>Latencia acima do esperado</td>
                            <td><div class="badge badge-opacity-warning">atencao</div></td>
                          </tr>
                          <tr>
                            <td>09:48</td>
                            <td>Fila de pedidos</td>
                            <td>Processamento normalizado</td>
                            <td><div class="badge badge-opacity-success">ok</div></td>
                          </tr>
                          <tr>
                            <td>09:12</td>
                            <td>Pagamento</td>
                            <td>Falha de webhook</td>
                            <td><div class="badge badge-opacity-danger">critico</div></td>
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

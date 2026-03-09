<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Configuracoes do sistema</h3>
                    <p class="text-muted mb-0">Parametros globais do SaaS.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-primary text-white"><i class="mdi mdi-content-save"></i> Salvar ajustes</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Parametros gerais</h4>
                    <div class="mb-3">
                      <label class="form-label">Tempo de trial (dias)</label>
                      <input type="number" class="form-control" value="14">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Limite padrao de usuarios</label>
                      <input type="number" class="form-control" value="10">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Limite padrao de produtos</label>
                      <input type="number" class="form-control" value="200">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Dominio white label</label>
                      <input type="text" class="form-control" placeholder="app.suaempresa.com">
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-lg-6 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">SMTP e pagamentos</h4>
                    <div class="mb-3">
                      <label class="form-label">SMTP host</label>
                      <input type="text" class="form-control" placeholder="smtp.seudominio.com">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">SMTP usuario</label>
                      <input type="text" class="form-control" placeholder="smtp@seudominio.com">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">SMTP senha</label>
                      <input type="password" class="form-control" placeholder="********">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Integracao de pagamento</label>
                      <select class="form-select">
                        <option>Mercado Pago</option>
                        <option>Stripe</option>
                        <option>PagSeguro</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

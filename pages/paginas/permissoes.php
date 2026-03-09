<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Controle de acesso</h3>
                    <p class="text-muted mb-0">Perfis, permissoes e acesso a modulos.</p>
                  </div>
                  <div class="btn-wrapper">
                    <button class="btn btn-outline-secondary me-2"><i class="mdi mdi-account-key"></i> Novo perfil</button>
                    <button class="btn btn-primary text-white"><i class="mdi mdi-shield-plus"></i> Nova permissao</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-6 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Perfis globais</h4>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Perfil</th>
                            <th>Descricao</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>admin_master</td>
                            <td>Gestor do SaaS</td>
                            <td>
                              <button class="btn btn-outline-primary btn-sm me-1"><i class="mdi mdi-pencil"></i></button>
                              <button class="btn btn-outline-danger btn-sm"><i class="mdi mdi-delete"></i></button>
                            </td>
                          </tr>
                          <tr>
                            <td>gerente</td>
                            <td>Gestao operacional</td>
                            <td>
                              <button class="btn btn-outline-primary btn-sm me-1"><i class="mdi mdi-pencil"></i></button>
                              <button class="btn btn-outline-danger btn-sm"><i class="mdi mdi-delete"></i></button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-6 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Permissoes</h4>
                    <div class="table-responsive mt-3">
                      <table class="table select-table">
                        <thead>
                          <tr>
                            <th>Permissao</th>
                            <th>Modulo</th>
                            <th>Acoes</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>empresas.visualizar</td>
                            <td>Empresas</td>
                            <td>
                              <button class="btn btn-outline-primary btn-sm me-1"><i class="mdi mdi-pencil"></i></button>
                              <button class="btn btn-outline-danger btn-sm"><i class="mdi mdi-delete"></i></button>
                            </td>
                          </tr>
                          <tr>
                            <td>planos.editar</td>
                            <td>Licencas</td>
                            <td>
                              <button class="btn btn-outline-primary btn-sm me-1"><i class="mdi mdi-pencil"></i></button>
                              <button class="btn btn-outline-danger btn-sm"><i class="mdi mdi-delete"></i></button>
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

<div class="main-panel">
          <div class="content-wrapper">
            <div class="row">
              <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                  <div>
                    <h3 class="mb-1">Cadastro / Edicao de empresa</h3>
                    <p class="text-muted mb-0">Formulario focado em dados essenciais e decisoes rapidas.</p>
                  </div>
                  <div class="btn-wrapper">
                    <a href="index.php?paginas=empresas" class="btn btn-outline-secondary"><i class="mdi mdi-arrow-left"></i> Voltar</a>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-8 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Dados da empresa</h4>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" placeholder="Empresa Exemplo">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Nome fantasia</label>
                        <input type="text" class="form-control" placeholder="Empresa Ex">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">CNPJ</label>
                        <input type="text" class="form-control" placeholder="00.000.000/0001-00">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control" placeholder="(00) 00000-0000">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" placeholder="contato@empresa.com">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Endereco</label>
                        <input type="text" class="form-control" placeholder="Rua X, 123">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Cidade</label>
                        <input type="text" class="form-control" placeholder="Sao Paulo">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-control" placeholder="SP">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-lg-4 grid-margin stretch-card">
                <div class="card card-rounded">
                  <div class="card-body">
                    <h4 class="card-title card-title-dash">Plano e status</h4>
                    <div class="mb-3">
                      <label class="form-label">Plano</label>
                      <select class="form-select">
                        <option>Starter</option>
                        <option>Growth</option>
                        <option>Pro</option>
                        <option>Enterprise</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Limite de usuarios</label>
                      <input type="number" class="form-control" placeholder="40">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Limite de produtos</label>
                      <input type="number" class="form-control" placeholder="200">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select class="form-select">
                        <option>ativo</option>
                        <option>suspenso</option>
                        <option>cancelado</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Observacoes internas</label>
                      <textarea class="form-control" rows="4" placeholder="Notas administrativas"></textarea>
                    </div>
                    <div class="d-flex gap-2">
                      <button class="btn btn-primary text-white w-100">Salvar</button>
                      <button class="btn btn-outline-secondary w-100">Cancelar</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

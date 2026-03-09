<!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">
          <div class="row">
            <div class="col-sm-12">
              <div class="home-tab">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom">
                  <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active ps-0" id="home-tab" data-bs-toggle="tab" href="#overview" role="tab"
                        aria-controls="overview" aria-selected="true">Contact</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="profile-tab" data-bs-toggle="tab" href="#audiences" role="tab"
                        aria-selected="false">Lead</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="contact-tab" data-bs-toggle="tab" href="#demographics" role="tab"
                        aria-selected="false">Account</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link border-0" id="more-tab" data-bs-toggle="tab" href="#more" role="tab"
                        aria-selected="false">More</a>
                    </li>
                  </ul>
                  <div>
                    <div class="btn-wrapper">
                      <a href="#" class="btn btn-otline-dark align-items-center"><i class="icon-share"></i> Share</a>
                      <a href="#" class="btn btn-otline-dark"><i class="icon-printer"></i> Print</a>
                      <a href="#" class="btn btn-primary text-white me-0"><i class="icon-plus"></i> New</a>
                    </div>
                  </div>
                </div>
                <div class="tab-content tab-content-basic">
                  <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview">
                    <div class="row">
                      <div class="col-sm-12">
                        <div class="statistics-details d-flex align-items-center justify-content-between">
                          <div>
                            <p class="statistics-title">Number of sales</p>
                            <h3 class="rate-percentage">2431</h3>
                          </div>
                          <div>
                            <p class="statistics-title">Sales Revenue</p>
                            <h3 class="rate-percentage">$24.403</h3>
                          </div>
                          <div>
                            <p class="statistics-title">Total Products</p>
                            <h3 class="rate-percentage">84234</h3>
                          </div>
                          <div class="d-none d-md-block">
                            <p class="statistics-title">Total Customers</p>
                            <h3 class="rate-percentage">64732</h3>
                          </div>
                          <div class="d-none d-md-block">
                            <p class="statistics-title">Average Price</p>
                            <h3 class="rate-percentage">$2431</h3>
                          </div>
                          <div class="d-none d-md-block">
                            <p class="statistics-title">Total Turnover</p>
                            <h3 class="rate-percentage">$5567</h3>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-6 d-flex flex-column">
                        <div class="row flex-grow">
                          <div class="col-12 col-lg-4 col-lg-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                              <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                  <div>
                                    <h4 class="card-title card-title-dash">Revenue Analytics</h4>
                                  </div>
                                  <div>
                                    <div class="dropdown">
                                      <button class="btn btn-light dropdown-toggle toggle-dark btn-lg mb-0 me-0"
                                        type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false"> This month </button>
                                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                        <h6 class="dropdown-header">Settings</h6>
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">Separated link</a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="chartjs-wrapper mt-4 chart-height-dash">
                                  <canvas id="marketingOverviewCrm"></canvas>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-6 d-flex flex-column">
                        <div class="row flex-grow">
                          <div class="col-12 col-lg-4 col-lg-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                              <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                  <div>
                                    <h4 class="card-title card-title-dash">Sales Analytics</h4>
                                  </div>
                                  <div>
                                    <div class="dropdown">
                                      <button class="btn btn-light dropdown-toggle toggle-dark btn-lg mb-0 me-0"
                                        type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false"> This month </button>
                                      <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                                        <h6 class="dropdown-header">Weekly</h6>
                                        <a class="dropdown-item" href="#">Monthly</a>
                                        <a class="dropdown-item" href="#">Yearly</a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="chartjs-wrapper mt-4">
                                  <div class="d-lg-flex justify-content-between">
                                    <div class="doughnut-wrapper">
                                      <canvas class="my-auto" id="doughnutChartCrm" height="170"></canvas>
                                    </div>
                                    <div id="doughnutChartCrm-legend" class="mt-4 text-center"></div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-lg-8 d-flex flex-column">
                        <div class="row flex-grow">
                          <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                              <div class="card-body">
                                <div class="d-sm-flex justify-content-between align-items-start">
                                  <div>
                                    <h4 class="card-title card-title-dash">Sales Trend</h4>
                                  </div>
                                </div>
                                <div class="me-3 mt-4">
                                  <div id="salesTrend-legend"></div>
                                </div>
                                <div class="chartjs-bar-wrapper mt-3">
                                  <canvas id="salesTrend"></canvas>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row flex-grow">
                          <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                              <div class="card-body">
                                <div class="d-sm-flex justify-content-between align-items-start">
                                  <div>
                                    <h4 class="card-title card-title-dash">Product Overview</h4>
                                  </div>
                                  <div>
                                    <p>13/03/2018 to 20/03/2018</p>
                                  </div>
                                </div>
                                <div class="table-responsive  mt-1">
                                  <table class="table select-table">
                                    <thead>
                                      <tr>
                                        <th>
                                          <p class="dark-text">Product Name</p>
                                        </th>
                                        <th>
                                          <p class="dark-text">Sales</p>
                                        </th>
                                        <th>
                                          <p class="dark-text">Record Point</p>
                                        </th>
                                        <th>
                                          <p class="dark-text">Stock</p>
                                        </th>
                                        <th>
                                          <p class="dark-text">Amount</p>
                                        </th>
                                        <th>
                                          <p class="dark-text">Stock Status
                                          <p>
                                        </th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr>
                                        <td>
                                          <p class="dark-text">Adidas Shoes</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">453</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">04</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">447</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">$293.01</p>
                                        </td>
                                        <td>
                                          <div class="badge badge-success">In Stock</div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <p class="dark-text">Puma Sports Wear</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">567</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">08</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">225</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">$578.21</p>
                                        </td>
                                        <td>
                                          <div class="badge badge-danger">Out of Stock</div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <p class="dark-text">Nike Casual Shoes</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">122</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">05</p>
                                        </td>
                                        <td>
                                          <p>765</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">$312.10</p>
                                        </td>
                                        <td>
                                          <div class="badge badge-success">In Stock</div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <p class="dark-text">Adidas Football</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">324</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">03</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">347</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">$145.02</p>
                                        </td>
                                        <td>
                                          <div class="badge badge-success">In Stock</div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <p class="dark-text">Puma T-shirts</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">412</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">07</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">692</p>
                                        </td>
                                        <td>
                                          <p class="dark-text">$424.26</p>
                                        </td>
                                        <td>
                                          <div class="badge badge-danger">Out of Stock</div>
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
                      <div class="col-lg-4 d-flex flex-column">
                        <div class="row flex-grow">
                          <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded">
                              <div class="card-body">
                                <div class="row">
                                  <div class="col-lg-12">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                      <div>
                                        <h4 class="card-title card-title-dash">Top Performer</h4>
                                      </div>
                                    </div>
                                    <div class="mt-3">
                                      <div
                                        class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <div class="d-flex">
                                          <img class="img-sm rounded" src="../../../assets/images/faces/face1.jpg"
                                            alt="profile">
                                          <div class="wrapper ms-3">
                                            <p class="ms-1 mb-1 fw-bold">Isabella Becker</p>
                                            <small class="text-muted mb-0">isabellabacker@gmail.com</small>
                                          </div>
                                        </div>
                                      </div>
                                      <div
                                        class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <div class="d-flex">
                                          <img class="img-sm rounded" src="../../../assets/images/faces/face2.jpg"
                                            alt="profile">
                                          <div class="wrapper ms-3">
                                            <p class="ms-1 mb-1 fw-bold">Alex Joshi</p>
                                            <small class="text-muted mb-0">alex00123@gmail.com</small>
                                          </div>
                                        </div>
                                      </div>
                                      <div
                                        class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <div class="d-flex">
                                          <img class="img-sm rounded" src="../../../assets/images/faces/face3.jpg"
                                            alt="profile">
                                          <div class="wrapper ms-3">
                                            <p class="ms-1 mb-1 fw-bold">Catherin Rott</p>
                                            <small class="text-muted mb-0">catherinrott@gmail.com</small>
                                          </div>
                                        </div>
                                      </div>
                                      <div
                                        class="wrapper d-flex align-items-center justify-content-between py-2 border-bottom">
                                        <div class="d-flex">
                                          <img class="img-sm rounded" src="../../../assets/images/faces/face4.jpg"
                                            alt="profile">
                                          <div class="wrapper ms-3">
                                            <p class="ms-1 mb-1 fw-bold">Selva Dobi</p>
                                            <small class="text-muted mb-0">selvadobi801@gmail.com</small>
                                          </div>
                                        </div>
                                      </div>
                                      <div class="wrapper d-flex align-items-center justify-content-between pt-2">
                                        <div class="d-flex">
                                          <img class="img-sm rounded" src="../../../assets/images/faces/face5.jpg"
                                            alt="profile">
                                          <div class="wrapper ms-3">
                                            <p class="ms-1 mb-1 fw-bold">Aman Walt</p>
                                            <small class="text-muted mb-0">amanwalt@gmail.com</small>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-12 grid-margin stretch-card">
                            <div class="card card-rounded ad-dashboard">
                              <div class="card-body text-center text-white">
                                <div>
                                  <img class="rounded-10 mb-3" src="../../../assets/images/dashboard/hand.png"
                                    alt="profile">
                                </div>
                                <h4 class="display-5 fw-medium mb-3">Get StarAdmin 2 Pro <br> Mobile App </h4>
                                <p class="mb-3">Download StarAdmin 2 Pro mobile app. Now available in play store and app
                                  store</p>
                                <button class="btn btn-light btn-lg">Download Now</button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:../../partials/_footer.html -->
        <footer class="footer">
  <div class="d-sm-flex justify-content-center justify-content-sm-between">
    <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Premium <a
        href="https://www.bootstrapdash.com/" target="_blank">Bootstrap admin template</a> from BootstrapDash.</span>
    <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center">Copyright © 2025. All rights reserved.</span>
  </div>
</footer>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
<x-layouts.app title="Dashboard">
    <div class="row">
        <div class="col-lg-8 mb-4 order-0">
            <div class="card">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Welcome to {{ config('app.name', 'Funflow HRMS') }}</h5>
                            <p class="mb-4">
                                Manage employees, attendance, payroll, and HR workflows from one place.
                            </p>
                            <a href="#" class="btn btn-sm btn-outline-primary">View Reports</a>
                        </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140"
                                alt="Dashboard" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 order-1">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}"
                                        alt="Performance" class="rounded" />
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Active Employees</span>
                            <h3 class="card-title mb-2">248</h3>
                            <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +8.2%</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Payroll"
                                        class="rounded" />
                                </div>
                            </div>
                            <span class="d-block mb-1">Payroll Processed</span>
                            <h3 class="card-title text-nowrap mb-1">$86,420</h3>
                            <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> +5.1%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 order-2 mb-4">
            <div class="card">
                <div class="row row-bordered g-0">
                    <div class="col-md-8">
                        <h5 class="card-header m-0 me-2 pb-3">Attendance Trend</h5>
                        <div class="px-3 pb-3">
                            <img src="{{ asset('assets/img/backgrounds/18.jpg') }}" class="img-fluid rounded"
                                alt="Attendance Trend" />
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card-body">
                            <div class="text-center">
                                <span class="badge bg-label-primary">Current Month</span>
                            </div>
                        </div>
                        <div class="text-center fw-semibold pt-3 mb-2">Overall Attendance: 96.4%</div>

                        <div class="d-flex px-4 p-4 gap-3 justify-content-between">
                            <div class="d-flex">
                                <div class="me-2">
                                    <span class="badge bg-label-primary p-2"><i
                                            class="bx bx-user-check text-primary"></i></span>
                                </div>
                                <div class="d-flex flex-column">
                                    <small>Present</small>
                                    <h6 class="mb-0">238</h6>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="me-2">
                                    <span class="badge bg-label-warning p-2"><i
                                            class="bx bx-user-x text-warning"></i></span>
                                </div>
                                <div class="d-flex flex-column">
                                    <small>On Leave</small>
                                    <h6 class="mb-0">10</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 order-3 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0 me-2">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <ul class="p-0 m-0">
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="{{ asset('assets/img/icons/unicons/chart.png') }}" alt="Expense"
                                    class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">New Joiner</small>
                                    <h6 class="mb-0">2 employees onboarded</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Payroll"
                                    class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">Payroll</small>
                                    <h6 class="mb-0">Monthly payroll generated</h6>
                                </div>
                            </div>
                        </li>
                        <li class="d-flex">
                            <div class="avatar flex-shrink-0 me-3">
                                <img src="{{ asset('assets/img/icons/unicons/cc-warning.png') }}" alt="Leave"
                                    class="rounded" />
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <small class="text-muted d-block mb-1">Leave Request</small>
                                    <h6 class="mb-0">5 requests pending approval</h6>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
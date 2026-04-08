<x-layouts.app title="Employee Dashboard">
    <div id="employee-dashboard-page" data-stats-url="{{ route('dashboard.stats') }}">
        <x-ui.page-header title="Employee Dashboard"
            description="Track your service requests and assignment footprint." />

        <x-dashboard.hero-panel title="Welcome back"
            description="Your personal workspace for service requests and assignment tracking." class="mb-4">
            <x-slot:highlights>
                <span class="ui-dashboard-chip">
                    <i class="bx bx-loader-circle"></i>
                    <span id="hero-employee-open-requests" class="fw-bold">0</span>
                    <small>Open Requests</small>
                </span>

                <span class="ui-dashboard-chip">
                    <i class="bx bx-user-pin"></i>
                    <span id="hero-employee-active-assignments" class="fw-bold">0</span>
                    <small>Active Assignments</small>
                </span>

                <span class="ui-dashboard-chip">
                    <i class="bx bx-check-shield"></i>
                    <span id="hero-employee-completed-requests" class="fw-bold">0</span>
                    <small>Completed Requests</small>
                </span>
            </x-slot:highlights>

            <x-slot:actions>
                <span class="ui-dashboard-inline-meta">
                    <i class="bx bx-time-five"></i>
                    <span class="summary-last-updated">Updated --</span>
                </span>
            </x-slot:actions>
        </x-dashboard.hero-panel>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <x-dashboard.stat-tile label="Submitted" value-id="summary-employee-submitted"
                    subtitle="Requests submitted and waiting for handling." icon="bx bx-send" tone="primary"
                    :loading="true" />
            </div>

            <div class="col-xl-3 col-md-6">
                <x-dashboard.stat-tile label="In Progress" value-id="summary-employee-in-progress"
                    subtitle="Requests currently being fulfilled." icon="bx bx-loader-circle" tone="warning"
                    :loading="true" />
            </div>

            <div class="col-xl-3 col-md-6">
                <x-dashboard.stat-tile label="Completed" value-id="summary-employee-completed"
                    subtitle="Successfully fulfilled requests." icon="bx bx-check-circle" tone="success"
                    :loading="true" />
            </div>

            <div class="col-xl-3 col-md-6">
                <x-dashboard.stat-tile label="Rejected" value-id="summary-employee-rejected"
                    subtitle="Requests returned with reason." icon="bx bx-x-circle" tone="danger" :loading="true" />
            </div>

            <div class="col-xl-6 col-md-6">
                <x-dashboard.stat-tile label="Active Assignments" value-id="summary-employee-active-assignments"
                    subtitle="Assignments where you are currently marked active." icon="bx bx-user-pin" tone="info"
                    :loading="true" />
            </div>

            <div class="col-xl-6 col-md-6">
                <x-dashboard.stat-tile label="Total Assignments" value-id="summary-employee-total-assignments"
                    subtitle="All assignment records linked to your profile." icon="bx bx-sitemap" tone="secondary"
                    :loading="true" />
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 rounded-4 h-100">
                    <div class="card-header border-0 pb-0 p-4">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Profile & Reporting Chain</h5>
                                <p class="text-secondary small mb-0">Snapshot of your position and leadership levels
                                    above you.</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-3 p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="text-secondary small">Name</div>
                                <div id="profile-full-name" class="fw-semibold text-dark">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary small">Email</div>
                                <div id="profile-email" class="fw-semibold text-dark">-</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-secondary small">Position</div>
                                <div id="profile-position" class="fw-semibold text-dark">-</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-secondary small">Sub-Company</div>
                                <div id="profile-sub-company" class="fw-semibold text-dark">-</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-secondary small">Squad</div>
                                <div id="profile-squad" class="fw-semibold text-dark">-</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="text-secondary small">CEO</div>
                                <div id="profile-ceo" class="fw-semibold text-dark">-</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary small">Direct Leader</div>
                                <div id="profile-leader" class="fw-semibold text-dark">-</div>
                            </div>
                        </div>

                        <div>
                            <div class="text-secondary small mb-2">Positions Above You</div>
                            <div id="profile-upper-positions-empty" class="text-secondary small">No upper positions
                                found yet.</div>
                            <ul id="profile-upper-positions" class="list-group list-group-flush d-none"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
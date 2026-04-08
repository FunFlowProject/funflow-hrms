<x-layouts.app title="Admin Dashboard">
    <div id="admin-dashboard-page" data-stats-url="{{ route('dashboard.stats') }}">
        <x-ui.page-header title="Admin Dashboard"
            description="One place to monitor workforce health, organization structure, and execution capacity." />

        <x-dashboard.hero-panel title="Welcome to {{ config('app.name', 'Funflow HRMS') }}"
            description="Track core people operations in real time and jump directly into priority modules."
            class="mb-4">
            <x-slot:highlights>
                <span class="ui-dashboard-chip">
                    <i class="bx bx-group"></i>
                    <span id="hero-total-employees" class="fw-bold">0</span>
                    <small>Total Employees</small>
                </span>

                <span class="ui-dashboard-chip">
                    <i class="bx bx-loader-circle"></i>
                    <span id="hero-open-onboarding" class="fw-bold">0</span>
                    <small>Open Onboarding</small>
                </span>

                <span class="ui-dashboard-chip">
                    <i class="bx bx-user-pin"></i>
                    <span id="hero-active-assignments" class="fw-bold">0</span>
                    <small>Active Worker Assignments</small>
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
            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Total Employees" value-id="summary-total-employees"
                    subtitle="All employee records in the system." icon="bx bx-group" tone="primary" :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Active Workforce" value-id="summary-active-workforce"
                    subtitle="Joined + onboarding employees." icon="bx bx-user-check" tone="success" :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Pending Intake" value-id="summary-pending-employees"
                    subtitle="Employees waiting for onboarding." icon="bx bx-time-five" tone="warning"
                    :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Active Worker Assignments" value-id="summary-active-assignments"
                    subtitle="Current worker assignments in service." icon="bx bx-user-pin" tone="info"
                    :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Organization Nodes" value-id="summary-organization-nodes"
                    subtitle="Sub-companies plus squads." icon="bx bx-sitemap" tone="secondary" :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Worker Assignment Coverage" value-id="summary-assignment-coverage"
                    subtitle="Worker assignments against total employees." icon="bx bx-pulse" tone="danger" :loading="true" />
            </div>
        </div>

        @php
            $distributionRows = [
                ['key' => 'joined', 'label' => 'Joined', 'tone' => 'success'],
                ['key' => 'pending', 'label' => 'Pending', 'tone' => 'warning'],
                ['key' => 'onboarding', 'label' => 'Onboarding', 'tone' => 'info'],
                ['key' => 'terminated', 'label' => 'Terminated', 'tone' => 'danger'],
            ];
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <x-dashboard.progress-group title="Workforce Distribution"
                    description="Live status breakdown generated from employee records." :rows="$distributionRows"
                    :loading="true" />
            </div>

            <div class="col-xl-5">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <x-dashboard.quick-action-tile title="Employee Directory"
                            description="Open profiles, status updates, and workforce records." icon="bx bx-user-voice"
                            href="{{ route('employees.index') }}" tone="primary" />
                    </div>

                    <div class="col-sm-6">
                        <x-dashboard.quick-action-tile title="Pending Intake"
                            description="Focus on pending employees and onboarding priorities." icon="bx bx-task"
                            href="{{ route('employees.index') }}?search_status=pending" tone="warning" />
                    </div>

                    <div class="col-sm-6">
                        <x-dashboard.quick-action-tile title="Sub-Company Map"
                            description="Manage business units and structural coverage." icon="bx bx-buildings"
                            href="{{ route('sub-companies.index') }}" tone="success" />
                    </div>

                    <div class="col-sm-6">
                        <x-dashboard.quick-action-tile title="Squad Capacity"
                            description="Review squads and worker assignments." icon="bx bx-grid-alt"
                            href="{{ route('squads.index') }}" tone="info" />
                    </div>
                </div>
            </div>
        </div>

        <x-dashboard.activity-placeholder class="mb-2" />
    </div>
</x-layouts.app>
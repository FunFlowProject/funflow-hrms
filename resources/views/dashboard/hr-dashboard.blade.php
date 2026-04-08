<x-layouts.app title="HR Dashboard">
    <div id="hr-dashboard-page" data-stats-url="{{ route('dashboard.stats') }}">
        <x-ui.page-header title="HR Dashboard"
            description="Monitor onboarding flow, service desk queues, and workforce assignment load." />

        <x-dashboard.hero-panel title="HR Operations Pulse"
            description="Use this dashboard to prioritize incoming requests and onboarding bottlenecks." class="mb-4">
            <x-slot:highlights>
                <span class="ui-dashboard-chip">
                    <i class="bx bx-user-plus"></i>
                    <span id="hero-hr-open-onboarding" class="fw-bold">0</span>
                    <small>Open Onboarding</small>
                </span>

                <span class="ui-dashboard-chip">
                    <i class="bx bx-time-five"></i>
                    <span id="hero-hr-unassigned-queue" class="fw-bold">0</span>
                    <small>Unassigned Queue</small>
                </span>

                <span class="ui-dashboard-chip">
                    <i class="bx bx-briefcase-alt"></i>
                    <span id="hero-hr-my-queue" class="fw-bold">0</span>
                    <small>My Active Queue</small>
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
                <x-dashboard.stat-tile label="Pending Intake" value-id="summary-hr-pending-employees"
                    subtitle="Employees waiting for first onboarding action." icon="bx bx-user-plus" tone="warning"
                    :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Open Onboarding" value-id="summary-hr-onboarding-employees"
                    subtitle="Employees currently in onboarding flow." icon="bx bx-loader-circle" tone="info"
                    :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Active Assignments" value-id="summary-hr-active-assignments"
                    subtitle="Current active workforce assignment records." icon="bx bx-user-pin" tone="success"
                    :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Submitted Requests" value-id="summary-hr-submitted-requests"
                    subtitle="New requests waiting for handling." icon="bx bx-send" tone="primary" :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="In Progress Queue" value-id="summary-hr-in-progress-requests"
                    subtitle="Requests currently being fulfilled." icon="bx bx-loader-circle" tone="warning"
                    :loading="true" />
            </div>

            <div class="col-xl-4 col-md-6">
                <x-dashboard.stat-tile label="Unassigned Queue" value-id="summary-hr-unassigned-requests"
                    subtitle="Open requests without a handler." icon="bx bx-task" tone="danger" :loading="true" />
            </div>
        </div>

        @php
            $requestRows = [
                ['key' => 'submitted', 'label' => 'Submitted', 'tone' => 'primary'],
                ['key' => 'in-progress', 'label' => 'In Progress', 'tone' => 'warning'],
                ['key' => 'completed', 'label' => 'Completed', 'tone' => 'success'],
                ['key' => 'rejected', 'label' => 'Rejected', 'tone' => 'danger'],
            ];
        @endphp

        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <x-dashboard.progress-group title="Service Request Queue Distribution"
                    description="Real-time status spread across all service requests." :rows="$requestRows"
                    :loading="true" empty-title="No service requests yet"
                    empty-description="Requests will appear here as soon as employees submit them." />
            </div>

            <div class="col-xl-5">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <x-dashboard.quick-action-tile title="Service Requests"
                            description="Open queue, claim requests, and execute transitions." icon="bx bx-message-dots"
                            href="{{ route('service-requests.index') }}" tone="primary" />
                    </div>

                    <div class="col-sm-6">
                        <x-dashboard.quick-action-tile title="Employee Directory"
                            description="Update employee status and onboarding records." icon="bx bx-group"
                            href="{{ route('employees.index') }}" tone="success" />
                    </div>

                    <div class="col-sm-6">
                        <x-dashboard.quick-action-tile title="Pending Intake"
                            description="Filter employees currently waiting for onboarding." icon="bx bx-task"
                            href="{{ route('employees.index') }}?search_status=pending" tone="warning" />
                    </div>

                    <div class="col-sm-6">
                        <x-dashboard.quick-action-tile title="Service Catalog"
                            description="Manage available request types for employees." icon="bx bx-briefcase-alt-2"
                            href="{{ route('service-catalog.index') }}" tone="info" />
                    </div>
                </div>
            </div>
        </div>

        <x-dashboard.activity-placeholder class="mb-2" title="HR Activity Stream"
            description="Activity stream can be extended with request assignment and onboarding timeline events."
            rows="3" />
    </div>
</x-layouts.app>
<x-layouts.app title="My Learning Objectives">
    <div id="my-objectives-page" 
        data-list-url="{{ route('my-objectives.list') }}"
        data-stats-url="{{ route('my-objectives.stats') }}"
        data-update-progress-url-template="{{ route('my-objectives.update-progress', ['objective' => '__id__']) }}"
        data-complete-url-template="{{ route('my-objectives.complete', ['objective' => '__id__']) }}">

        <x-ui.page-header title="My Learning Objectives"
            description="Track your assigned educational and learning goals.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#my-objectives-search-filters" aria-expanded="false">
                        <i class="bx bx-filter-alt me-1"></i>Filters
                    </button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Total Assigned" value-id="summary-total" value-class="text-primary" :loading="true"
                    border-tone="active" icon-tone="primary">
                    <i class="bx bx-book-bookmark bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Not Started" value-id="summary-not-started" value-class="text-secondary"
                    :loading="true" border-tone="pending" icon-tone="secondary">
                    <i class="bx bx-radio-circle bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="In Progress" value-id="summary-in-progress"
                    value-class="text-info" :loading="true" border-tone="active" icon-tone="info">
                    <i class="bx bx-loader bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Completed" value-id="summary-completed"
                    value-class="text-success" :loading="true" border-tone="active" icon-tone="success">
                    <i class="bx bx-check-shield bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="my-objectives-search-filters" title="Filters"
            description="Filter by name or status.">
            <form id="my-objectives-search-form" class="row g-3">
                <div class="col-md-6">
                    <label for="filter-objective-name" class="form-label fw-bold text-dark">Name</label>
                    <input type="text" id="filter-objective-name" name="search" class="form-control" placeholder="Search objectives..." />
                </div>

                <div class="col-md-6">
                    <label for="filter-objective-status" class="form-label fw-bold text-dark">Status</label>
                    <select id="filter-objective-status" name="status" class="form-select select2-filter">
                        <option value="">All Statuses</option>
                        <option value="not_started">Not Started</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-objective-filters" type="button" class="btn btn-light border px-4 fw-bold">Reset</button>
                    <button id="btn-apply-objective-filters" type="submit" class="btn btn-primary px-4 fw-bold">Apply</button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <!-- Loading Spinner -->
        <div id="objectives-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Cards Container -->
        <div class="row g-4 d-none" id="objectives-grid"></div>
        
        <!-- Empty State -->
        <div id="objectives-empty" class="col-12 py-5 text-center d-none bg-white rounded shadow-sm border mt-4">
            <div class="text-muted mb-3">
                <i class="bx bx-book-blank bx-lg"></i>
            </div>
            <h5 class="fw-bold">No objectives found</h5>
            <p class="text-muted">You do not have any learning objectives matching this criteria.</p>
        </div>

        <x-educational-objectives.progress-modal />
    </div>
</x-layouts.app>

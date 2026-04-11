<x-layouts.app title="My Work Logs">
    <div id="work-logs-page" 
         data-is-my-logs="true"
         data-store-url="{{ route('work-logs.store') }}"
         data-datatable-url="{{ route('work-logs.datatable') }}"
         data-show-url-template="{{ route('work-logs.show', ['workLog' => '__id__']) }}"
         data-update-url-template="{{ route('work-logs.update', ['workLog' => '__id__']) }}"
         data-destroy-url-template="{{ route('work-logs.destroy', ['workLog' => '__id__']) }}">

        <x-ui.page-header title="My Work Logs" 
            description="Manage your individual task history and daily activity logs.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#work-logs-search-filters" aria-expanded="false"
                        aria-controls="work-logs-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>
                    <button type="button" class="btn btn-primary px-4 py-2 fw-bold" data-bs-toggle="modal" 
                        data-bs-target="#workLogFormModal">
                        <i class="bx bx-plus me-1"></i>New Work Log
                    </button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.search-filters-panel id="work-logs-search-filters" title="Search & Filters"
            description="Filter your work history by date.">
            <form id="work-logs-search-form" class="row g-3">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-work-log-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <x-ui.data-table-card title="My Personal Logs" class="mb-5"
            body-class="ui-data-table-card-body px-0 pt-0 pb-3">
            <div class="table-responsive ui-data-table-scroll">
                <table id="work-logs-table" class="table table-hover align-middle w-100">
                    <thead class="table-custom-header">
                        <tr>
                            <th class="border-top-0 border-start-0 rounded-top-start">Main Tasks</th>
                            <th class="border-top-0">Duration</th>
                            <th class="border-top-0">Logged At</th>
                            <th class="border-top-0 border-end-0 rounded-top-end text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0"></tbody>
                </table>
            </div>
        </x-ui.data-table-card>

        <x-work-logs.form-modal />
        <x-work-logs.view-modal />
    </div>
</x-layouts.app>

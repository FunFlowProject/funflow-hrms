<x-layouts.app title="My Documents">
    <div id="my-documents-page" 
        data-list-url="{{ route('my-documents.list') }}"
        data-stats-url="{{ route('my-documents.stats') }}"
        data-mark-viewed-url-template="{{ route('my-documents.mark-viewed', ['document' => '__id__']) }}"
        data-acknowledge-url-template="{{ route('my-documents.acknowledge', ['document' => '__id__']) }}">

        <x-ui.page-header title="My Documents"
            description="Browse, search, and acknowledge company documentation and policies.">
            <x-slot:actions>
                <div class="d-flex flex-wrap justify-content-md-end gap-2">
                    <button type="button" class="btn btn-outline-primary px-4 py-2 fw-bold" data-bs-toggle="collapse"
                        data-bs-target="#my-documents-search-filters" aria-expanded="false"
                        aria-controls="my-documents-search-filters">
                        <i class="bx bx-filter-alt me-1"></i>Search & Filters
                    </button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Total Documents" value-id="summary-total" value-class="text-primary" :loading="true"
                    border-tone="active" icon-tone="primary">
                    <i class="bx bx-collection bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="New" value-id="summary-new" value-class="text-warning"
                    :loading="true" border-tone="pending" icon-tone="warning">
                    <i class="bx bx-bell bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Requires Acknowledgment" value-id="summary-requires-ack"
                    value-class="text-danger" :loading="true" border-tone="inactive" icon-tone="danger">
                    <i class="bx bx-error-circle bx-sm"></i>
                </x-ui.stat-card>
            </div>

            <div class="col-xl-3 col-sm-6">
                <x-ui.stat-card label="Acknowledged" value-id="summary-acknowledged"
                    value-class="text-success" :loading="true" border-tone="active" icon-tone="success">
                    <i class="bx bx-check-circle bx-sm"></i>
                </x-ui.stat-card>
            </div>
        </div>

        <x-ui.search-filters-panel id="my-documents-search-filters" title="Search & Filters"
            description="Search your documents by name or classification.">
            <form id="my-documents-search-form" class="row g-3">
                <div class="col-md-6">
                    <label for="filter-document-name" class="form-label fw-bold text-dark">Name</label>
                    <input type="text" id="filter-document-name" name="search" class="form-control"
                        placeholder="Search by document name" />
                </div>

                <div class="col-md-6">
                    <label for="filter-document-classification" class="form-label fw-bold text-dark">Classification</label>
                    <select id="filter-document-classification" name="classification" class="form-select select2-filter">
                        <option value="">All Classifications</option>
                        <option value="public">Public</option>
                        <option value="internal_use_only">Internal Use Only</option>
                        <option value="confidential">Confidential</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button id="btn-reset-document-filters" type="button" class="btn btn-light border px-4 fw-bold">
                        Reset
                    </button>
                    <button id="btn-apply-document-filters" type="submit" class="btn btn-primary px-4 fw-bold">
                        Apply
                    </button>
                </div>
            </form>
        </x-ui.search-filters-panel>

        <!-- Loading Spinner -->
        <div id="documents-loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <!-- Cards Container -->
        <div class="row g-4 d-none" id="documents-grid">
            <!-- Rendered by JS -->
        </div>
        
        <!-- Empty State -->
        <div id="documents-empty" class="col-12 py-5 text-center d-none bg-white rounded shadow-sm border mt-4">
            <div class="text-muted mb-3">
                <i class="bx bx-file-blank bx-lg"></i>
            </div>
            <h5 class="fw-bold">No documents found</h5>
            <p class="text-muted">You do not have any documents matching this criteria.</p>
        </div>

    </div>
</x-layouts.app>

<x-ui.modal id="workLogViewModal" title="Work Log Details" size="lg" :scrollable="true" body-class="px-4">
    <div class="row g-4 mb-4">
        <div class="col-md-6 text-center text-md-start">
            <span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Employee</span>
            <div id="view-employee-name" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6 text-center text-md-end">
            <span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Total Duration</span>
            <div id="view-total-duration" class="fw-bold text-primary fs-5">-</div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Tasks & Activities</span>
            <div id="view-tasks-list" class="list-group list-group-flush border rounded">
                <!-- Tasks will be injected here -->
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Logged At</span>
            <div id="view-created-at" class="fw-semibold text-dark small">-</div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Close</button>
    </x-slot:footer>
</x-ui.modal>

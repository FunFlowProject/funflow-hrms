<x-ui.modal id="employeeViewModal" title="Service Details" size="lg" :scrollable="true" body-class="px-4">
    <div class="row g-4">
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Service Name</span>
            <div id="view-name" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Category</span>
            <div id="view-category" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Requires
                Justification</span>
            <div id="view-requires-justification" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Status</span>
            <div id="view-active" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Created By</span>
            <div id="view-created-by" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Updated By</span>
            <div id="view-updated-by" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Created At</span>
            <div id="view-created-at" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Updated At</span>
            <div id="view-updated-at" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-12"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Description</span>
            <div id="view-description" class="fw-semibold text-dark">-</div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Close</button>
    </x-slot:footer>
</x-ui.modal>
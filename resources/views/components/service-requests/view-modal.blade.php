<x-ui.modal id="employeeViewModal" title="Service Request Details" size="lg" :scrollable="true" body-class="px-4">
    <div class="row g-4">
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Service</span>
            <div id="view-service-name" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Category</span>
            <div id="view-service-category" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Requester</span>
            <div id="view-requester" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Status</span>
            <div id="view-status" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Handled By</span>
            <div id="view-handled-by" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Acted
                At</span>
            <div id="view-acted-at" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Created At</span>
            <div id="view-created-at" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Updated At</span>
            <div id="view-updated-at" class="fw-semibold text-dark">-</div>
        </div>

        <div class="col-12">
            <span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Justification</span>
            <div id="view-justification" class="fw-semibold text-dark">-</div>
        </div>

        <div class="col-12">
            <span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Fulfillment Note</span>
            <div id="view-fulfillment-note" class="fw-semibold text-dark">-</div>
        </div>

        <div class="col-12">
            <span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Rejection Reason</span>
            <div id="view-rejection-reason" class="fw-semibold text-dark">-</div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Close</button>
    </x-slot:footer>
</x-ui.modal>
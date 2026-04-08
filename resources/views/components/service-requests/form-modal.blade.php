<x-ui.modal id="employeeFormModal" size="lg" :scrollable="true" body-class="px-4 pt-0">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark" id="service-request-modal-title">New Service Request</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <form id="service-request-form">
        @csrf
        <input type="hidden" id="service_request_id" value="" />

        <div id="service-request-form-errors" class="alert alert-danger d-none rounded-3 fw-medium"></div>

        <div class="row g-3">
            <div class="col-12">
                <label for="form-service-request-service-catalog-item"
                    class="form-label fw-bold text-dark">Service</label>
                <select id="form-service-request-service-catalog-item" name="service_catalog_item_id"
                    class="form-select select2-init" required>
                    <option value="">Select Service</option>
                </select>
            </div>

            <div class="col-12">
                <label for="form-service-request-justification" class="form-label fw-bold text-dark">
                    Justification
                    <span id="justification-required-indicator"
                        class="badge bg-warning text-dark ms-1 d-none">Required</span>
                </label>
                <textarea id="form-service-request-justification" name="justification" class="form-control" rows="5"
                    maxlength="3000" placeholder="Provide context for your request"></textarea>
                <small class="text-muted">Justification is required only for services configured with mandatory
                    justification.</small>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="service-request-form" class="btn btn-primary rounded-pill px-4 fw-bold"
            id="service-request-save-button">Submit Request</button>
    </x-slot:footer>
</x-ui.modal>
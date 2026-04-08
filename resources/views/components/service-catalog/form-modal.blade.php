@php
    $activeOptions = \App\Enums\ActiveStatus::options();
@endphp

<x-ui.modal id="employeeFormModal" size="lg" :scrollable="true" body-class="px-4 pt-0">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark" id="service-catalog-modal-title">Create Service</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <form id="service-catalog-form">
        @csrf
        <input type="hidden" id="service_catalog_id" value="" />

        <div id="service-catalog-form-errors" class="alert alert-danger d-none rounded-3 fw-medium"></div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="form-service-catalog-name" class="form-label fw-bold text-dark">Service Name</label>
                <input type="text" id="form-service-catalog-name" name="name" class="form-control" required />
            </div>

            <div class="col-md-6">
                <label for="form-service-catalog-category" class="form-label fw-bold text-dark">Category</label>
                <input type="text" id="form-service-catalog-category" name="category" class="form-control" required />
            </div>

            <div class="col-md-6">
                <label for="form-service-catalog-active" class="form-label fw-bold text-dark">Status</label>
                <select id="form-service-catalog-active" name="active" class="form-select select2-init" required>
                    <option value="">Select Status</option>
                    @foreach ($activeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="form-service-catalog-requires-justification" class="form-label fw-bold text-dark">Requires
                    Justification</label>
                <select id="form-service-catalog-requires-justification" name="requires_justification"
                    class="form-select select2-init" required>
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="col-12">
                <label for="form-service-catalog-description" class="form-label fw-bold text-dark">Description</label>
                <textarea id="form-service-catalog-description" name="description" class="form-control" rows="4"
                    maxlength="3000" placeholder="Write a service description (optional)"></textarea>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="service-catalog-form" class="btn btn-primary rounded-pill px-4 fw-bold"
            id="service-catalog-save-button">Save Changes</button>
    </x-slot:footer>
</x-ui.modal>
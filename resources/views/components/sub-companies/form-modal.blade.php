@php
    $activeOptions = \App\Enums\ActiveStatus::options();
@endphp

<x-ui.modal id="employeeFormModal" size="lg" :scrollable="true" body-class="px-4 pt-0">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark" id="sub-company-modal-title">Create Sub-Company</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <form id="sub-company-form">
        @csrf
        <input type="hidden" id="sub_company_id" value="" />

        <div id="sub-company-form-errors" class="alert alert-danger d-none rounded-3 fw-medium"></div>

        <div class="row g-3">
            <div class="col-md-8">
                <label for="form-sub-company-name" class="form-label fw-bold text-dark">Name</label>
                <input type="text" id="form-sub-company-name" name="name" class="form-control" required />
            </div>

            <div class="col-md-4">
                <label for="form-active" class="form-label fw-bold text-dark">Status</label>
                <select id="form-active" name="active" class="form-select select2-init" required>
                    <option value="">Select Status</option>
                    @foreach ($activeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label for="form-sub-company-description" class="form-label fw-bold text-dark">Description</label>
                <textarea id="form-sub-company-description" name="description" class="form-control" rows="4"
                    maxlength="255" placeholder="Write a short description (optional)"></textarea>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="sub-company-form" class="btn btn-primary rounded-pill px-4 fw-bold"
            id="sub-company-save-button">Save Changes</button>
    </x-slot:footer>
</x-ui.modal>

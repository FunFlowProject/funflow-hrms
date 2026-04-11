@php
    $activeOptions = \App\Enums\ActiveStatus::options();
@endphp

<x-ui.modal id="squadFormModal" size="lg" :scrollable="true" body-class="px-4 pt-0">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark" id="squad-modal-title">Create Squad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <form id="squad-form">
        @csrf
        <input type="hidden" id="squad_id" value="" />

        <div id="squad-form-errors" class="alert alert-danger d-none rounded-3 fw-medium"></div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="form-sub-company-id" class="form-label fw-bold text-dark">Sub-Company</label>
                <select id="form-sub-company-id" name="sub_company_id" class="form-select select2-init" required>
                    <option value="">Select Sub-Company</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="form-squad-name" class="form-label fw-bold text-dark">Squad Name</label>
                <input type="text" id="form-squad-name" name="name" class="form-control" required />
            </div>

            <div class="col-md-6">
                <label for="form-active" class="form-label fw-bold text-dark">Status</label>
                <select id="form-active" name="active" class="form-select select2-init" required>
                    <option value="">Select Status</option>
                    @foreach ($activeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="squad-form" class="btn btn-primary rounded-pill px-4 fw-bold"
            id="squad-save-button">Save Changes</button>
    </x-slot:footer>
</x-ui.modal>

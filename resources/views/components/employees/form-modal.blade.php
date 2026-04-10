<x-ui.modal id="employeeFormModal" size="lg" :scrollable="true" body-class="px-4 pt-0">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark" id="employee-modal-title">Create Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <form id="employee-form">
        @csrf
        <input type="hidden" id="employee_id" value="" />

        <div id="employee-form-errors" class="alert alert-danger d-none rounded-3 fw-medium"></div>
        <div class="row g-3">
            <div class="col-md-12">
                <label for="form-full-name" class="form-label fw-bold text-dark">Full Name</label>
                <input type="text" id="form-full-name" name="full_name" class="form-control" required />
            </div>
            <div class="col-md-6">
                <label for="form-email" class="form-label fw-bold text-dark">Email</label>
                <input type="email" id="form-email" name="email" class="form-control" required />
            </div>
            <div class="col-md-6">
                <label for="form-phone-number" class="form-label fw-bold text-dark">Phone Number</label>
                <input type="text" id="form-phone-number" name="phone_number" class="form-control" required />
            </div>

            <div class="col-md-3">
                <label for="form-date-of-birth" class="form-label fw-bold text-dark">Date of Birth</label>
                <input type="text" id="form-date-of-birth" name="date_of_birth" class="form-control flatpickr-date"
                    placeholder="YYYY-MM-DD" required />
            </div>
            <div class="col-md-3">
                <label for="form-hire-date" class="form-label fw-bold text-dark">Hire Date</label>
                <input type="text" id="form-hire-date" name="hire_date" class="form-control flatpickr-date"
                    placeholder="YYYY-MM-DD" required />
            </div>

            <div class="col-md-6">
                <label for="form-contract-type" class="form-label fw-bold text-dark">Contract Type</label>
                <select id="form-contract-type" name="contract_type" class="form-select select2-init" required>
                    <option value="">Select Contract</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="form-system-role" class="form-label fw-bold text-dark">System Role</label>
                <select id="form-system-role" name="system_role" class="form-select select2-init" required>
                    <option value="">Select Role</option>
                </select>
            </div>

            <div class="col-12 d-none" id="primary-assignment-fields">
                <div class="col-12 mt-4">
                    <h6 class="mb-2 fw-bolder text-secondary text-uppercase small tracking-wide">Primary Worker Assignment
                    </h6>
                    <hr class="mt-0 mb-3 border-secondary opacity-25">
                </div>

                <div class="row g-3">
                    <div class="col-md-4 mt-0">
                        <label for="form-sub-company" class="form-label fw-bold text-dark">Sub-Company</label>
                        <select id="form-sub-company" name="assignments[0][sub_company_id]"
                            class="form-select select2-init">
                            <option value="">Select Sub-Company</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-0">
                        <label for="form-squad" class="form-label fw-bold text-dark">Squad</label>
                        <select id="form-squad" name="assignments[0][squad_id]" class="form-select select2-init">
                            <option value="">No Squad</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-0">
                        <label for="form-hierarchy" class="form-label fw-bold text-dark">Hierarchy</label>
                        <select id="form-hierarchy" name="assignments[0][hierarchy_id]" class="form-select select2-init">
                            <option value="">Select Hierarchy</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4">
                <div class="alert alert-info mb-0">
                    Username and temporary password are generated automatically by the system and sent to the employee
                    email.
                </div>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="employee-form" class="btn btn-primary rounded-pill px-4 fw-bold"
            id="employee-save-button">Save Changes</button>
    </x-slot:footer>
</x-ui.modal>
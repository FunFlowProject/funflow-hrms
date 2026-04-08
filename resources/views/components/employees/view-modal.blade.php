<x-ui.modal id="employeeViewModal" title="Employee Details" size="lg" :scrollable="true" body-class="px-4">
    <div class="row g-4">
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Username</span>
            <div id="view-username" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Full
                Name</span>
            <div id="view-name" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Email</span>
            <div id="view-email" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Phone</span>
            <div id="view-phone" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-4"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Contract</span>
            <div id="view-contract" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-4"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">System Role</span>
            <div id="view-role" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-4"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Status</span>
            <div id="view-status" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Date of Birth</span>
            <div id="view-dob" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-md-6"><span
                class="d-block text-secondary small fw-bold mb-1 text-uppercase tracking-wide">Hire Date</span>
            <div id="view-hire-date" class="fw-semibold text-dark">-</div>
        </div>
        <div class="col-12 border-top border-secondary opacity-75 pt-3"><span
            class="d-block text-secondary small fw-bold mb-2 text-uppercase tracking-wide">Worker Assignments</span>
            <div id="view-assignments" class="fw-semibold text-dark">-</div>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-light border rounded-pill px-4 fw-bold text-dark"
            data-bs-dismiss="modal">Close</button>
    </x-slot:footer>
</x-ui.modal>

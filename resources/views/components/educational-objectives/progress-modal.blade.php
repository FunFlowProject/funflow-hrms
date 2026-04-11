<x-ui.modal id="objectiveProgressModal" size="xl" :scrollable="true" :centered="true">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark">Objective Progress Breakdown</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <div class="mb-4">
        <h6 class="fw-bold mb-1" id="progress-objective-name">Objective Name</h6>
        <p class="text-muted small mb-0">Detailed view of achievement status for all assigned employees.</p>
    </div>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="fw-bold text-dark px-4 py-3">Employee</th>
                    <th class="fw-bold text-dark py-3">Status</th>
                    <th class="fw-bold text-dark py-3">Progress Notes</th>
                    <th class="fw-bold text-dark px-4 py-3 text-end">Completion Date</th>
                </tr>
            </thead>
            <tbody id="progress-table-body">
                <!-- Dynamically populated -->
            </tbody>
        </table>
    </div>

    <div id="progress-loading-placeholder" class="d-none text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2 text-muted">Fetching progress data...</p>
    </div>

    <div id="progress-empty-placeholder" class="d-none text-center py-5">
        <i class="bx bx-info-circle fs-1 text-muted"></i>
        <p class="mt-2 text-muted">No employees are currently assigned to this objective.</p>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Close</button>
    </x-slot:footer>
</x-ui.modal>

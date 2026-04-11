<x-ui.modal id="documentStatusModal" size="lg" :scrollable="true" :centered="true">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark" id="document-status-modal-title">Document Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <div class="table-responsive">
        <table class="table table-hover align-middle w-100" id="document-status-table">
            <thead class="table-custom-header">
                <tr>
                    <th class="border-top-0 border-start-0 rounded-top-start">Employee Name</th>
                    <th class="border-top-0">Status</th>
                    <th class="border-top-0 border-end-0 rounded-top-end">Acknowledged At</th>
                </tr>
            </thead>
            <tbody class="border-top-0" id="document-status-tbody">
                <tr>
                    <td colspan="3" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-label-secondary border rounded-pill px-4 fw-bold"
            data-bs-dismiss="modal">Close</button>
    </x-slot:footer>
</x-ui.modal>

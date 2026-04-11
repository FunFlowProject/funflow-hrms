<x-ui.modal id="workLogFormModal" title="Log Work" size="lg">
    <form id="work-log-form">
        <input type="hidden" id="work_log_id" name="id">
        
        <div id="work-log-form-errors" class="alert alert-danger d-none mb-3"></div>

        <div class="row g-3 mb-4 align-items-center">
            <div class="col-12 text-center">
                <div class="total-duration-display border rounded d-inline-block px-4 py-2 bg-light">
                    <span class="text-muted small text-uppercase fw-bold me-2">Total Duration:</span>
                    <span id="display-total-duration" class="fw-bold text-primary fs-5">0h 0m</span>
                </div>
            </div>
        </div>

        <div class="tasks-section mt-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold"><i class="bx bx-list-check me-2"></i>Tasks & Activities</h6>
                <button type="button" id="btn-add-task-row" class="btn btn-xs btn-outline-primary">
                    <i class="bx bx-plus me-1"></i>Add Task
                </button>
            </div>

            <div id="tasks-container" class="tasks-container">
                <!-- Task rows will be injected here by JS -->
            </div>
            
            <div id="empty-tasks-placeholder" class="text-center py-4 border rounded dashed bg-light d-none">
                <p class="text-muted mb-0">No tasks added. Click "Add Task" to start logging.</p>
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="button" class="btn btn-label-secondary me-2" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4">
                <i class="bx bx-save me-1"></i>Save Work Log
            </button>
        </div>
    </form>

    {{-- Task Row Template --}}
    <template id="task-row-template">
        <div class="task-row card mb-2 border shadow-none bg-lighter">
            <div class="card-body p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <input type="text" name="tasks[__INDEX__][name]" class="form-control form-control-sm task-name-input" 
                            placeholder="What did you do?" required>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm input-group-merge">
                            <input type="number" name="tasks[__INDEX__][duration_minutes]" 
                                class="form-control form-control-sm task-duration-input" 
                                placeholder="Min" min="1" required>
                            <span class="input-group-text">Min</span>
                        </div>
                    </div>
                    <div class="col-md-2 text-end">
                        <div class="form-check form-check-inline me-2 pt-1">
                            <input class="form-check-input task-done-checkbox" type="checkbox" name="tasks[__INDEX__][done]" value="1" checked>
                            <label class="form-check-label px-0 mr-0">Done</label>
                        </div>
                        <button type="button" class="btn btn-icon btn-sm btn-label-danger btn-remove-task-row">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</x-ui.modal>

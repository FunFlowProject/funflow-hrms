<x-ui.modal id="objectiveFormModal" size="lg" :scrollable="true" :centered="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark" id="objective-modal-title">Assign Educational Objective</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <form id="objective-form" class="needs-validation" novalidate>
        <div id="objective-form-errors" class="alert alert-danger d-none mb-3" role="alert"></div>

        <div class="row g-3">
            <div class="col-md-12">
                <label for="form-objective-name" class="form-label fw-bold text-dark">Objective Name <span
                        class="text-danger">*</span></label>
                <input type="text" id="form-objective-name" name="name" class="form-control" required
                    placeholder="e.g., Q3 Security Compliance Training">
            </div>

            <div class="col-md-12">
                <label for="form-objective-description" class="form-label fw-bold text-dark">Description</label>
                <textarea id="form-objective-description" name="description" class="form-control" rows="3"
                    placeholder="Provide details about the learning objective..."></textarea>
            </div>

            <div class="col-md-6">
                <label for="form-objective-target-date" class="form-label fw-bold text-dark">Target Date</label>
                <input type="date" id="form-objective-target-date" name="target_date" class="form-control">
            </div>

            <div class="col-md-6">
                <label for="form-objective-priority" class="form-label fw-bold text-dark">Priority <span
                        class="text-danger">*</span></label>
                <select id="form-objective-priority" name="priority" class="form-select select2-init" required>
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="form-objective-scope-type" class="form-label fw-bold text-dark">Assign To Scope <span
                        class="text-danger">*</span></label>
                <select id="form-objective-scope-type" name="scope_type" class="form-select select2-init" required>
                    @if (auth()->user()->can('educational-objectives.manage-all'))
                        <option value="company" selected>Company-wide</option>
                        <option value="sub_company">Specific Sub-Company</option>
                    @endif
                    <option value="squad"
                        {{ !auth()->user()->can('educational-objectives.manage-all') ? 'selected' : '' }}>Specific Squad
                    </option>
                    <option value="individual">Individual Employee</option>
                </select>
            </div>

            <div class="col-md-6 d-none" id="scope-id-container">
                <label for="form-objective-scope-id" class="form-label fw-bold text-dark">Select Target <span
                        class="text-danger">*</span></label>
                <select id="form-objective-scope-id" name="scope_id" class="form-select select2-init"
                    style="width: 100%;">
                    <option value=""></option>
                </select>
                <small class="text-muted d-none" id="manager-squad-notice">Only subordinate squad members will be
                    targeted.</small>
            </div>

            <div class="col-md-12">
                <label for="form-objective-upload-type" class="form-label fw-bold text-dark">Resource Materials</label>
                <select id="form-objective-upload-type" name="upload_type" class="form-select select2-init">
                    <option value="" selected>No attachment</option>
                    <option value="file">File Upload</option>
                    <option value="url">External URL</option>
                </select>
            </div>

            <div class="col-md-12 d-none" id="file-upload-container">
                <label for="form-objective-file" class="form-label fw-bold text-dark">Upload File</label>
                <input type="file" id="form-objective-file" name="file" class="form-control">
            </div>

            <div class="col-md-12 d-none" id="file-url-container">
                <label for="form-objective-file-url" class="form-label fw-bold text-dark">Resource URL</label>
                <input type="url" id="form-objective-file-url" name="attachment_url" class="form-control"
                    placeholder="https://example.com/course">
            </div>

            <div class="col-12 mt-4">
                <div
                    class="form-check form-switch bg-light border rounded px-4 py-3 d-flex align-items-center justify-content-between">
                    <label class="form-check-label fw-bold text-dark mb-0 form-label mb-0" for="form-objective-mandatory">
                        Mandatory Objective
                        <small class="d-block text-muted fw-normal mt-1">If enabled, this learning goal is required
                            tracking.</small>
                    </label>
                    <input class="form-check-input fs-4 m-0" type="checkbox" id="form-objective-mandatory"
                        name="mandatory" value="1">
                </div>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-label-secondary border rounded-pill px-4 fw-bold"
            data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="objective-form" class="btn btn-primary rounded-pill px-4 fw-bold">Assign
            Objective</button>
    </x-slot:footer>
</x-ui.modal>

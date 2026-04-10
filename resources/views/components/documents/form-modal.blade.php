<x-ui.modal id="documentFormModal" size="lg" :scrollable="true" :centered="true" data-bs-backdrop="static"
    data-bs-keyboard="false">
    <x-slot:header>
        <h5 class="modal-title fw-bolder text-dark" id="document-modal-title">Create Document</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </x-slot:header>

    <form id="document-form" class="needs-validation" novalidate>
        <input type="hidden" id="document_id" name="id">

        <div id="document-form-errors" class="alert alert-danger d-none mb-3" role="alert"></div>

        <div class="row g-3">
            <div class="col-md-12">
                <label for="form-document-name" class="form-label fw-bold text-dark">Document Name <span
                        class="text-danger">*</span></label>
                <input type="text" id="form-document-name" name="name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label for="form-document-classification" class="form-label fw-bold text-dark">Classification <span
                        class="text-danger">*</span></label>
                <select id="form-document-classification" name="classification" class="form-select select2-init"
                    required>
                    <option value="public">Public</option>
                    <option value="internal_use_only" selected>Internal Use Only</option>
                    <option value="confidential">Confidential</option>
                </select>
            </div>

            <div class="col-md-6">
                <label for="form-document-scope-type" class="form-label fw-bold text-dark">Scope <span
                        class="text-danger">*</span></label>
                <select id="form-document-scope-type" name="scope_type" class="form-select select2-init" required>
                    <option value="company" selected>Company-wide</option>
                    <option value="sub_company">Specific Sub-Company</option>
                    <option value="squad">Specific Squad</option>
                </select>
            </div>

            <div class="col-md-12 d-none" id="scope-id-container">
                <label for="form-document-scope-id" class="form-label fw-bold text-dark">Select Target <span
                        class="text-danger">*</span></label>
                <select id="form-document-scope-id" name="scope_id" class="form-select select2-init"
                    style="width: 100%;">
                    <option value=""></option>
                </select>
            </div>

            <div class="col-md-12">
                <label for="form-document-upload-type" class="form-label fw-bold text-dark">Upload Type <span
                        class="text-danger">*</span></label>
                <select id="form-document-upload-type" name="upload_type" class="form-select select2-init" required>
                    <option value="file" selected>File Upload</option>
                    <option value="url">External URL</option>
                    <option value="keep" class="edit-only d-none">Keep Existing File/URL</option>
                </select>
            </div>

            <div class="col-md-12" id="file-upload-container">
                <label for="form-document-file" class="form-label fw-bold text-dark">File <span
                        class="text-danger">*</span></label>
                <input type="file" id="form-document-file" name="file" class="form-control">
            </div>

            <div class="col-md-12 d-none" id="file-url-container">
                <label for="form-document-file-url" class="form-label fw-bold text-dark">URL <span
                        class="text-danger">*</span></label>
                <input type="url" id="form-document-file-url" name="file_url" class="form-control"
                    placeholder="https://example.com/doc">
            </div>

            <div class="col-12 mt-4">
                <div
                    class="form-check form-switch bg-light border rounded px-4 py-3 d-flex align-items-center justify-content-between">
                    <label class="form-check-label fw-bold text-dark mb-0 form-label mb-0"
                        for="form-document-requires-ack">
                        Requires Acknowledgment
                        <small class="d-block text-muted fw-normal mt-1">If enabled, employees must explicitly
                            acknowledge they have read this document.</small>
                    </label>
                    <input class="form-check-input fs-4 m-0" type="checkbox" id="form-document-requires-ack"
                        name="requires_acknowledgment" value="1">
                </div>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-label-secondary border rounded-pill px-4 fw-bold"
            data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="document-form" class="btn btn-primary rounded-pill px-4 fw-bold">Save
            changes</button>
    </x-slot:footer>
</x-ui.modal>

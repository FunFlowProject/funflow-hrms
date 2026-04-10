<x-ui.modal id="objectiveProgressModal" title="Update Progress" :centered="true" data-bs-backdrop="static">
    <form id="objective-progress-form">
        <input type="hidden" id="progress_objective_id" name="id">
        <div id="objective-progress-form-errors" class="alert alert-danger d-none mb-3" role="alert"></div>

        <div class="mb-3">
            <label for="form-objective-progress-notes" class="form-label fw-bold text-dark">Progress Notes</label>
            <textarea id="form-objective-progress-notes" name="progress_notes" class="form-control" rows="4"
                placeholder="Briefly describe the progress you have made so far..."></textarea>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-label-secondary border rounded-pill px-4 fw-bold"
            data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="objective-progress-form" class="btn btn-primary rounded-pill px-4 fw-bold">Save
            Notes</button>
    </x-slot:footer>
</x-ui.modal>

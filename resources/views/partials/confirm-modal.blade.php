{{-- Global Bootstrap confirmation modal (replaces browser confirm()) --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title fw-semibold" id="confirmModalLabel">
                    <i class="fa fa-exclamation-triangle me-2" aria-hidden="true"></i>
                    Bitte bestätigen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <div id="confirmModalMessage" class="text-secondary"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <button type="button" class="btn btn-danger" id="confirmModalConfirmBtn">OK</button>
            </div>
        </div>
    </div>
</div>

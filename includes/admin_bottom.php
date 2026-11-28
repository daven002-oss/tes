    </main>
</div>

<?php
// Confirmation pop-up shared by every Delete button (see js/main.js: forms with data-confirm).
?>
<dialog id="confirm-dialog" class="modal modal-small" aria-labelledby="confirm-title">
    <h2 id="confirm-title">Are you sure?</h2>
    <p id="confirm-text"></p>
    <div class="modal-actions">
        <button type="button" class="btn-ghost" data-confirm-no>No, cancel</button>
        <button type="button" class="btn-danger" data-confirm-yes>Yes, delete</button>
    </div>
</dialog>

<?php require __DIR__ . '/footer.php'; ?>

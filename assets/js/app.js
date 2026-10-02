// Simple helpers
document.addEventListener('DOMContentLoaded', () => {
    // Confirm deletes
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', e => {
            if (!confirm(el.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });
});

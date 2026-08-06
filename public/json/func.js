function loading(selector, status, message) {
    const el = typeof selector === 'string' ? $(selector) : $(selector);
    el.find('.btn-text').html(message);
    el.prop('disabled', status);
}

function showToast(message, type = 'success') {
    if (!message) return;
    const container = document.querySelector('.js-toast-container');
    if (!container) {
        console.warn('Toast container introuvable');
        return;
    }

    const duplicate = [...container.querySelectorAll('.toast__message')]
        .find(el => el.textContent.trim() === message.trim());
    if (duplicate) return;

    const durations = { success: 3000, info: 4000, warning: 5000, error: 7000 };
    const duration = durations[type] || 4000;

    const icons = {
        success: '<svg class="toast__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        error: '<svg class="toast__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        warning: '<svg class="toast__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
        info: '<svg class="toast__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
    };

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');

    toast.innerHTML = `
        <div class="toast__content">
            ${icons[type] || icons.info}
            <span class="toast__message">${message}</span>
        </div>
        <button type="button" class="toast__close" aria-label="Fermer">&times;</button>
        <div class="toast__progress"></div>
    `;

    toast.style?.setProperty('--toast-duration', `${duration}ms`);
    container.appendChild(toast);
    requestAnimationFrame(() => { toast.classList.add('toast--show'); });

    const removeToast = () => {
        toast.classList.remove('toast--show');
        setTimeout(() => { toast.remove(); }, 300);
    };

    toast.querySelector('.toast__close').addEventListener('click', removeToast);
    setTimeout(removeToast, duration);
}

function showConfirm(message, callback) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay active';
    modal.innerHTML = `
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">Confirmation</h3>
            </div>
            <div class="modal-body">
                <p>${message}</p>
            </div>
            <div class="modal-footer" style="justify-content: flex-end;">
                <button class="btn btn-sm btn-secondary" onclick="this.closest('.modal-overlay').remove()">Annuler</button>
                <button class="btn btn-sm btn-primary" id="confirmBtn">Confirmer</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('confirmBtn').onclick = function() {
        callback();
        modal.remove();
    };
    modal.onclick = function(e) {
        if (e.target === modal) modal.remove();
    };
}

function loading(selector, status, message) {
    const el = typeof selector === 'string' ? $(selector) : $(selector);
    if (el.find('.btn-text').length) {
        el.find('.btn-text').html(message);
    } else {
        el.html(message);
    }
    el.prop('disabled', status);
}

function showToast(message, type = 'success') {
    if (!message) return;
    let container = document.querySelector('.js-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'js-toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 999999; display: flex; flex-direction: column; gap: 10px; max-width: 380px; width: 100%;';
        document.body.appendChild(container);
    }

    const duplicate = [...container.querySelectorAll('.toast__message')]
        .find(el => el.textContent.trim() === message.trim());
    if (duplicate) return;

    const durations = { success: 3000, info: 4000, warning: 5000, error: 7000 };
    const duration = durations[type] || 4000;

    const icons = {
        success: '<svg class="toast__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;color:#10B981;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        error: '<svg class="toast__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;color:#EF4444;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        warning: '<svg class="toast__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;color:#F59E0B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
        info: '<svg class="toast__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:20px;height:20px;color:#3B82F6;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
    };

    const toast = document.createElement('div');
    toast.className = `toast toast--${type}`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'polite');
    toast.style.cssText = 'background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 14px 16px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; gap: 12px; animation: slideIn 0.2s ease;';

    toast.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
            ${icons[type] || icons.info}
            <span class="toast__message" style="font-size: 13px; font-weight: 600; color: #1E293B;">${message}</span>
        </div>
        <button type="button" class="toast__close" aria-label="Fermer" style="background: none; border: none; font-size: 18px; color: #94A3B8; cursor: pointer; line-height: 1;">&times;</button>
    `;

    container.appendChild(toast);

    const removeToast = () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        toast.style.transition = 'all 0.2s ease';
        setTimeout(() => { toast.remove(); }, 200);
    };

    toast.querySelector('.toast__close').addEventListener('click', removeToast);
    setTimeout(removeToast, duration);
}

function showConfirm(message, callback, title = 'Confirmation', confirmText = 'Confirmer', isDanger = false) {
    // Supprimer tout ancien modal de confirmation ouvert
    $('.custom-confirm-overlay').remove();

    const overlay = document.createElement('div');
    overlay.className = 'custom-confirm-overlay';
    overlay.style.cssText = 'position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); z-index: 999999; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 16px; animation: fadeIn 0.15s ease;';

    const btnColor = isDanger ? 'background: #DC2626; color: #FFFFFF;' : 'background: #2563EB; color: #FFFFFF;';

    overlay.innerHTML = `
        <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 420px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); overflow: hidden; animation: popIn 0.15s ease;">
            <div style="padding: 18px 20px; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between;">
                <h3 style="margin: 0; font-size: 15px; font-weight: 700; color: #1E293B; display: flex; align-items: center; gap: 8px;">
                    <i class="fa ${isDanger ? 'fa-exclamation-triangle' : 'fa-question-circle'}" style="color: ${isDanger ? '#DC2626' : '#2563EB'};"></i> ${title}
                </h3>
                <button type="button" class="btn-close-confirm" style="background: none; border: none; font-size: 18px; color: #94A3B8; cursor: pointer; line-height: 1;">&times;</button>
            </div>
            <div style="padding: 20px; color: #334155; font-size: 14px; line-height: 1.5;">
                ${message}
            </div>
            <div style="padding: 14px 20px; background: #F8FAFC; border-top: 1px solid #E2E8F0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-sm btn-secondary btn-cancel-confirm" style="padding: 8px 14px; border-radius: 8px; font-weight: 600; cursor: pointer;">Annuler</button>
                <button type="button" class="btn btn-sm btn-action-ok" style="padding: 8px 16px; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; ${btnColor}">${confirmText}</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    const closeOverlay = () => {
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.15s ease';
        setTimeout(() => { overlay.remove(); }, 150);
    };

    overlay.querySelector('.btn-close-confirm').addEventListener('click', closeOverlay);
    overlay.querySelector('.btn-cancel-confirm').addEventListener('click', closeOverlay);
    overlay.querySelector('.btn-action-ok').addEventListener('click', () => {
        closeOverlay();
        if (typeof callback === 'function') callback();
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeOverlay();
    });
}

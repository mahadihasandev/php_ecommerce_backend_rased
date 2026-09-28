/** Show feedback while the browser performs ordinary server-rendered navigation. */
export function initAdminLoading() {
    const overlay = document.getElementById('admin-loading');
    if (!overlay) return;

    const main = document.getElementById('admin-main-scroll');
    const progress = document.getElementById('admin-top-progress');
    const status = overlay.querySelector('[data-loading-status]');
    const recovery = overlay.querySelector('[data-loading-recovery]');
    const views = overlay.querySelectorAll('[data-skeleton-view]');
    let recoveryTimer;
    let showTimer;

    const adminURL = (value) => {
        const url = new URL(value, window.location.href);
        return url.origin === window.location.origin && /^\/admin(?:\/|$)/.test(url.pathname) ? url : null;
    };

    const position = () => {
        const rect = main?.getBoundingClientRect();
        Object.assign(overlay.style, {
            top: `${rect?.top ?? 0}px`,
            left: `${rect?.left ?? 0}px`,
            width: `${rect?.width ?? window.innerWidth}px`,
            height: `${rect?.height ?? window.innerHeight}px`,
        });
    };

    const reset = () => {
        clearTimeout(recoveryTimer);
        clearTimeout(showTimer);
        overlay.hidden = true;
        recovery.hidden = true;
        progress?.classList.remove('is-loading');
        if (main) {
            main.inert = false;
            main.removeAttribute('aria-busy');
        }
    };

    const show = (url, submitting = false) => {
        const path = url.pathname.replace(/\/$/, '');
        const parts = path.split('/').filter(Boolean);
        const type = parts.length === 1 || /\/(login|register|logout|purge-cache)$/.test(path)
            ? 'dashboard' : parts.length > 2 ? 'form' : 'table';
        const section = parts[1] || 'dashboard';
        views.forEach((view) => { view.hidden = view.dataset.skeletonView !== type; });
        const submissionStatus = {
            login: 'Signing in…',
            register: 'Creating your account…',
            logout: 'Signing out…',
            'purge-cache': 'Refreshing the dashboard cache…',
        };
        status.textContent = submitting
            ? (submissionStatus[section] || 'Please wait while we save your changes…')
            : `Loading ${type === 'dashboard' ? 'dashboard' : section}…`;
        position();
        overlay.scrollTop = 0;
        overlay.hidden = false;
        recovery.hidden = true;
        progress?.classList.add('is-loading');
        if (main) {
            main.inert = true;
            main.setAttribute('aria-busy', 'true');
        }
        window.dispatchEvent(new Event('admin-navigating'));
        clearTimeout(recoveryTimer);
        recoveryTimer = setTimeout(() => { recovery.hidden = false; }, 15000);
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href]');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey
            || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
        const url = adminURL(link.href);
        if (!url || ((url.hash || link.getAttribute('href').includes('#')) && url.pathname === location.pathname && url.search === location.search)) return;
        // Allow existing handlers (including preventDefault) to finish first.
        clearTimeout(showTimer);
        showTimer = setTimeout(() => { if (!event.defaultPrevented) show(url); }, 0);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        const submitter = event.submitter;
        const target = submitter?.getAttribute('formtarget') ?? form.target;
        const method = submitter?.getAttribute('formmethod') ?? form.method;
        if ((target && target !== '_self') || method.toLowerCase() === 'dialog') return;
        const url = adminURL(submitter?.getAttribute('formaction') ?? form.action);
        if (!url) return;
        // Native validation and existing delete confirmations must remain in control.
        clearTimeout(showTimer);
        showTimer = setTimeout(() => { if (!event.defaultPrevented) show(url, method.toLowerCase() !== 'get'); }, 0);
    });

    overlay.querySelector('[data-loading-cancel]').addEventListener('click', () => {
        window.stop();
        reset();
    });
    window.addEventListener('resize', () => { if (!overlay.hidden) position(); });
    window.addEventListener('pagehide', () => { clearTimeout(showTimer); clearTimeout(recoveryTimer); });
    window.addEventListener('pageshow', reset); // Restore an interactive page from the back/forward cache.
    reset();
}

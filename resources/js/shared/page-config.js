export function initPageConfigs() {
    document.querySelectorAll('[data-page-config]').forEach((el) => {
        const key = el.dataset.pageConfig;
        if (!key) return;
        try {
            window[key] = JSON.parse(el.dataset.config || '{}');
        } catch {
            window[key] = {};
        }
    });
}

initPageConfigs();

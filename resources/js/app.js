import './bootstrap';

document.addEventListener('flux:editor', (e) => {
    e.detail.enableExtension('table');
    e.detail.enableExtension('tableRow');
    e.detail.enableExtension('tableCell');
    e.detail.enableExtension('tableHeader');
});

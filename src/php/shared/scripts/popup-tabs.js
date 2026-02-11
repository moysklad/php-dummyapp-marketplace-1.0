const tabs = Array.from(document.querySelectorAll('.tab'));
const panels = Array.from(document.querySelectorAll('.tab-panel'));

const setActiveTab = (tabId) => {
    tabs.forEach(tab => {
        const isActive = tab.dataset.tab === tabId;

        tab.classList.toggle('active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    panels.forEach(panel => {
        const isActive = panel.dataset.tabPanel === tabId;

        panel.classList.toggle('active', isActive);
    });
};

tabs.forEach(tab => {
    tab.addEventListener('click', () => setActiveTab(tab.dataset.tab));
});

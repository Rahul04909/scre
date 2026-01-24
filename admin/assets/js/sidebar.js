/* Sidebar JS */
document.addEventListener("DOMContentLoaded", function () {
    // Toggle active class on list items
    const menuItems = document.querySelectorAll('.list-group-item');

    menuItems.forEach(item => {
        item.addEventListener('click', function (e) {
            // If it's a submenu toggle, let Bootstrap handle it, but flip arrow
            if (this.getAttribute('data-bs-toggle') === 'collapse') {
                return;
            }

            // Remove active from all
            menuItems.forEach(el => el.classList.remove('active'));
            // Add active to clicked (if not a toggle)
            this.classList.add('active');
        });
    });

    // Optional: Auto-expand sidebar if active item is in submenu
    const activeSubItem = document.querySelector('.sub-menu .list-group-item.active');
    if (activeSubItem) {
        const parentCollapse = activeSubItem.closest('.collapse');
        if (parentCollapse) {
            parentCollapse.classList.add('show');
            const toggle = document.querySelector(`[data-bs-target="#${parentCollapse.id}"]`);
            if (toggle) {
                toggle.setAttribute('aria-expanded', 'true');
            }
        }
    }
});

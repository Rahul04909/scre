/**
 * Sidebar Interactive Logic
 * Handles toggling, state persistence, and animations.
 */

document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const header = document.getElementById('main-header');
    
    // 1. Load Saved State (Desktop only)
    if (window.innerWidth > 768) {
        const savedState = localStorage.getItem('sidebarState');
        if (savedState === 'collapsed') {
            body.classList.add('toggled');
            if(header) header.style.marginLeft = '0';
        } else {
             body.classList.remove('toggled');
             // Header margin handled by CSS usually, but we can enforce if needed
             if(header) header.style.marginLeft = ''; 
        }
    }

    // 2. Toggle Handler
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle Class
            body.classList.toggle('toggled');
            
            // Animate Icon
            const icon = this.querySelector('i');
            if(icon) {
                if (body.classList.contains('toggled')) {
                    icon.classList.remove('fa-outdent');
                    icon.classList.add('fa-indent'); // Show "Expand" icon
                } else {
                    icon.classList.remove('fa-indent');
                    icon.classList.add('fa-outdent'); // Show "Collapse" icon
                }
            }

            // Save State
            if (window.innerWidth > 768) {
                if (body.classList.contains('toggled')) {
                    localStorage.setItem('sidebarState', 'collapsed');
                     if(header) header.style.marginLeft = '0';
                } else {
                    localStorage.setItem('sidebarState', 'expanded');
                     if(header) header.style.marginLeft = '';
                }
            }
        });
    }

    // 3. Mobile Close on Click Outside
    document.addEventListener('click', function(event) {
        const mobileToggle = document.getElementById('sidebar-toggle');
        if (window.innerWidth < 768 && 
            sidebar && 
            !sidebar.contains(event.target) && 
            mobileToggle && 
            !mobileToggle.contains(event.target) &&
            sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });
});

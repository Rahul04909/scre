document.addEventListener('DOMContentLoaded', function() {
    
    // Elements
    const body = document.body;
    const desktopToggle = document.getElementById('menu-toggle');
    const mobileToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar-wrapper');
    const toggleIcon = desktopToggle ? desktopToggle.querySelector('i') : null;

    // --- State Persistence ---
    const SIDEBAR_STATE_KEY = 'studentSidebarState'; // Key specific to student
    
    // Function to apply state
    function applySidebarState(isCollapsed) {
        if (window.innerWidth >= 768) {
            if (isCollapsed) {
                body.classList.add('toggled');
                if(toggleIcon) {
                    toggleIcon.classList.remove('fa-outdent');
                    toggleIcon.classList.add('fa-indent');
                }
            } else {
                body.classList.remove('toggled');
                if(toggleIcon) {
                    toggleIcon.classList.remove('fa-indent');
                    toggleIcon.classList.add('fa-outdent');
                }
            }
        }
    }

    // Load initial state
    const savedState = localStorage.getItem(SIDEBAR_STATE_KEY);
    if (savedState === 'collapsed') {
        applySidebarState(true);
    } else {
        applySidebarState(false); // Default open
    }

    // --- Desktop Toggle ---
    if (desktopToggle) {
        desktopToggle.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('toggled');
            
            // Icon Flip
            if(toggleIcon) {
                if (body.classList.contains('toggled')) {
                    toggleIcon.classList.remove('fa-outdent');
                    toggleIcon.classList.add('fa-indent');
                    localStorage.setItem(SIDEBAR_STATE_KEY, 'collapsed');
                } else {
                    toggleIcon.classList.remove('fa-indent');
                    toggleIcon.classList.add('fa-outdent');
                    localStorage.setItem(SIDEBAR_STATE_KEY, 'expanded');
                }
            }
        });
    }

    // --- Mobile Toggle ---
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            body.classList.toggle('toggled');
        });
    }

    // --- Close on Outside Click (Mobile) ---
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768 && body.classList.contains('toggled')) {
            // If click is outside sidebar and not on the toggle button
            if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                body.classList.remove('toggled');
            }
        }
    });

    // --- Resize Handler ---
    // Ensure correct state when resizing window
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            // Restore desktop preference
            const savedState = localStorage.getItem(SIDEBAR_STATE_KEY);
            applySidebarState(savedState === 'collapsed');
        } else {
            // Mobile defaults to hidden (no 'toggled' class usually means hidden on mobile via CSS unless logic inverted)
            // Current CSS: Mobile hidden by default (-280). 'toggled' shows it (0).
            body.classList.remove('toggled');
        }
    });

});

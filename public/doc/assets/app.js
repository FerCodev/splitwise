(function () {
    'use strict';

    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var toggleBtn = document.getElementById('menu-toggle');
    var navLinks = sidebar ? sidebar.querySelectorAll('nav a') : [];
    var sections = [];

    // Collect all section elements
    document.querySelectorAll('section[id]').forEach(function (s) {
        sections.push(s);
    });

    // ===== Mobile menu toggle =====
    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on nav link click (mobile)
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            closeSidebar();
        });
    });

    // ===== Active section tracking =====
    function updateActiveNav() {
        var scrollY = window.scrollY + 120; // offset for header
        var currentId = '';

        for (var i = 0; i < sections.length; i++) {
            var section = sections[i];
            if (section.offsetTop <= scrollY) {
                currentId = section.getAttribute('id');
            } else {
                break;
            }
        }

        navLinks.forEach(function (link) {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + currentId) {
                link.classList.add('active');
            }
        });
    }

    var ticking = false;
    window.addEventListener('scroll', function () {
        if (!ticking) {
            window.requestAnimationFrame(function () {
                updateActiveNav();
                ticking = false;
            });
            ticking = true;
        }
    });

    updateActiveNav();

})();

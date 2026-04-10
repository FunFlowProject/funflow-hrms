import { Helpers } from '../../public/js/helpers';
import { Menu } from '../../public/js/menu';

function initTooltips() {
    const tooltipNodes = document.querySelectorAll('[data-bs-toggle="tooltip"]');

    tooltipNodes.forEach((tooltipNode) => {
        new window.bootstrap.Tooltip(tooltipNode);
    });
}

function initAccordions() {
    const accordionNodes = document.querySelectorAll('.accordion');

    accordionNodes.forEach((accordionNode) => {
        const handler = (event) => {
            const itemNode = event.target.closest('.accordion-item');

            if (!itemNode) {
                return;
            }

            if (event.type === 'show.bs.collapse') {
                itemNode.classList.add('active');
            } else {
                itemNode.classList.remove('active');
            }
        };

        accordionNode.addEventListener('show.bs.collapse', handler);
        accordionNode.addEventListener('hide.bs.collapse', handler);
    });
}

function initMenuShadow() {
    // Menu shadow is disabled without perfect scrollbar
}

function calculatePageScrollHeight(pageScrollNode) {
    // Height calculation not needed natively
}

function syncPageScrollbar() {
    // Native scrollbar requires no syncing
}

function initPageScrollbar() {
    // Native scrolling used
}

function initMenuTogglers() {
    const menuTogglers = document.querySelectorAll('.layout-menu-toggle');

    menuTogglers.forEach((toggleNode) => {
        toggleNode.addEventListener('click', (event) => {
            event.preventDefault();
            window.Helpers.toggleCollapsed();
        });
    });
}

function initDesktopToggleHandle() {
    const layoutMenuNode = document.getElementById('layout-menu');

    if (!layoutMenuNode) {
        return;
    }

    const delay = (element, callback) => {
        let timeout;

        element.onmouseenter = function onMenuMouseEnter() {
            timeout = setTimeout(callback, Helpers.isSmallScreen() ? 0 : 300);
        };

        element.onmouseleave = function onMenuMouseLeave() {
            const toggleNode = document.querySelector('.layout-menu-toggle');

            toggleNode?.classList.remove('d-block');
            clearTimeout(timeout);
        };
    };

    delay(layoutMenuNode, () => {
        if (!Helpers.isSmallScreen()) {
            document.querySelector('.layout-menu-toggle')?.classList.add('d-block');
        }
    });
}

function initTemplateMenu() {
    document.querySelectorAll('#layout-menu').forEach((menuNode) => {
        const menuInstance = new Menu(menuNode, {
            orientation: 'vertical',
            closeChildren: false,
        });

        window.Helpers.scrollToActive(false);
        window.Helpers.mainMenu = menuInstance;
    });
}

export function initLayout() {
    if (document.body.dataset.layoutInitialized === 'true') {
        return;
    }

    document.body.dataset.layoutInitialized = 'true';

    window.Helpers = Helpers;
    window.Menu = Menu;

    initTemplateMenu();
    initPageScrollbar();
    initMenuTogglers();
    initDesktopToggleHandle();
    initMenuShadow();
    initTooltips();
    initAccordions();

    window.Helpers.setAutoUpdate(true);
    window.Helpers.initPasswordToggle();

    if (typeof window.Helpers.initSpeechToText === 'function') {
        window.Helpers.initSpeechToText();
    }

    if (!window.Helpers.isSmallScreen()) {
        window.Helpers.setCollapsed(true, false);
    }
}
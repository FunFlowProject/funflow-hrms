import PerfectScrollbar from 'perfect-scrollbar';
import { Helpers } from '../../public/js/helpers';
import { Menu } from '../../public/js/menu';

let pageScrollbarInstance = null;

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
    const menuInnerNode = document.querySelector('.menu-inner');
    const menuShadowNode = document.querySelector('.menu-inner-shadow');

    if (!menuInnerNode || !menuShadowNode) {
        return;
    }

    menuInnerNode.addEventListener('ps-scroll-y', function onMenuScroll() {
        const thumbNode = this.querySelector('.ps__thumb-y');

        if (!thumbNode) {
            return;
        }

        menuShadowNode.style.display = thumbNode.offsetTop ? 'block' : 'none';
    });
}

function calculatePageScrollHeight(pageScrollNode) {
    const footerNode = document.querySelector('.content-footer');
    const footerHeight = footerNode ? footerNode.getBoundingClientRect().height : 0;
    const topOffset = pageScrollNode.getBoundingClientRect().top;

    return Math.max(220, Math.floor(window.innerHeight - topOffset - footerHeight - 12));
}

function syncPageScrollbar() {
    const pageScrollNode = document.getElementById('layout-page-content');

    if (!pageScrollNode) {
        return;
    }

    if (window.Helpers.isSmallScreen()) {
        pageScrollNode.style.height = '';

        if (pageScrollbarInstance) {
            pageScrollbarInstance.destroy();
            pageScrollbarInstance = null;
        }

        return;
    }

    pageScrollNode.style.height = `${calculatePageScrollHeight(pageScrollNode)}px`;

    if (!pageScrollbarInstance) {
        pageScrollbarInstance = new PerfectScrollbar(pageScrollNode, {
            suppressScrollX: true,
            wheelPropagation: false,
        });
    } else {
        pageScrollbarInstance.update();
    }
}

function initPageScrollbar() {
    const pageScrollNode = document.getElementById('layout-page-content');

    if (!pageScrollNode) {
        return;
    }

    const sync = () => {
        syncPageScrollbar();
    };

    sync();

    window.Helpers.on('resize.layout:pageScroll', sync);
    window.Helpers.on('toggle.layout:pageScroll', sync);

    if (typeof window.ResizeObserver === 'function') {
        const resizeObserver = new window.ResizeObserver(sync);
        const navbarNode = document.querySelector('.layout-navbar');
        const footerNode = document.querySelector('.content-footer');

        if (navbarNode) {
            resizeObserver.observe(navbarNode);
        }

        if (footerNode) {
            resizeObserver.observe(footerNode);
        }
    }
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
    window.PerfectScrollbar = PerfectScrollbar;

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
/**
 * Modularity Table of Contents
 *
 * Generates a linked table of contents based on headings found in specified containers.
 */
class TableOfContents {
    static MOBILE_MEDIA_QUERY = window.matchMedia('(max-width: 62em)');

    /**
     * Main content selector - always scanned for headings.
     */
    static MAIN_CONTENT_SELECTOR = 'article.c-article';

    /**
     * Maps sidebar ACF values to DOM selectors.
     * Only containers whose key appears in config.sidebars are scanned.
     */
    static SIDEBAR_SELECTOR_MAP = {
        'content-area-top': '#sidebar-content-area-top',
        'content-area': '#sidebar-content-area',
        'content-area-bottom': '#sidebar-content-area-bottom',
    };

    /**
     * @param {HTMLElement} rootElement - The TOC root element
     * @param {HTMLElement} tocElement - The TOC nav element
     * @param {Object} config - Configuration object
     * @param {string} config.id - Unique identifier for this TOC
     * @param {string[]} config.sidebars - Sidebar identifiers to include headings from (besides main content)
     * @param {string[]} config.headingLevels - Heading levels to include (e.g., ['h2', 'h3'])
     * @param {boolean} config.ignoreCardSubHeaders - If true, ignore headings inside .c-card that are not in .c-card__header
     * @param {string} config.mobileStyle - Mobile style, dropdown or expanded
     */
    constructor(rootElement, tocElement, config) {
        this.rootElement = rootElement;
        this.tocElement = tocElement;
        this.config = config;
        this.listElement = tocElement.querySelector('.c-toc__list');
        this.toggleButton = rootElement.querySelector('[data-toc-toggle]');
        this.panelElement = rootElement.querySelector('[data-toc-panel]');
        this.headings = [];
        this.observer = null;
        this.activeHeadingId = null;
        this.hasTrack = false;
        this.mobileStyle = config.mobileStyle === 'expanded' ? 'expanded' : 'dropdown';
        this.handleMediaChange = () => this.syncMobileState();

        if (!this.listElement) {
            console.warn('TOC list element not found');
            return;
        }

        this.hasTrack = this.listElement.classList.contains('c-toc__list--track');

        this.init();
    }

    /**
     * Initialize the table of contents
     */
    init() {
        this.headings = this.findHeadings();

        if (this.headings.length === 0) {
            this.rootElement.hidden = true;
            return;
        }

        this.ensureHeadingIds(this.headings);
        this.renderToc(this.headings);
        this.setupMobileStyle();
        this.setupObserver();
    }

    /**
     * Check if an element is visible
     * @param {HTMLElement} element
     * @returns {boolean}
     */
    isElementVisible(element) {
        let current = element;

        while (current && current !== document.body) {
            const style = window.getComputedStyle(current);

            if (style.display === 'none' || style.visibility === 'hidden') {
                return false;
            }

            current = current.parentElement;
        }

        return true;
    }

    /**
     * Build the list of container selectors based on config.sidebars.
     * Main content is always included; sidebars are opt-in.
     * @returns {string[]}
     */
    getContainerSelectors() {
        const selectors = [TableOfContents.MAIN_CONTENT_SELECTOR];
        const sidebars = Array.isArray(this.config.sidebars) ? this.config.sidebars : [];
        const sidebarSelectorMap = {
            ...TableOfContents.SIDEBAR_SELECTOR_MAP,
            ...(this.config.sidebarSelectorMap || {}),
        };

        for (const key of sidebars) {
            const sel = sidebarSelectorMap[key];
            if (sel) {
                selectors.push(sel);
            }
        }

        return selectors;
    }

    findHeadings() {
        const headings = [];
        const selector = this.config.headingLevels.join(', ');
        const containerSelectors = this.getContainerSelectors();

        for (const containerSelector of containerSelectors) {
            const container = document.querySelector(containerSelector);

            if (!container) {
                continue;
            }

            const containerHeadings = container.querySelectorAll(selector);

            for (const heading of containerHeadings) {
                if (!this.isElementVisible(heading)) {
                    continue;
                }

                if (heading.closest('.modularity-mod-toc')) {
                    continue;
                }

                if (this.config.ignoreCardSubHeaders) {
                    const card = heading.closest('.c-card');

                    if (card && !heading.closest('.c-card__header')) {
                        continue;
                    }
                }

                headings.push(heading);
            }
        }

        headings.sort((a, b) => {
            const position = a.compareDocumentPosition(b);

            if (position & Node.DOCUMENT_POSITION_FOLLOWING) {
                return -1;
            }

            if (position & Node.DOCUMENT_POSITION_PRECEDING) {
                return 1;
            }

            return 0;
        });

        return headings;
    }

    /**
     * Ensure all headings have unique IDs for linking
     * @param {HTMLElement[]} headings
     */
    ensureHeadingIds(headings) {
        const usedIds = new Set();

        headings.forEach((heading) => {
            if (!heading.id) {
                const baseId = this.slugify(heading.textContent);
                let id = baseId;
                let counter = 1;

                while (usedIds.has(id) || document.getElementById(id)) {
                    id = `${baseId}-${counter}`;
                    counter += 1;
                }

                heading.id = id;
            }

            usedIds.add(heading.id);
        });
    }

    /**
     * Convert text to a URL-friendly slug
     * @param {string} text
     * @returns {string}
     */
    slugify(text) {
        const charMap = {
            'å': 'a', 'ä': 'a', 'ö': 'o',
            'Å': 'a', 'Ä': 'a', 'Ö': 'o',
            'é': 'e', 'è': 'e', 'ë': 'e', 'ê': 'e',
            'á': 'a', 'à': 'a', 'â': 'a', 'ã': 'a',
            'ó': 'o', 'ò': 'o', 'ô': 'o', 'õ': 'o',
            'ú': 'u', 'ù': 'u', 'û': 'u', 'ü': 'u',
            'í': 'i', 'ì': 'i', 'î': 'i', 'ï': 'i',
            'ñ': 'n', 'ç': 'c'
        };

        return text
            .toLowerCase()
            .trim()
            .split('')
            .map(char => charMap[char] || char)
            .join('')
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '') || 'heading';
    }

    /**
     * Render the table of contents list with nested structure
     * @param {HTMLElement[]} headings
     */
    renderToc(headings) {
        const getLevel = (heading) => parseInt(heading.tagName.charAt(1), 10);
        const trackClass = this.hasTrack ? ' c-toc__list--track' : '';

        const buildNestedList = (items, startIndex = 0, parentLevel = 0) => {
            const list = document.createElement('ul');
            list.className = startIndex === 0 ? `c-toc__list${trackClass}` : 'c-toc__sublist';

            let index = startIndex;

            while (index < items.length) {
                const heading = items[index];
                const level = getLevel(heading);

                if (parentLevel > 0 && level <= parentLevel) {
                    break;
                }

                const listItem = document.createElement('li');
                listItem.className = 'c-toc__item';
                listItem.classList.add(`c-toc__item--${heading.tagName.toLowerCase()}`);

                const link = document.createElement('a');
                link.href = `#${heading.id}`;
                link.className = 'c-toc__link';
                link.textContent = heading.textContent;
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    this.setActiveHeading(heading.id);
                    this.scrollToHeading(heading);
                    this.closeMobileDropdown();
                });

                listItem.appendChild(link);

                const nextIndex = index + 1;

                if (nextIndex < items.length) {
                    const nextLevel = getLevel(items[nextIndex]);

                    if (nextLevel > level) {
                        const result = buildNestedList(items, nextIndex, level);
                        listItem.appendChild(result.list);
                        index = result.lastIndex;
                    } else {
                        index += 1;
                    }
                } else {
                    index += 1;
                }

                list.appendChild(listItem);
            }

            return { list, lastIndex: index };
        };

        const result = buildNestedList(headings);
        this.listElement.replaceWith(result.list);
        this.listElement = result.list;
    }

    /**
     * Smoothly scroll to a heading
     * @param {HTMLElement} heading
     */
    scrollToHeading(heading) {
        const headerHeight = this.getScrollOffset(heading);
        const elementPosition = heading.getBoundingClientRect().top + window.scrollY;
        const offsetPosition = elementPosition - headerHeight;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });

        history.pushState(null, '', `#${heading.id}`);

        heading.setAttribute('tabindex', '-1');
        heading.focus({ preventScroll: true });
    }

    /**
     * Setup mobile style controls
     */
    setupMobileStyle() {
        if (!this.toggleButton || !this.panelElement) {
            return;
        }

        this.toggleButton.addEventListener('click', () => this.toggleMobileDropdown());

        if (typeof TableOfContents.MOBILE_MEDIA_QUERY.addEventListener === 'function') {
            TableOfContents.MOBILE_MEDIA_QUERY.addEventListener('change', this.handleMediaChange);
        } else if (typeof TableOfContents.MOBILE_MEDIA_QUERY.addListener === 'function') {
            TableOfContents.MOBILE_MEDIA_QUERY.addListener(this.handleMediaChange);
        }

        this.syncMobileState();
    }

    /**
     * Synchronize the mobile UI with the current viewport and selected style
     */
    syncMobileState() {
        if (!this.toggleButton || !this.panelElement) {
            return;
        }

        const useDropdown = this.shouldUseMobileDropdown();

        this.rootElement.classList.toggle('is-mobile-open', useDropdown && this.rootElement.classList.contains('is-mobile-open'));
        this.toggleButton.hidden = !useDropdown;

        if (useDropdown) {
            const isExpanded = this.rootElement.classList.contains('is-mobile-open');
            this.toggleButton.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
            this.panelElement.hidden = !isExpanded;
        } else {
            this.rootElement.classList.remove('is-mobile-open');
            this.toggleButton.setAttribute('aria-expanded', 'false');
            this.panelElement.hidden = false;
        }
    }

    /**
     * Toggle the dropdown panel on mobile
     */
    toggleMobileDropdown() {
        if (!this.shouldUseMobileDropdown()) {
            return;
        }

        const isExpanded = this.rootElement.classList.toggle('is-mobile-open');
        this.toggleButton.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        this.panelElement.hidden = !isExpanded;
    }

    /**
     * Close the dropdown after a link click
     */
    closeMobileDropdown() {
        if (!this.shouldUseMobileDropdown()) {
            return;
        }

        this.rootElement.classList.remove('is-mobile-open');
        this.toggleButton.setAttribute('aria-expanded', 'false');
        this.panelElement.hidden = true;
    }

    /**
     * Check if the mobile dropdown style should be active
     * @returns {boolean}
     */
    shouldUseMobileDropdown() {
        return this.mobileStyle === 'dropdown' && TableOfContents.MOBILE_MEDIA_QUERY.matches;
    }

    /**
     * Set up IntersectionObserver to track active heading
     */
    setupObserver() {
        if (this.headings.length > 0) {
            this.setActiveHeading(this.headings[0].id);
        }

        const initObserver = () => {
            const headerHeight = this.getScrollOffset(this.headings[0] ?? null);

            const options = {
                root: null,
                rootMargin: `-${headerHeight}px 0px -70% 0px`,
                threshold: 0
            };

            this.observer = new IntersectionObserver((entries) => {
                this.handleIntersection(entries);
            }, options);

            this.headings.forEach((heading) => {
                this.observer.observe(heading);
            });
        };

        if (document.readyState === 'complete') {
            initObserver();
        } else {
            window.addEventListener('load', initObserver, { once: true });
        }
    }

    /**
     * Handle intersection observer entries
     * @param {IntersectionObserverEntry[]} entries
     */
    handleIntersection(entries) {
        const visibleHeadings = [];

        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                visibleHeadings.push(entry.target);
            }
        });

        if (visibleHeadings.length > 0) {
            visibleHeadings.sort((a, b) => this.headings.indexOf(a) - this.headings.indexOf(b));
            this.setActiveHeading(visibleHeadings[0].id);
        }
    }

    /**
     * Set the active heading and update TOC link styling
     * @param {string} headingId
     */
    setActiveHeading(headingId) {
        if (this.activeHeadingId === headingId) {
            return;
        }

        this.activeHeadingId = headingId;

        const allItems = this.listElement.querySelectorAll('.c-toc__item');
        allItems.forEach((item) => {
            item.classList.remove('c-toc__item--active');
        });

        const activeLink = this.listElement.querySelector(`a[href="#${headingId}"]`);

        if (!activeLink) {
            return;
        }

        const activeItem = activeLink.closest('.c-toc__item');

        if (!activeItem) {
            return;
        }

        activeItem.classList.add('c-toc__item--active');

        if (this.hasTrack) {
            this.updateTrackPosition(activeItem);
        }
    }

    /**
     * Update the sliding track position to match the active item
     * @param {HTMLElement} activeItem
     */
    updateTrackPosition(activeItem) {
        const listRect = this.listElement.getBoundingClientRect();
        const itemRect = activeItem.getBoundingClientRect();

        const top = itemRect.top - listRect.top;
        const height = itemRect.height;

        this.listElement.style.setProperty('--toc-track-top', `${top}px`);
        this.listElement.style.setProperty('--toc-track-height', `${height}px`);
    }

    /**
     * Get the vertical offset needed to keep anchors below the sticky header
     * @param {HTMLElement|null} heading
     * @returns {number}
     */
    getScrollOffset(heading = null) {
        if (heading instanceof HTMLElement) {
            const scrollMarginTop = window.getComputedStyle(heading).scrollMarginTop;
            const parsedScrollMarginTop = Number.parseFloat(scrollMarginTop);

            if (Number.isFinite(parsedScrollMarginTop) && parsedScrollMarginTop > 0) {
                return parsedScrollMarginTop;
            }
        }

        const header = document.querySelector('.site-header.c-header.c-header--flexible, .site-header.c-header, .c-header');

        if (header instanceof HTMLElement && header.classList.contains('c-header--sticky')) {
            return header.getBoundingClientRect().bottom + 25;
        }

        return 25;
    }
}

/**
 * Initialize all TOC instances on the page
 */
function initTableOfContents() {
    const configElements = document.querySelectorAll('script[data-toc-config]');

    configElements.forEach((configElement) => {
        try {
            const config = JSON.parse(configElement.textContent);
            const tocElement = document.getElementById(config.id);

            if (!tocElement) {
                return;
            }

            const rootElement = tocElement.closest('[data-toc-root]');

            if (!rootElement) {
                return;
            }

            new TableOfContents(rootElement, tocElement, config);
        } catch (error) {
            console.error('Failed to initialize TOC:', error);
        }
    });
}

/**
 * Move automatically inserted mobile TOC elements into their target containers
 */
function initMobileInsertion() {
    const mobileRoots = document.querySelectorAll('[data-toc-root][data-toc-insertion-selector]');

    mobileRoots.forEach((rootElement) => {
        const selector = rootElement.dataset.tocInsertionSelector;
        const method = rootElement.dataset.tocInsertionMethod || 'prepend';

        if (!selector) {
            return;
        }

        const container = document.querySelector(selector);

        if (!container) {
            return;
        }

        const mobileId = rootElement.dataset.tocRoot;
        const wrapper = document.createElement('div');
        wrapper.id = `mod-toc-mobile-${mobileId}`;
        wrapper.className = 'modularity-mod-toc';
        wrapper.setAttribute('lang', 'i-');
        wrapper.appendChild(rootElement);

        if (method === 'prepend') {
            container.prepend(wrapper);
        } else {
            container.append(wrapper);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initTableOfContents();
        initMobileInsertion();
    });
} else {
    initTableOfContents();
    initMobileInsertion();
}

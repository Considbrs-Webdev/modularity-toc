/**
 * Modularity Table of Contents
 * 
 * Generates a linked table of contents based on headings found in specified containers.
 */

class TableOfContents {
    /**
     * Container selectors to search for headings, in priority order
     */
    static CONTAINER_SELECTORS = [
        '#sidebar-content-area-top',
        'article.c-article',
        '#sidebar-content-area',
        '#sidebar-content-area-bottom'
    ];

    /**
     * @param {HTMLElement} tocElement - The TOC nav element
     * @param {Object} config - Configuration object
     * @param {string} config.id - Unique identifier for this TOC
     * @param {string[]} config.sidebars - Sidebar identifiers (unused currently, reserved for future)
     * @param {string[]} config.headingLevels - Heading levels to include (e.g., ['h2', 'h3'])
     * @param {boolean} config.ignoreCardSubHeaders - If true, ignore headings inside .c-card that are not in .c-card__header
     */
    constructor(tocElement, config) {
        this.tocElement = tocElement;
        this.config = config;
        this.listElement = tocElement.querySelector('.c-toc__list');
        this.headings = [];
        this.observer = null;
        this.activeHeadingId = null;
        this.hasTrack = false;
        
        if (!this.listElement) {
            console.warn('TOC list element not found');
            return;
        }

        // Check if track is enabled
        this.hasTrack = this.listElement.classList.contains('c-toc__list--track');

        this.init();
    }

    /**
     * Initialize the table of contents
     */
    init() {
        this.headings = this.findHeadings();
        
        if (this.headings.length === 0) {
            this.tocElement.style.display = 'none';
            return;
        }

        this.ensureHeadingIds(this.headings);
        this.renderToc(this.headings);
        this.setupObserver();
    }

    /**
     * Find all matching headings in the specified containers
     * @returns {HTMLElement[]} Array of heading elements
     */
    findHeadings() {
        const headings = [];
        const selector = this.config.headingLevels.join(', ');

        for (const containerSelector of TableOfContents.CONTAINER_SELECTORS) {
            const container = document.querySelector(containerSelector);
            
            if (container) {
                const containerHeadings = container.querySelectorAll(selector);
                
                for (const heading of containerHeadings) {
                    // If ignoreCardSubHeaders is enabled, skip headings inside .c-card
                    // that are not in .c-card__header
                    if (this.config.ignoreCardSubHeaders) {
                        const card = heading.closest('.c-card');
                        if (card && !heading.closest('.c-card__header')) {
                            continue;
                        }
                    }
                    
                    headings.push(heading);
                }
            }
        }

        return headings;
    }

    /**
     * Ensure all headings have unique IDs for linking
     * @param {HTMLElement[]} headings - Array of heading elements
     */
    ensureHeadingIds(headings) {
        const usedIds = new Set();

        headings.forEach((heading, index) => {
            if (!heading.id) {
                let baseId = this.slugify(heading.textContent);
                let id = baseId;
                let counter = 1;

                // Ensure unique ID
                while (usedIds.has(id) || document.getElementById(id)) {
                    id = `${baseId}-${counter}`;
                    counter++;
                }

                heading.id = id;
            }

            usedIds.add(heading.id);
        });
    }

    /**
     * Convert text to a URL-friendly slug
     * @param {string} text - Text to convert
     * @returns {string} Slugified text
     */
    slugify(text) {
        return text
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '')
            || 'heading';
    }

    /**
     * Render the table of contents list with nested structure
     * @param {HTMLElement[]} headings - Array of heading elements
     */
    renderToc(headings) {
        const getLevel = (heading) => parseInt(heading.tagName.charAt(1), 10);
        
        // Preserve the track class from the original list element
        const trackClass = this.hasTrack ? ' c-toc__list--track' : '';
        
        const buildNestedList = (headings, startIndex = 0, parentLevel = 0) => {
            const ul = document.createElement('ul');
            ul.className = startIndex === 0 ? 'c-toc__list' + trackClass : 'c-toc__sublist';
            
            let i = startIndex;
            
            while (i < headings.length) {
                const heading = headings[i];
                const level = getLevel(heading);
                
                // If this heading is higher level (smaller number) than parent, go back up
                if (parentLevel > 0 && level <= parentLevel) {
                    break;
                }
                
                const li = document.createElement('li');
                li.className = 'c-toc__item';
                li.classList.add(`c-toc__item--${heading.tagName.toLowerCase()}`);

                const link = document.createElement('a');
                link.href = `#${heading.id}`;
                link.className = 'c-toc__link';
                link.textContent = heading.textContent;

                // Smooth scroll on click
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.scrollToHeading(heading);
                });

                li.appendChild(link);
                
                // Check if next heading should be nested
                const nextIndex = i + 1;
                if (nextIndex < headings.length) {
                    const nextLevel = getLevel(headings[nextIndex]);
                    
                    // If next heading is deeper level, create nested list
                    if (nextLevel > level) {
                        const result = buildNestedList(headings, nextIndex, level);
                        li.appendChild(result.list);
                        i = result.lastIndex;
                    } else {
                        i++;
                    }
                } else {
                    i++;
                }
                
                ul.appendChild(li);
            }
            
            return { list: ul, lastIndex: i };
        };
        
        const result = buildNestedList(headings);
        
        // Replace the empty list with the built one
        this.listElement.replaceWith(result.list);
        this.listElement = result.list;
    }

    /**
     * Smoothly scroll to a heading
     * @param {HTMLElement} heading - The heading element to scroll to
     */
    scrollToHeading(heading) {
        const header = document.querySelector('.c-header__main-upper-area-container');
        const headerHeight = header ? header.offsetHeight : 0;
        const elementPosition = heading.getBoundingClientRect().top + window.scrollY;
        const offsetPosition = elementPosition - headerHeight;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
        
        // Update URL hash without jumping
        history.pushState(null, '', `#${heading.id}`);
        
        // Set focus for accessibility
        heading.setAttribute('tabindex', '-1');
        heading.focus({ preventScroll: true });
    }

    /**
     * Set up IntersectionObserver to track active heading
     */
    setupObserver() {
        // Set the first heading as active initially
        if (this.headings.length > 0) {
            this.setActiveHeading(this.headings[0].id);
        }

        const header = document.querySelector('.c-header__main-upper-area-container');
        const headerHeight = header ? header.offsetHeight : 0;

        const options = {
            root: null,
            rootMargin: `-${headerHeight}px 0px -70% 0px`,
            threshold: 0
        };

        this.observer = new IntersectionObserver((entries) => {
            this.handleIntersection(entries);
        }, options);

        this.headings.forEach(heading => {
            this.observer.observe(heading);
        });
    }

    /**
     * Handle intersection observer entries
     * @param {IntersectionObserverEntry[]} entries - Observer entries
     */
    handleIntersection(entries) {
        // Find all currently intersecting headings
        const visibleHeadings = [];
        
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                visibleHeadings.push(entry.target);
            }
        });

        // If we have visible headings, use the first one in document order
        if (visibleHeadings.length > 0) {
            // Sort by position in the headings array (document order)
            visibleHeadings.sort((a, b) => {
                return this.headings.indexOf(a) - this.headings.indexOf(b);
            });
            
            this.setActiveHeading(visibleHeadings[0].id);
        }
    }

    /**
     * Set the active heading and update TOC link styling
     * @param {string} headingId - The ID of the active heading
     */
    setActiveHeading(headingId) {
        if (this.activeHeadingId === headingId) {
            return;
        }

        this.activeHeadingId = headingId;

        // Remove active class from all items
        const allItems = this.listElement.querySelectorAll('.c-toc__item');
        allItems.forEach(item => {
            item.classList.remove('c-toc__item--active');
        });

        // Add active class to current item and update track position
        const activeLink = this.listElement.querySelector(`a[href="#${headingId}"]`);
        if (activeLink) {
            const activeItem = activeLink.closest('.c-toc__item');
            if (activeItem) {
                activeItem.classList.add('c-toc__item--active');
                
                // Only update track position if track is enabled
                if (this.hasTrack) {
                    this.updateTrackPosition(activeItem);
                }
            }
        }
    }

    /**
     * Update the sliding track position to match the active item
     * @param {HTMLElement} activeItem - The active list item element
     */
    updateTrackPosition(activeItem) {
        const listRect = this.listElement.getBoundingClientRect();
        const itemRect = activeItem.getBoundingClientRect();
        
        const top = itemRect.top - listRect.top;
        const height = itemRect.height;

        this.listElement.style.setProperty('--toc-track-top', `${top}px`);
        this.listElement.style.setProperty('--toc-track-height', `${height}px`);
    }
}

/**
 * Initialize mobile drawer functionality
 */
function initMobileDrawer() {
    /**
     * Calculate and set header height as CSS variable
     */
    function setHeaderHeight() {
        if (document.body.classList.contains('sticky-header')) {
            const header = document.querySelector('header.c-header');
            if (header) {
                let headerHeight = header.offsetHeight;
                
                // Add WordPress admin bar height if present
                if (document.body.classList.contains('admin-bar')) {
                    headerHeight += 32;
                }
                
                document.documentElement.style.setProperty('--header-height', `${headerHeight}px`);
            }
        }
    }

    /**
     * Get all focusable elements within a container
     * @param {HTMLElement} container 
     * @returns {HTMLElement[]}
     */
    function getFocusableElements(container) {
        const focusableSelectors = [
            'a[href]',
            'button:not([disabled])',
            'input:not([disabled])',
            'select:not([disabled])',
            'textarea:not([disabled])',
            '[tabindex]:not([tabindex="-1"])'
        ];
        return Array.from(container.querySelectorAll(focusableSelectors.join(', '))).filter(
            el => el.offsetParent !== null // Only visible elements
        );
    }

    /**
     * Handle focus trap within drawer - prevents ALL tab navigation outside
     * @param {KeyboardEvent} e 
     * @param {HTMLElement} drawer 
     */
    function handleFocusTrap(e, drawer) {
        if (e.key !== 'Tab') return;

        const focusableElements = getFocusableElements(drawer);
        if (focusableElements.length === 0) {
            e.preventDefault();
            return;
        }

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        const activeElement = document.activeElement;

        // Check if focus is currently inside the drawer
        const focusIsInDrawer = drawer.contains(activeElement);

        if (!focusIsInDrawer) {
            // Focus escaped somehow, bring it back
            e.preventDefault();
            firstElement.focus();
            return;
        }

        if (e.shiftKey) {
            // Shift + Tab: if on first element, go to last
            if (activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
            }
        } else {
            // Tab: if on last element, go to first
            if (activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        }
    }

    // Track active focus trap
    let activeFocusTrapHandler = null;
    let lastFocusedElement = null;
    let activeDrawer = null;
    let isTransitioning = false;

    /**
     * Keep focus inside drawer on any focus change
     * @param {FocusEvent} e 
     */
    function handleFocusIn(e) {
        if (!activeDrawer) return;
        
        // If focus moved outside the drawer, bring it back
        if (!activeDrawer.contains(e.target)) {
            e.preventDefault();
            e.stopPropagation();
            
            const focusableElements = getFocusableElements(activeDrawer);
            if (focusableElements.length > 0) {
                focusableElements[0].focus();
            }
        }
    }

    /**
     * Open drawer with focus trap
     * @param {HTMLElement} moduleElement 
     */
    function openDrawer(moduleElement) {
        // Prevent opening if transitioning
        if (isTransitioning) return;
        
        isTransitioning = true;
        
        const drawer = moduleElement.querySelector('.c-toc-drawer');
        const toggleButton = moduleElement.querySelector('[data-toc-toggle]');
        
        // Save last focused element to restore later
        lastFocusedElement = document.activeElement;
        activeDrawer = drawer;
        
        setHeaderHeight();
        moduleElement.classList.add('is-toc-open');
        document.body.style.overflow = 'hidden';

        // Update ARIA attributes
        if (toggleButton) {
            toggleButton.setAttribute('aria-expanded', 'true');
        }
        drawer.setAttribute('aria-hidden', 'false');

        // Focus the close button
        const closeButton = drawer.querySelector('[data-toc-close]');
        if (closeButton) {
            setTimeout(() => closeButton.focus(), 100);
        }

        // Set up focus trap via Tab key
        activeFocusTrapHandler = (e) => handleFocusTrap(e, drawer);
        document.addEventListener('keydown', activeFocusTrapHandler);

        // Also trap focus on any focus change (catches mouse clicks, etc.)
        document.addEventListener('focusin', handleFocusIn);

        // Close on Escape key
        document.addEventListener('keydown', handleEscapeKey);
        
        // Allow transitions after a short delay
        setTimeout(() => {
            isTransitioning = false;
        }, 400);
    }

    /**
     * Close drawer and release focus trap
     * @param {HTMLElement} moduleElement 
     */
    function closeDrawer(moduleElement) {
        // Prevent closing if transitioning
        if (isTransitioning) return;
        
        isTransitioning = true;
        
        const drawer = moduleElement.querySelector('.c-toc-drawer');
        const toggleButton = moduleElement.querySelector('[data-toc-toggle]');

        moduleElement.classList.remove('is-toc-open');
        document.body.style.overflow = '';

        // Update ARIA attributes
        if (toggleButton) {
            toggleButton.setAttribute('aria-expanded', 'false');
        }
        if (drawer) {
            drawer.setAttribute('aria-hidden', 'true');
        }

        // Remove focus trap
        if (activeFocusTrapHandler) {
            document.removeEventListener('keydown', activeFocusTrapHandler);
            activeFocusTrapHandler = null;
        }
        document.removeEventListener('keydown', handleEscapeKey);
        document.removeEventListener('focusin', handleFocusIn);
        activeDrawer = null;

        // Restore focus to trigger button
        if (lastFocusedElement) {
            lastFocusedElement.focus();
            lastFocusedElement = null;
        }
        
        // Allow transitions after a short delay
        setTimeout(() => {
            isTransitioning = false;
        }, 400);
    }

    /**
     * Handle Escape key to close drawer
     * @param {KeyboardEvent} e 
     */
    function handleEscapeKey(e) {
        if (e.key === 'Escape') {
            const openModule = document.querySelector('.modularity-mod-toc.is-toc-open');
            if (openModule) {
                closeDrawer(openModule);
            }
        }
    }

    // Set header height on init
    setHeaderHeight();

    // Update header height on resize
    window.addEventListener('resize', setHeaderHeight);

    // Handle toggle buttons (open)
    document.querySelectorAll('[data-toc-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const moduleElement = button.closest('.modularity-mod-toc');
            
            if (moduleElement) {
                openDrawer(moduleElement);
            }
        });
    });

    // Handle close buttons
    document.querySelectorAll('[data-toc-close]').forEach(button => {
        button.addEventListener('click', () => {
            const moduleElement = button.closest('.modularity-mod-toc');
            
            if (moduleElement) {
                closeDrawer(moduleElement);
            }
        });
    });

    // Handle overlay clicks
    document.querySelectorAll('[data-toc-overlay]').forEach(overlay => {
        overlay.addEventListener('click', () => {
            const moduleElement = overlay.closest('.modularity-mod-toc');
            
            if (moduleElement) {
                closeDrawer(moduleElement);
            }
        });
    });

    // Close drawer when clicking TOC links on mobile
    document.querySelectorAll('.c-toc__link').forEach(link => {
        link.addEventListener('click', () => {
            const moduleElement = link.closest('.modularity-mod-toc');
            
            if (moduleElement && window.innerWidth < 1248) { // 78em = 1248px
                // Use scrollend event to close after smooth scroll finishes
                const closeAfterScroll = () => {
                    closeDrawer(moduleElement);
                    window.removeEventListener('scrollend', closeAfterScroll);
                };

                // Check if browser supports scrollend event
                if ('onscrollend' in window) {
                    window.addEventListener('scrollend', closeAfterScroll, { once: true });
                    
                    // Fallback timeout in case scrollend doesn't fire (e.g., already at position)
                    setTimeout(() => {
                        window.removeEventListener('scrollend', closeAfterScroll);
                        if (moduleElement.classList.contains('is-toc-open')) {
                            closeDrawer(moduleElement);
                        }
                    }, 2000);
                } else {
                    // Fallback for older browsers
                    setTimeout(() => {
                        closeDrawer(moduleElement);
                    }, 750);
                }
            }
        });
    });
}

/**
 * Initialize all TOC instances on the page
 */
function initTableOfContents() {
    const configElements = document.querySelectorAll('script[data-toc-config]');

    configElements.forEach(configElement => {
        try {
            const config = JSON.parse(configElement.textContent);
            const tocElement = document.getElementById(config.id);

            if (tocElement) {
                new TableOfContents(tocElement, config);
            }
        } catch (e) {
            console.error('Failed to initialize TOC:', e);
        }
    });

    // Initialize mobile drawer
    initMobileDrawer();
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTableOfContents);
} else {
    initTableOfContents();
}
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
        
        if (!this.listElement) {
            console.warn('TOC list element not found');
            return;
        }

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
        
        const buildNestedList = (headings, startIndex = 0, parentLevel = 0) => {
            const ul = document.createElement('ul');
            ul.className = startIndex === 0 ? 'c-toc__list' : 'c-toc__sublist';
            
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
                this.updateTrackPosition(activeItem);
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
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTableOfContents);
} else {
    initTableOfContents();
}
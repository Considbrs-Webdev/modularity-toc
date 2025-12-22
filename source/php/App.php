<?php

namespace ModularityToc;

use ModularityToc\AcfFields\AcfFieldLoader;
use ModularityToc\Helper\CacheBust;

class App
{
    public function __construct()
    {
        // Register module
        add_action('init', array($this, 'registerModule'));

        // Register ACF options page
        add_action('acf/init', array($this, 'registerOptionsPage'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));

        // Load ACF field values
        new AcfFieldLoader();

        
    }

    /**
     * Enqueue styles
     * @return void
     */
    public function enqueueStyles()
    {
        $styleFile = CacheBust::name('css/modularity-toc.css');

        if ($styleFile) {
            wp_enqueue_style(
                'modularity-toc',
                MODULARITY_TOC_URL . '/assets/dist/' . $styleFile,
                array(),
                null
            );
        }
    }

    /**
     * Enqueue scripts
     * @return void
     */
    public function enqueueScripts()
    {
        $scriptFile = CacheBust::name('js/modularity-toc.js');

        if ($scriptFile) {
            wp_enqueue_script(
                'modularity-toc',
                MODULARITY_TOC_URL . '/assets/dist/' . $scriptFile,
                array(),
                null,
                true
            );
        }
    }

    /**
     * Register the module
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITY_TOC_MODULE_PATH,
                'TableOfContents'
            );
        }
    }

    /**
     * Register ACF options page
     * @return void
     */
    public function registerOptionsPage()
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page(array(
                'page_title'    => __('Table of Contents Settings', 'modularity-toc'),
                'menu_title'    => __('Table of Contents', 'modularity-toc'),
                'menu_slug'     => 'modularity-toc-settings',
                'post_id'       => 'modularity-toc-settings',
                'capability'    => 'manage_options',
                'parent_slug'   => 'options-general.php',
                'position'      => false,
                'icon_url'      => false,
            ));
        }
    }
}

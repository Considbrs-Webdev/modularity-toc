<?php

/**
 * Plugin Name:       Modularity Table of Contents
 * Plugin URI:        https://github.com/alingsas-kommun/modularity-toc.git
 * Description:       A Table of Contents module for Modularity.
 * Version: 1.0.0
 * Author:            Consid Borås AB
 * Author URI:        https://github.com/alingsas-kommun
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-toc
 * Domain Path:       /languages
 */

// Protect agains direct file access
if (!defined('WPINC')) {
    die;
}

define('MODULARITY_TOC_PATH', plugin_dir_path(__FILE__));
define('MODULARITY_TOC_URL', plugins_url('', __FILE__));
define('MODULARITY_TOC_VIEW_PATH', MODULARITY_TOC_PATH . 'views/');
define('MODULARITY_TOC_MODULE_VIEW_PATH', plugin_dir_path(__FILE__) . 'source/php/Module/views');
define('MODULARITY_TOC_MODULE_PATH', MODULARITY_TOC_PATH . 'source/php/Module/');

add_action('init', function() {
    load_plugin_textdomain('modularity-toc', false, plugin_basename(dirname(__FILE__)) . '/languages');
}); 

// Autoload from plugin
if (file_exists(MODULARITY_TOC_PATH . 'vendor/autoload.php')) {
    require_once MODULARITY_TOC_PATH . 'vendor/autoload.php';
}
require_once MODULARITY_TOC_PATH . 'Public.php';

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-toc');
    $acfExportManager->setExportFolder(MODULARITY_TOC_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'general-settings' => 'group_69450b33eef44',
        'instance-settings' => 'group_69424998c467b',
    ));
    $acfExportManager->import();
}); 

// Modularity 3.0 ready - ViewPath for Component library
add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-toc'] = MODULARITY_TOC_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Start application
new ModularityToc\App();

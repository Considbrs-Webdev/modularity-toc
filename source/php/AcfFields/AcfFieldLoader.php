<?php

namespace ModularityToc\AcfFields;

class AcfFieldLoader
{
    /**
     * Field keys mapped to their choice loader methods
     */
    private array $fieldChoiceLoaders = [
        'field_6942499969780' => 'getSidebarChoices',
    ];

    /**
     * Constructor - register ACF hooks
     */
    public function __construct()
    {
        add_filter('acf/load_field', [$this, 'loadFieldChoices']);
        add_filter('acf/prepare_field/key=field_67d6a94cb3c02', [$this, 'hideIfAutomaticMobileInsertion']);
        add_filter('acf/prepare_field/key=field_67d6c68ab3c03', [$this, 'hideIfAutomaticMobileInsertion']);
    }

    /**
     * Hide hide_on_mobile / hide_on_desktop fields when automatic mobile insertion is enabled
     *
     * @param array|false $field
     * @return array|false
     */
    public function hideIfAutomaticMobileInsertion($field)
    {
        if (get_field('automatic_mobile_insertion', 'modularity-toc-settings')) {
            return false;
        }

        return $field;
    }

    /**
     * Load field choices based on field key
     *
     * @param array $field The ACF field array
     * @return array Modified field array
     */
    public function loadFieldChoices(array $field): array
    {
        if (!isset($field['key']) || !isset($this->fieldChoiceLoaders[$field['key']])) {
            return $field;
        }

        // Don't load choices when editing ACF field groups
        if ($this->isEditingFieldGroup()) {
            return $field;
        }

        // If no loader method is defined, return field as is
        if (!isset($this->fieldChoiceLoaders[$field['key']])) {
            return $field;
        }

        $loaderMethod = $this->fieldChoiceLoaders[$field['key']];

        if (method_exists($this, $loaderMethod)) {
            $field['choices'] = $this->$loaderMethod();
        }

        return $field;
    }

    /**
     * Check if currently editing an ACF field group
     *
     * @return bool True if editing a field group, false otherwise
     */
    private function isEditingFieldGroup(): bool
    {
        global $pagenow, $typenow;

        // Check if we're on the post edit screen for ACF field groups
        if (in_array($pagenow, ['post.php', 'post-new.php']) && $typenow === 'acf-field-group') {
            return true;
        }

        // Also check via GET/POST parameters as fallback
        $postType = $_GET['post_type'] ?? $_POST['post_type'] ?? null;
        if ($postType === 'acf-field-group') {
            return true;
        }

        // Check if editing an existing field group post
        $postId = $_GET['post'] ?? $_POST['post'] ?? null;
        if ($postId && get_post_type($postId) === 'acf-field-group') {
            return true;
        }

        return false;
    }

    /**
     * Get available sidebars as choices for the select field
     *
     * @return array Associative array of sidebar ID => sidebar name
     */
    private function getSidebarChoices(): array
    {
        $choices = [
            SidebarKey::CONTENT_AREA_TOP    => __('Content area (above article)', 'municipio'),
            SidebarKey::CONTENT_AREA        => __('Content area (below article)', 'municipio'),
            SidebarKey::CONTENT_AREA_BOTTOM => __('Main container bottom', 'municipio'),
        ];

        /**
         * Filter the available sidebar choices for the TOC field.
         *
         * Add custom entries as 'key' => 'Label' pairs. The key must
         * also be registered in the `Modularity/Module/TableOfContents/SidebarSelectorMap`
         * filter so the JS knows which DOM selector to use.
         *
         * @param array $choices Associative array of sidebar key => label.
         */
        return apply_filters('Modularity/Module/TableOfContents/SidebarChoices', $choices);
    }
}

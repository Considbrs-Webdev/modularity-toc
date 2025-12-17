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
            'content-area-top' => __('Content area (above article)', 'municipio'),
            'content-area' => __('Content area (below article)', 'municipio'),
            'content-area-bottom' => __('Main container bottom', 'municipio'),
        ];

        return $choices;
    }
}

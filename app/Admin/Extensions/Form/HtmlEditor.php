<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class HtmlEditor extends Field
{
    protected $view = 'admin::form.editor';

    protected static $js = [
        'https://cdn.jsdelivr.net/npm/tinymce@7.6.0/tinymce.min.js',
    ];

    public function render()
    {
        $config = json_encode([
            'selector' => '#' . $this->id,
            'base_url' => 'https://cdn.jsdelivr.net/npm/tinymce@7.6.0',
            'suffix' => '.min',
            'height' => 480,
            'menubar' => false,
            'branding' => false,
            'promotion' => false,
            'plugins' => 'lists link table code preview autoresize',
            'toolbar' => 'undo redo | blocks | bold italic underline strikethrough | bullist numlist | outdent indent | link table | removeformat | code preview',
            'block_formats' => 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4',
            'content_style' => 'body { font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; font-size: 14px; }',
            'convert_urls' => false,
            'relative_urls' => false,
            'entity_encoding' => 'raw',
            'valid_elements' => '*[*]',
            'extended_valid_elements' => '*[*]',
            'license_key' => 'gpl',
        ], JSON_UNESCAPED_UNICODE);

        $this->script = <<<SCRIPT
(function () {
    if (typeof tinymce === 'undefined') {
        return;
    }
    tinymce.remove('#{$this->id}');
    tinymce.init({$config});
    var \$form = \$('#{$this->id}').closest('form');
    \$form.off('submit.htmlEditor').on('submit.htmlEditor', function () {
        tinymce.triggerSave();
    });
})();
SCRIPT;

        return parent::render();
    }
}

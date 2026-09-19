<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class HtmlEditor extends Field
{
    protected $view = 'admin::form.editor';

    protected static $js = [
        'https://cdn.jsdelivr.net/npm/ckeditor4@4.22.1/ckeditor.js',
    ];

    public function render()
    {
        $config = json_encode([
            'height' => 480,
            'language' => 'ru',
            'allowedContent' => true,
            'extraAllowedContent' => '*(*);*{*}',
            'removePlugins' => 'exportpdf',
            'toolbar' => [
                ['name' => 'document', 'items' => ['Source', '-', 'Preview']],
                ['name' => 'clipboard', 'items' => ['Undo', 'Redo']],
                ['name' => 'basicstyles', 'items' => ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat']],
                ['name' => 'paragraph', 'items' => ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote']],
                ['name' => 'links', 'items' => ['Link', 'Unlink']],
                ['name' => 'insert', 'items' => ['Table', 'HorizontalRule']],
                ['name' => 'styles', 'items' => ['Format', 'Styles']],
            ],
        ], JSON_UNESCAPED_UNICODE);

        $this->script = <<<SCRIPT
(function () {
    if (typeof CKEDITOR === 'undefined') {
        return;
    }
    if (CKEDITOR.instances['{$this->id}']) {
        CKEDITOR.instances['{$this->id}'].destroy(true);
    }
    var editor = CKEDITOR.replace('{$this->id}', {$config});
    var \$form = \$('#{$this->id}').closest('form');
    \$form.on('submit', function () {
        for (var name in CKEDITOR.instances) {
            if (CKEDITOR.instances.hasOwnProperty(name)) {
                CKEDITOR.instances[name].updateElement();
            }
        }
    });
})();
SCRIPT;

        return parent::render();
    }
}

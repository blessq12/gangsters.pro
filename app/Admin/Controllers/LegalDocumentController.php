<?php

namespace App\Admin\Controllers;

use App\Enums\LegalDocumentType;
use App\Models\LegalDocument;
use App\Repositories\LegalDocumentRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LegalDocumentController extends AdminController
{
    protected $title = 'Юридические документы';

    protected function grid()
    {
        $grid = new Grid(new LegalDocument());

        $grid->model()->orderByDesc('id');

        $grid->column('id', __('ID'));
        $grid->column('type', __('Тип'))->display(function ($type) {
            return LegalDocumentType::LABELS[$type] ?? $type;
        });
        $grid->column('version', __('Версия'));
        $grid->column('title', __('Заголовок'));
        $grid->column('is_current', __('Текущая'))->bool();
        $grid->column('published_at', __('Опубликовано'));
        $grid->column('created_at', __('Создано'));

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('type', 'Тип')->select(LegalDocumentType::LABELS);
            $filter->equal('is_current', 'Текущая')->select([
                1 => 'Да',
                0 => 'Нет',
            ]);
        });

        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
        });
        $grid->disableBatchActions();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(LegalDocument::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('type', __('Тип'))->as(function ($type) {
            return LegalDocumentType::LABELS[$type] ?? $type;
        });
        $show->field('version', __('Версия'));
        $show->field('title', __('Заголовок'));
        $show->field('body_html', __('HTML'))->unescape()->as(function ($html) {
            return '<div class="legal-document-preview" style="max-width:100%;overflow:auto;border:1px solid #eee;padding:16px;background:#fff">'
                . $html
                . '</div>';
        });
        $show->field('is_current', __('Текущая'));
        $show->field('published_at', __('Опубликовано'));
        $show->field('created_at', __('Создано'));
        $show->field('updated_at', __('Обновлено'));

        $show->panel()->tools(function ($tools) {
            $tools->disableDelete();
        });

        return $show;
    }

    protected function form()
    {
        $form = new Form(new LegalDocument());

        if ($form->isEditing()) {
            $form->select('type', __('Тип'))
                ->options(LegalDocumentType::LABELS)
                ->readOnly()
                ->help('Тип нельзя сменить. Сохранение опубликует новую версию.');
            $form->display('version', __('Версия-источник'));
            $form->html(
                '<div class="alert alert-info" style="margin-bottom:15px">Сохранение опубликует <strong>новую</strong> версию этого типа. Старая запись останется в истории.</div>'
            );
        } else {
            $form->select('type', __('Тип'))
                ->options(LegalDocumentType::LABELS)
                ->required()
                ->help('При сохранении создаётся новая версия документа выбранного типа.');
        }

        $form->text('title', __('Заголовок'))->required();
        $form->htmlEditor('body_html', __('Содержимое'))
            ->required()
            ->help('Визуальный HTML-редактор. Кнопка Source — правка исходного HTML.');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }

    public function store()
    {
        return $this->publishFromRequest();
    }

    public function update($id)
    {
        $existing = LegalDocument::query()->findOrFail($id);

        return $this->publishFromRequest($existing->type);
    }

    public function destroy($id)
    {
        return response('Удаление запрещено', 405);
    }

    private function publishFromRequest(?string $forcedType = null)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'body_html' => 'required|string',
        ];

        if ($forcedType === null) {
            $rules['type'] = 'required|string|in:' . implode(',', LegalDocumentType::ALL);
        }

        $data = request()->validate($rules);
        $type = $forcedType ?? $data['type'];

        $document = app(LegalDocumentRepository::class)->publishNewVersion(
            $type,
            $data['title'],
            $data['body_html']
        );

        admin_toastr('Новая версия v' . $document->version . ' опубликована');

        return redirect(admin_url('legal-documents/' . $document->id));
    }
}

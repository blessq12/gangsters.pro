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

        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });
        $grid->disableBatchActions();

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(LegalDocument::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('type', __('Тип'));
        $show->field('version', __('Версия'));
        $show->field('title', __('Заголовок'));
        $show->field('body_html', __('HTML'));
        $show->field('is_current', __('Текущая'));
        $show->field('published_at', __('Опубликовано'));
        $show->field('created_at', __('Создано'));
        $show->field('updated_at', __('Обновлено'));

        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableDelete();
        });

        return $show;
    }

    protected function form()
    {
        $form = new Form(new LegalDocument());

        $form->select('type', __('Тип'))
            ->options(LegalDocumentType::LABELS)
            ->required()
            ->help('Создание всегда публикует новую иммутабельную версию');
        $form->text('title', __('Заголовок'))->required();
        $form->textarea('body_html', __('HTML текст'))->rows(20)->required();

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
        });

        return $form;
    }

    public function store()
    {
        $data = request()->validate([
            'type' => 'required|string|in:' . implode(',', LegalDocumentType::ALL),
            'title' => 'required|string|max:255',
            'body_html' => 'required|string',
        ]);

        $document = app(LegalDocumentRepository::class)->publishNewVersion(
            $data['type'],
            $data['title'],
            $data['body_html']
        );

        admin_toastr('Новая версия опубликована');

        return redirect(admin_url('legal-documents/' . $document->id));
    }

    public function update($id)
    {
        return response('Редактирование запрещено: создайте новую версию через «Создать»', 405);
    }

    public function destroy($id)
    {
        return response('Удаление запрещено', 405);
    }
}

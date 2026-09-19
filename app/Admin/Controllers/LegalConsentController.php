<?php

namespace App\Admin\Controllers;

use App\Models\LegalConsent;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LegalConsentController extends AdminController
{
    protected $title = 'Согласия (аудит)';

    protected function grid()
    {
        $grid = new Grid(new LegalConsent());

        $grid->model()->orderByDesc('accepted_at');

        $grid->column('id', __('ID'));
        $grid->column('consent_type', __('Тип согласия'));
        $grid->column('user_id', __('User ID'));
        $grid->column('order_id', __('Order ID'));
        $grid->column('legal_document_id', __('Документ'));
        $grid->column('document.version', __('Версия'));
        $grid->column('document.type', __('Тип документа'));
        $grid->column('ip', __('IP'));
        $grid->column('accepted_at', __('Принято'));

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(LegalConsent::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('consent_type', __('Тип согласия'));
        $show->field('user_id', __('User ID'));
        $show->field('order_id', __('Order ID'));
        $show->field('legal_document_id', __('Документ ID'));
        $show->field('document.type', __('Тип документа'));
        $show->field('document.version', __('Версия'));
        $show->field('document.title', __('Заголовок'));
        $show->field('ip', __('IP'));
        $show->field('user_agent', __('User-Agent'));
        $show->field('accepted_at', __('Принято'));
        $show->field('created_at', __('Создано'));

        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableDelete();
        });

        return $show;
    }

    protected function form()
    {
        abort(405, 'Согласия только для просмотра');
    }
}

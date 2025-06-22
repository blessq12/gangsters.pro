<?php

namespace App\Admin\Controllers;

use App\Models\CompanyLegal;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CompanyLegalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Юридические данные компаний';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CompanyLegal());

        $grid->column('id', __('ID'));
        $grid->column('company.name', __('Компания'));
        $grid->column('legal_form', __('Правовая форма'));
        $grid->column('legal_email', __('Юр. email'));
        $grid->column('owner', __('Владелец'));
        $grid->column('inn', __('ИНН'));
        $grid->column('ogrn', __('ОГРН'));
        $grid->column('created_at', __('Создано'));
        $grid->column('updated_at', __('Обновлено'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CompanyLegal::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('company.name', __('Компания'));
        $show->field('legal_form', __('Правовая форма'));
        $show->field('legal_email', __('Юр. email'));
        $show->field('owner', __('Владелец'));
        $show->field('inn', __('ИНН'));
        $show->field('ogrn', __('ОГРН'));
        $show->field('okpo', __('ОКПО'));
        $show->field('kpp', __('КПП'));
        $show->field('registration_address', __('Адрес регистрации'));
        $show->field('created_at', __('Создано'));
        $show->field('updated_at', __('Обновлено'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CompanyLegal());

        $form->select('company_id', __('Компания'))->options(function () {
            return \App\Models\Company::all()->pluck('name', 'id');
        });
        $form->text('legal_form', __('Правовая форма'))->required();
        $form->email('legal_email', __('Юр. email'))->required();
        $form->text('owner', __('Владелец'))->required();
        $form->text('inn', __('ИНН'))->required();
        $form->text('ogrn', __('ОГРН'))->required();
        $form->text('okpo', __('ОКПО'))->required();
        $form->text('kpp', __('КПП'))->required();
        $form->text('registration_address', __('Адрес регистрации'))->required();

        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Company;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CompanyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Компания';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Company());

        $grid->column('id', __('Идентификатор'));
        $grid->column('name', __('Название'));
        $grid->column('description', __('Описание'));
        $grid->column('phone', __('Телефон'));
        $grid->column('phone_additional', __('Дополнительный телефон'));
        $grid->column('email_address', __('Электронная почта'));
        $grid->column('work_shedule', __('График работы'))->display(function ($workShedule) {
            return '<a href="' . admin_url('work-shedules') . '">Shedules</a>';
        });
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
        $show = new Show(Company::findOrFail($id));

        $show->field('id', __('Идентификатор'));
        $show->field('name', __('Название'));
        $show->field('description', __('Описание'));
        $show->field('country', __('Страна'));
        $show->field('state', __('Область'));
        $show->field('city', __('Город'));
        $show->field('street', __('Улица'));
        $show->field('house', __('Дом'));
        $show->field('phone', __('Телефон'));
        $show->field('phone_additional', __('Дополнительный телефон'));
        $show->field('email_address', __('Электронная почта'));
        $show->field('vk', __('Вконтакте'));
        $show->field('inst', __('Instagram'));
        $show->field('logo', __('Логотип'))->image();
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
        $form = new Form(new Company());

        $form->text('name', __('Название'));
        $form->text('description', __('Описание'));
        $form->text('country', __('Страна'));
        $form->text('state', __('Область'));
        $form->text('city', __('Город'));
        $form->text('street', __('Улица'));
        $form->text('house', __('Здание'));
        $form->text('phone', __('Телефон'))->inputmask(['mask' => '+7 (999) 999-99-99']);
        $form->text('phone_additional', __('Дополнительный телефон'))->inputmask(['mask' => '+7 (999) 999-99-99']);
        $form->image('logo', __('Логотип'))->uniqueName();
        $form->text('email_address', __('Email адрес'));
        $form->text('vk', 'Вконтакте');
        $form->text('inst', 'Instagram');

        return $form;
    }
}

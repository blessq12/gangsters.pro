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

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('description', __('Description'));

        $grid->column('phone', __('Phone'));
        $grid->column('phone_additional', __('Phone additional'));
        $grid->column('email_address', __('Email address'));

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

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('description', __('Description'));
        $show->field('country', __('Country'));
        $show->field('state', __('State'));
        $show->field('city', __('City'));
        $show->field('street', __('Street'));
        $show->field('house', __('House'));
        $show->field('phone', __('Phone'));
        $show->field('phone_additional', __('Phone additional'));
        $show->field('email_address', __('Email address'));
        $show->field('vk', 'Вконтакте');
        $show->field('inst', 'Instagram');
        $show->field('logo', __('Logo'))->image();
        $show->field('updated_at', __('Updated at'));

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

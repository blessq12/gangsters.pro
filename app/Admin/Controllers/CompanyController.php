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
        $show->field('logo', __('Logo'))->image();
        $show->field('created_at', __('Created at'));
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

        $form->text('name', __('Name'));
        $form->text('description', __('Description'));
        $form->text('country', __('Country'));
        $form->text('state', __('State'));
        $form->text('city', __('City'));
        $form->text('street', __('Street'));
        $form->text('house', __('House'));
        $form->mobile('phone', __('Phone'));
        $form->mobile('phone_additional', __('Phone additional'));
        $form->image('logo', __('Logo'))->name(time());
        $form->text('email_address', __('Email address'));

        $form->submitted(function ($form) {
            // $form->model()->legals()->create([
            //     'legal_form' => $form->input('legal_form'),
            //     'legal_email' => $form->input('legal_email'),
            //     'owner' => $form->input('owner'),
            //     'inn' => $form->input('inn'),
            //     'ogrn' => $form->input('ogrn'),
            //     'okpo' => $form->input('okpo'),
            //     'kpp' => $form->input('kpp'),
            //     'registration_address' => $form->input('registration_address'),
            // ]);
        });

        return $form;
    }
}

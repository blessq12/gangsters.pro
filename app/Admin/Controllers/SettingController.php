<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\Setting;

class SettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Setting';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Setting());

        $grid->column('id', __('Id'));
        $grid->column('frontpad_api_key', __('Frontpad api key'));
        $grid->column('use_coin_system', __('Use coin system'));
        $grid->column('coin_system_percent', __('Coin system percent'));
        $grid->column('use_discount_system', __('Use discount system'));
        $grid->column('discount_system_percent', __('Discount system percent'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Setting::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('frontpad_api_key', __('Frontpad api key'));
        $show->field('use_coin_system', __('Use coin system'));
        $show->field('coin_system_percent', __('Coin system percent'));
        $show->field('use_discount_system', __('Use discount system'));
        $show->field('discount_system_percent', __('Discount system percent'));
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
        $form = new Form(new Setting());

        $form->text('frontpad_api_key', __('Frontpad api key'));
        $form->switch('use_coin_system', __('Use coin system'));
        $form->number('coin_system_percent', __('Coin system percent'));
        $form->switch('use_discount_system', __('Use discount system'));
        $form->number('discount_system_percent', __('Discount system percent'));

        return $form;
    }
}

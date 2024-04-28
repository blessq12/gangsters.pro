<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Заказы';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'))->display(function ($val) {
            return Carbon::parse($val)->format('d.m.Y');
        });
        $grid->column('user_id', __('User type'))->display(function ($val) {
            return $val ?
                "<span class='badge' style='background: #008d4c'>Авторизован</span>" :
                "<span class='badge'>Не авторизован</span>";
        });
        $grid->column('name', __('Name'));
        $grid->column('tel', __('Tel'));
        $grid->column('street', __('Street'));
        $grid->column('house', __('House'));
        $grid->column('total', __('Total'));
        $grid->column('delivery', __('Delivery'));

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
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('user_id', __('User id'));
        $show->field('name', __('Name'));
        $show->field('tel', __('Tel'));
        $show->field('street', __('Street'));
        $show->field('house', __('House'));
        $show->field('building', __('Building'));
        $show->field('staircase', __('Staircase'));
        $show->field('floor', __('Floor'));
        $show->field('apartment', __('Apartment'));
        $show->field('total', __('Total'));
        $show->field('delivery', __('Delivery'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order());

        $form->number('user_id', __('User id'));
        $form->text('name', __('Name'));
        $form->text('tel', __('Tel'));
        $form->text('street', __('Street'));
        $form->text('house', __('House'));
        $form->text('building', __('Building'));
        $form->text('staircase', __('Staircase'));
        $form->text('floor', __('Floor'));
        $form->text('apartment', __('Apartment'));
        $form->text('total', __('Total'));
        $form->switch('delivery', __('Delivery'));

        return $form;
    }
}

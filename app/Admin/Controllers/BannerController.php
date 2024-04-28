<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Banner';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner());

        $grid->column('id', __('Id'));
        $grid->column('header', __('Header'));
        $grid->column('subheader', __('Subheader'));
        $grid->column('image', __('Image'))->display(function ($image) {
            return "<img src='/uploads/{$image}' width='auto' height='70' class='img-fluid'>";
        });
        $grid->column('created_at', __('Created at'))->display(function ($value) {
            return Carbon::parse($value)->format('Y.m.d');
        });
        $grid->column('visible', __('Visible'));

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
        $show = new Show(Banner::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('header', __('Header'));
        $show->field('subheader', __('Subheader'));
        $show->field('image', __('Image'))->image();
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Banner());

        $form->text('header', __('Header'));
        $form->textarea('subheader', __('Subheader'));
        $form->image('image', __('Image'))->uniqueName();
        $form->switch('visible', __('Visible'));

        return $form;
    }
}

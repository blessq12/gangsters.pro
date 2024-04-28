<?php

namespace App\Admin\Controllers;

use App\Models\Story;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class StoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Актуальное';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Story());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('image', __('Image'))->display(function ($image) {
            return "<img src='/uploads/{$image}' width='70' height='70' class='img-fluid'>";
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
        $show = new Show(Story::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('image', __('Image'))->image();
        $show->field('visible', __('Visible'))->as(function ($visible) {
            return $visible ? 'Да' : 'Нет';
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Story());

        $form->text('name', __('Name'));
        $form->image('image', __('Image'))->uniqueName();
        $form->switch('visible', __('Visible'));

        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerController extends AdminController
{
    protected $title = 'Баннеры';

    protected function grid()
    {
        $grid = new Grid(new Banner());
        $grid->column('id', __('ID'))->sortable();
        $grid->column('image', __('Изображение'))->image();
        $grid->column('title', __('Заголовок'));
        $grid->column('description', __('Описание'));
        $grid->column('created_at', __('Дата создания'))
            ->display(function ($value) {
                return \Carbon\Carbon::parse($value)->format('d.m.Y H:i');
            });
        $grid->column('updated_at', __('Дата обновления'))
            ->display(function ($value) {
                return \Carbon\Carbon::parse($value)->format('d.m.Y H:i');
            });

        return $grid;
    }

    protected function detail($id)
    {
        $show = new Show(Banner::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('image', __('Изображение'))->image();
        $show->field('title', __('Заголовок'));
        $show->field('description', __('Описание'));

        return $show;
    }

    protected function form()
    {
        $form = new Form(new Banner());

        $form->image('image', 'Изображение')
            ->move('banners')
            ->uniqueName()
            ->fit(1290, 380)
            ->required();

        $form->text('title', 'Заголовок');
        $form->text('description', 'Описание');

        return $form;
    }
}

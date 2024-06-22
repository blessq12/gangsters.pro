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
    protected $title = 'Баннеры';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner());

        $grid->column('id', __('ID'));
        $states = [
            'on' => ['text' => 'Да'],
            'off' => ['text' => 'Нет'],
        ];
        $grid->visible('Доступность')->switch($states);
        $grid->column('image', __('Изображение'))->image(null, null, 50);
        $grid->column('created_at', __('Создан'))->display(function ($value) {
            return Carbon::parse($value)->format('Y.m.d');
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
        $show = new Show(Banner::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('visible', __('Доступен'))->as(function ($value) {
            return $value ?
                'Да' :
                'Нет';
        });
        $show->field('image', __('Изображение'))->image(null, null, 150);
        $show->field('created_at', __('Создан'))->as(function ($ts) {
            return Carbon::parse($ts)->format('Y/m/d H:i');
        });
        // $show->field('updated_at', __('Updated at'));

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

        $form->switch('visible', __('Доступонсть'));
        $form->image('image', __('Изображение'))
            ->uniqueName()
            ->move('banners')
            ->resize(1920, 1080)
            ->encode('jpg', 85);
        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Story;
use Carbon\Carbon;
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

        $grid->column('id', __('ID'));
        $states = [
            'on' => ['text' => 'Да'],
            'off' => ['text' => 'Нет'],
        ];
        // $grid->column('visible', 'Доступность')->switch($states);
        $grid->column('non_hide', 'Не удалять')->switch($states);
        $grid->column('name', __('Заголовок'))->editable();
        $grid->column('image', __('Изображение'))->image(null, null, 50);
        $grid->column('start_time', 'Опубликован')->display(function ($val) {
            return Carbon::parse($val)->format('Y/m/d H:i');
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
        $show = new Show(Story::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('visible', 'Доступен')->as(function ($value) {
            return $value ?
                'Да' :
                'Нет';
        });
        $show->field('name', __('Заголовок'));
        $show->field('created_at', __('Создан'));
        // $show->field('updated_at', __('Updated at'));
        $show->field('image', __('Изображение'))->image(null, null, 100);

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

        $form->text('name', __('Заголовок'));
        $form->hidden('visible', __('Доступен'))->value(true);
        $form->switch('non_hide', __('Не скрывать'));
        $form->image('image', __('Изображение'))
            ->thumbnail('story', null, 256)
            ->uniqueName()
            ->fit(1080, 1920)
            ->encode('jpeg', 80)
            ->move('story/');
        $form->hidden('start_time', 'start_time')->default(now());
        $form->hidden('end_time', 'end_time')->default(now()->addDay(1));
        $form->hidden('created_at', 'Создан');
        $form->hidden('created_at', 'Обновлен');
        return $form;
    }
}

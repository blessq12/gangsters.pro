<?php

namespace App\Admin\Controllers;

use App\Models\ProductCategory;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Str;

class ProductCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Категории товаров';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductCategory());

        $grid->column('id', __('ID'));
        $states = [
            'on' => ['text' => 'Да'],
            'off' => ['text' => 'Нет'],
        ];
        $grid->visible('Доступность')->switch($states);
        $grid->column('name', __('Название'))->editable();
        // $grid->column('uri', __('URI (Уникальный идентификатор)'));
        // $grid->column('created_at', __('Создан'));
        $grid->column('product', 'Товары')
            ->display(function () {
                return $this->products()->count() . ' шт.';
            });
        $grid->column('updated_at', __('Обновлен'))->display(function ($timestamp) {
            return Carbon::parse($timestamp)->format('Y/m/d H:i');
        });

        $grid->filter(function ($filter) {
            $filter->by('id', __('Id'));
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
        $show = new Show(ProductCategory::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('uri', __('Идентификатор'));
        $show->field('visible', __('Доступность'))->as(function ($val) {
            return $val ? 'Категория доступна' : 'Категория недоступна';
        })->label();
        $show->field('name', __('Название категории'));
        $show->field('created_at', __('Создан'))->as(function ($ts) {
            return Carbon::parse($ts)->format('Y/m/d H:i');
        });
        $show->field('updated_at', __('Обновлен'))->as(function ($ts) {
            return Carbon::parse($ts)->format('Y/m/d H:i');
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
        $form = new Form(new ProductCategory());

        $form->display('uri', __('Идентификатор'))->value(Str::random(16));
        $form->switch('visible', __('Доступность'))->default(false);
        $form->text('name', __('Название категории'));

        return $form;
    }
}

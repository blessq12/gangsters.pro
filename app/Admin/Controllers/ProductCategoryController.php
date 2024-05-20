<?php

namespace App\Admin\Controllers;

use App\Models\ProductCategory;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

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
        $grid->column('uri', __('URI (Уникальный идентификатор)'));
        $grid->column('created_at', __('Создан'));
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

        $show->field('id', __('Id'));
        $show->field('uri', __('Uri'));
        $show->field('visible', __('Visible'));
        $show->field('name', __('Name'));
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
        $form = new Form(new ProductCategory());

        $form->text('uri', __('Uri'));
        $form->switch('visible', __('Visible'))->default(1);
        $form->text('name', __('Name'));

        return $form;
    }
}

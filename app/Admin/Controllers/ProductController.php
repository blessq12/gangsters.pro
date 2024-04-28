<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Product';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->column('id', __('Id'));
        $grid->column('visible', __('Visible'));
        $grid->column('product_category_id', __('Product category id'))->display(function ($category_id) {
            return ProductCategory::find($category_id)->name;
        });
        $grid->column('name', __('Name'));
        $grid->column('weight', __('Weight'));
        $grid->column('price', __('Price'));


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
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('visible', __('Visible'));
        $show->field('product_category_id', __('Product category id'));
        $show->field('name', __('Name'));
        $show->field('hit', __('Hit'));
        $show->field('spicy', __('Spicy'));
        $show->field('kidsAllow', __('KidsAllow'));
        $show->field('onion', __('Onion'));
        $show->field('garlic', __('Garlic'));
        $show->field('consist', __('Consist'));
        $show->field('weight', __('Weight'));
        $show->field('price', __('Price'));
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
        $form = new Form(new Product());

        $form->switch('visible', __('Visible'))->default(1);
        $form->number('product_category_id', __('Product category id'));
        $form->text('name', __('Name'))->default('Название не задано');
        $form->switch('hit', __('Hit'));
        $form->switch('spicy', __('Spicy'));
        $form->switch('kidsAllow', __('KidsAllow'));
        $form->switch('onion', __('Onion'));
        $form->switch('garlic', __('Garlic'));
        $form->textarea('consist', __('Consist'));
        $form->text('weight', __('Weight'));
        $form->text('price', __('Price'));

        return $form;
    }
}

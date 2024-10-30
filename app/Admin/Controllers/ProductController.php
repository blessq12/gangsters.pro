<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Show;

use function Laravel\Prompts\form;

class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Товары';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->filter(function (Filter $filter) {
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->like('name', 'Название');
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('product_category_id', 'Категория')->select(ProductCategory::all()->pluck('name', 'id'));
            });
        });

        // $grid->column('id', __('Id'))->sortable();

        $grid->visible('Доступность')->switch([
            'on' => ['text' => 'Да'],
            'off' => ['text' => 'Нет'],
        ]);

        $grid->column('product_category_id', __('Категория'))->display(function ($category_id) {
            return ProductCategory::find($category_id)->name;
        })->sortable();

        $grid->column('sku', __('Артикул'))->display(function ($sku) {
            return $sku ?? 'Нет артикула';
        });



        $grid->imgs('Изображения')->display(function ($images) {
            return $images[0]['path'] ?? null;
        })->image(null, 50, 50);

        $grid->column('name', __('Имя'))->editable();
        $grid->column('weight', __('Вес(нетто)'))->editable();
        $grid->column('price', __('Цена'))->editable();

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
        $show->field('sku', 'Артикул');
        $show->field('visible', __('Доступность'))->as(function ($val) {
            return $val ? 'Да' : 'Нет';
        });
        $show->imgs('ИИзображения')->as(function ($images) {
            return $images->map(function ($image) {
                return $image->path;
            });
        })->image(null, null, 80);
        $show->field('product_category_id', __('Категория'))->as(function ($id) {
            return ProductCategory::find($id)->name;
        });
        $show->field('name', __('Название'));
        $show->field('hit', __('Хит'))->as(function ($val) {
            return $val ? 'Да' : 'Нет';
        });;
        $show->field('spicy', __('Острый'))->as(function ($val) {
            return $val ? 'Да' : 'Нет';
        });;
        $show->field('kidsAllow', __('Можно детям'))->as(function ($val) {
            return $val ? 'Да' : 'Нет';
        });;
        $show->field('onion', __('Есть лук'))->as(function ($val) {
            return $val ? 'Да' : 'Нет';
        });;
        $show->field('garlic', __('Есть чеснок'))->as(function ($val) {
            return $val ? 'Да' : 'Нет';
        });;
        $show->field('consist', __('Состав'));
        $show->field('weight', __('Вес (нетто)'));
        $show->field('price', __('Цена'));
        $show->field('created_at', __('Создан'));
        $show->field('updated_at', __('Обновлен'));

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

        $form->switch('visible', __('Доступность'))->default(1);
        $form->text('sku', 'Артикул')->placeholder('Артикул присваивается автоматически')->readonly();
        $form->select('product_category_id', __('Категория'))
            ->options($this->categories())
            ->rules('required|exists:product_categories,id', [
                'required' => 'Категория обязательна для заполнения.',
                'exists' => 'Выбранная категория не существует.',
            ]);
        $form->multipleImage('imgs', __('Изображения'))
            ->pathColumn('path')
            ->uniqueName()
            ->thumbnail([
                'small' => [null, 256],
                'medium' => [null, 512],
                'large' => [null, 1024],
            ])
            ->removable();
        $form->text('name', __('Название'))->placeholder('Название не задано')->rules('required', [
            'required' => 'Название обязательно для заполнения.',
        ]);

        $form->switch('hit', __('Хит'));
        $form->switch('spicy', __('Острый'));
        $form->switch('kidsAllow', __('Можно детям'));

        $form->switch('onion', __('Есть лук'));
        $form->switch('garlic', __('Есть чеснок'));

        $form->textarea('consist', __('Состав'));
        $form->text('weight', __('Вес (нетто)'))->rules('required', [
            'required' => 'Вес обязателен для заполнения.',
        ]);
        $form->text('price', __('Цена'))->rules('required', [
            'required' => 'Цена обязательна для заполнения.',
        ]);

        return $form;
    }


    private function categories()
    {
        $categories = ProductCategory::all();
        $result = [];
        foreach ($categories as $category) {
            $result[$category->id] = $category->name;
        }
        return $result;
    }
}

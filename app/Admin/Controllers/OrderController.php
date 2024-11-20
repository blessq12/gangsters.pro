<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Models\User;
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
        $grid->model()->orderBy('id', 'desc');



        $grid->column('id', __('ID'))->sortable();
        $grid->column('created_at', __('Создан'))->display(function ($val) {
            return Carbon::parse($val)->format('d.m.Y');
        });
        $grid->column('user_id', __('Тип заказа'))->display(function ($val) {
            return $val ?
                "Авторизован" :
                "Не авторизован";
        })->label('success');

        $grid->column('additional', __('Подробности заказа'))
            ->display(function ($v) {
                return 'Смотреть ';
            })
            ->modal('Подробнее', function ($model) {
                return view('components.front.adModal', [
                    'order' => $model
                ]);
            });
        $grid->column('total', __('Сумма'))->display(function ($val) {
            return '<b>' . number_format($val, 2, '.', '') . ' руб.</b>';
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
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('ID заказа'));

        $show->field('created_at', __('Создан'));
        $show->field('updated_at', __('Обновлен'));
        $show->divider();
        $show->field('delivery', __('Доставка'))->as(function ($val) {
            return $val ?
                "<span class='badge' style='background: #008d4c'>Доставка</span>" :
                "<span class='badge'>Самовывоз</span>";
        })->unescape();
        $show->field('user_id', __('Тип заказа'))->as(function ($val) {
            $user = User::find($val);
            if (!$val) {
                return "<span class='badge' style='background: red'>Не авторизован</span>";
            } else {
                return "<span class='badge' style='background: green'>Авторизован</span>" . "<span style='margin-left: 20px'>Пользователь: ID: <b>{$user->id}</b>, Номер телефона: <b>{$user->tel}</b></span>";
            }
        })->unescape();
        $show->field('name', __('Имя в заказе'));
        $show->field('tel', __('Номер телефона'));
        $show->field('street', __('Улица'))->as(function ($value) {
            return $value ? $value : 'Не указано';
        });
        $show->field('house', __('Дом'))->as(function ($value) {
            return $value ? $value : 'Не указано';
        });
        $show->field('building', __('Строение'))->as(function ($value) {
            return $value ? $value : 'Не указано';
        });
        $show->field('staircase', __('Подъезд'))->as(function ($value) {
            return $value ? $value : 'Не указано';
        });
        $show->field('floor', __('Этаж'))->as(function ($value) {
            return $value ? $value : 'Не указано';
        });
        $show->field('apartment', __('Квартира'))->as(function ($value) {
            return $value ? $value : 'Не указано';
        });
        $show->field('total', __('Общая сумма'))->as(function ($value) {
            return $value . ' руб.';
        });;


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

        $form->display('user_id', 'ID Пользователя');

        $form->divider();
        $form->text('name', __('Имя'));
        $form->text('tel', __('Номер телефона'));
        $form->text('street', __('Улица'));
        $form->text('house', __('Дом'));
        $form->text('building', __('Строение'));
        $form->text('staircase', __('Подъезд'));
        $form->text('floor', __('Этаж'));
        $form->text('apartment', __('Квартира'));
        $form->text('total', __('Общая сумма'));
        $form->switch('delivery', __('Delivery'));

        return $form;
    }
}

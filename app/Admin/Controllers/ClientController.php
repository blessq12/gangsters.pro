<?php

namespace App\Admin\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ClientController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Клиенты';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->column('id', __('ID'));
        $grid->column('name', __('Имя'))->editable();
        $grid->column('email', __('Email адрес'))->editable();
        $grid->column('tel', __('Номер телефона'))->editable();
        $grid->column('coins', __('Койны'))->editable();
        $grid->column('created_at', __('Создан'))->display(function ($ts) {
            return Carbon::parse($ts)->format('Y/m/d H:i');
        });

        $grid->filter(function ($filter) {
            $filter->like('name', __('Name'));
            $filter->equal('tel', __('Tel'));
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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Имя'));
        $show->field('email', __('Email адрес'));
        $show->field('tel', __('Номер телефона'));
        $show->field('coins', __('Койны'));
        $show->field('dob', 'День рождения')->as(function ($ts) {
            if (!$ts) {
                return "Дата рождения не указана";
            } else {
                return Carbon::parse($ts)->format('Y/m/d H:i');
            }
        });
        // $show->field('email_verified_at', __('Email verified at'));
        // $show->field('password', __('Password'));
        // $show->field('remember_token', __('Remember token'));
        $show->field('created_at', __('Созан'))->as(function ($ts) {
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
        $form = new Form(new User());

        $form->text('name', __('Имя'));
        $form->email('email', __('Email адрес'));
        $form->text('tel', __('Номер телефона'));
        $form->text('coins', __('Койны'));
        // $form->datetime('email_verified_at', __('Email verified at'))->default(date('Y-m-d H:i:s'));
        // $form->display('password', __('Пароль'));
        // $form->text('remember_token', __('Remember token'));
        $form->datetime('dob', __('Дата рождения'));

        return $form;
    }
}

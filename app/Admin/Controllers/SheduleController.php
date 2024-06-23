<?php

namespace App\Admin\Controllers;

use App\Models\WorkShedule;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;

class SheduleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'WorkShedule';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {

        $grid = new Grid(new WorkShedule());

        $grid->disableFilter();
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableColumnSelector();

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('<a href="' . admin_url('companies') . '" class="btn btn-default"><i class="fa fa-arrow-left" style="margin-right: 5px;"></i>Назад к компании</a>');
        });

        // $grid->column('id', __('Id'))->editable();
        // $grid->column('company_id', __('Company id'))->editable();
        $grid->column('day', __('День'));
        $grid->column('open_time', __('Время открытия'))->editable('time');
        $grid->column('close_time', __('Время закрытия'))->editable('time');
        $grid->column('day_off', __('Выходной'))->switch();
        // $grid->column('created_at', __('Created at'))->editable();
        // $grid->column('updated_at', __('Updated at'))->editable();
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return \Encore\Admin\Form
     */
    protected function form()
    {
        $form = new \Encore\Admin\Form(new WorkShedule());

        $form->select('company_id', __('Компания'))->options(\App\Models\Company::all()->pluck('name', 'id'))->required();
        $form->text('day', __('День'))->required();
        $form->time('open_time', __('Время открытия'))->required();
        $form->time('close_time', __('Время закрытия'))->required();
        $form->switch('day_off', __('Выходной'))->default(false);

        return $form;
    }
}

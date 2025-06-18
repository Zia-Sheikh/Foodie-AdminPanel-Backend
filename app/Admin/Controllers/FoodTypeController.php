<?php

namespace App\Admin\Controllers;

use App\Models\FoodType;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FoodTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Food Type';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FoodType());

        $grid->column('id', __('Id'));
        $grid->column('type_name', __('Type Name'));
        $grid->column('icon', __('Icon'))->image();
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } if ($status == 2) {
                return "<span class='label label-danger'>$status_name</span>";
            } 
        });
       
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableActions();

        $grid->filter(function ($filter) {
            //Get All status
            $statuses = Status::pluck('status_name', 'id');
    
            $filter->like('type_name', __('Type Name'));
            $filter->equal('status', __('Status'))->select($statuses);
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new FoodType());
        $statuses = Status::pluck('status_name', 'id');

        $form->text('type_name', __('Type Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->text('type_name_ar', __('Type Name Ar'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->image('icon', __('Icon'))->move('food_types')->uniqueName()->rules(function ($form) {
            return 'required|max:150';
        });
        $form->select('status', __('Status'))->options(Status::where('slug','general')->pluck('status_name','id'))->rules(function ($form) {
            return 'required';
        });
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete(); 
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });

        return $form;
    }
}

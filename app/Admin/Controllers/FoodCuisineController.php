<?php

namespace App\Admin\Controllers;

use App\Models\FoodCuisine;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FoodCuisineController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Food Cuisine';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FoodCuisine());

        $grid->column('id', __('Id'));
        $grid->column('cuisine_name', __('Cuisine Name'));
        $grid->column('cuisine_image', __('Cuisine Image'))->image();
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } if ($status == 2) {
                return "<span class='label label-danger'>$status_name</span>";
            } 
        });



        $grid->disableExport();
        $grid->actions(function ($actions) {
                $actions->disableView();
            });
        $grid->filter(function ($filter) {
            //Get All status
        $statuses = Status::pluck('status_name', 'id');

        $filter->like('cuisine_name', __('Cuisine name'));
        $filter->like('cuisine_image', __('Cuisine image'));
        $filter->equal('status', __('Status'))->select($statuses);

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
        $show = new Show(FoodCuisine::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('cuisine_name', __('Cuisine name'));
        $show->field('cuisine_image', __('Cuisine image'));
        $show->field('status', __('Status'));
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
        $form = new Form(new FoodCuisine());
        $statuses = Status::pluck('status_name', 'id');


        $form->text('cuisine_name', __('Cuisine Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->text('cuisine_name_ar', __('Cuisine Name Ar'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->image('cuisine_image', __('Cuisine Image'))->uniqueName()->move('cuisines')->rules('required');
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

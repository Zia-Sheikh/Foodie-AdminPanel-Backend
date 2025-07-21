<?php

namespace App\Admin\Controllers;

use App\Models\Tax;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TaxController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Tax';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Tax());

        $grid->column('id', __('Id'));
        $grid->column('tax_name', __('Tax Name'));
        $grid->column('percentage', __('Percentage'));
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } else {
                return "<span class='label label-danger'>$status_name</span>";
            }
            });
      
        $grid->disableExport();
        if(env('MODE') == 'DEMO'){
            $grid->disableCreateButton();
            $grid->disableActions();
        }else{
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
        }

        $grid->filter(function ($filter) {
        //Get All status

        $filter->like('tag_name', __('Tag name'));



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
        $show = new Show(Tax::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('tag_name', __('Tag name'));
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
        $form = new Form(new Tax());
        $statuses = Status::where('slug','general')->pluck('status_name','id');

        $form->text('tax_name', __('Tax Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->text('percentage', __('Percentage'))->rules(function ($form) {
            return 'required|';
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

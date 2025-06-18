<?php

namespace App\Admin\Controllers;

use App\Models\FaqCategory;
use App\Models\UserType;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FaqCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Faq Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FaqCategory());

        $grid->column('id', __('Id'));
        $grid->column('faq_type', __('Faq Type'))->display(function($user_types){
            $user_types = UserType::where('id',$user_types)->value('user');
            return $user_types;
        });
        $grid->column('icon', __('Icon'))->image();
        $grid->column('category_name', __('Category Name'));
        $grid->column('description', __('Description'));
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


        $filter->like('category_name', __('Category name'));
        $filter->like('description', __('Description'));
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
        $show = new Show(FaqCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('icon', __('Icon'));
        $show->field('category_name', __('Category name'));
        $show->field('description', __('Description'));
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
        $form = new Form(new FaqCategory());
        $statuses = Status::pluck('status_name', 'id');
        $user_types = UserType::pluck('user', 'id');

        $form->select('faq_type', __('Faq Type'))->options($user_types)->rules(function ($form) {
            return 'required';
        });
        $form->image('icon', __('Icon'))->uniqueName()->move('faq_categories')->rules('required');
        $form->text('category_name', __('Category Name'))->rules('required|max:150');
        $form->text('description', __('Description'))->rules('required');
        $form->text('category_name_ar', __('Category Name Ar'))->rules('required|max:150');
        $form->text('description_ar', __('Description Ar'))->rules('required');
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

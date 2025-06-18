<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Status;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;

class CategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category());
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('created_by','=',Admin::user()->id);
        }
        $grid->column('id', __('Id'));
        if(Admin::user()->isAdministrator()){
            $grid->column('created_by', __('Created By'))->display(function($created_by){
            if ($created_by == 1) {
                return "Admin";
            }else{
                return "Restaurant";
            }
        });
        }
        $grid->column('category_name', __('Category Name'));
        $grid->column('category_image', __('Category Image'))->image();
        $grid->column('tax', __('Tax'));
        if(Admin::user()->isAdministrator()){
            $grid->column('recommended_by_admin', __('Recommended By Admin'))->display(function($status){
            if ($status == 1) {
                return "<span class='label label-success'>Yes</span>";
            } if ($status == 0) {
                return "<span class='label label-danger'>No</span>";
            } 
        });
        }
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

        /*if(Admin::user()->isRole('restaurant')){
            }else{
                $filter->like('created_by', 'Created_by')->select($restaurants);
            }*/
        $filter->like('category_name', 'Category Name');
        $filter->equal('status', 'Status')->select($statuses);

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
        $show = new Show(Category::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('category_name', __('Category name'));
        $show->field('category_image', __('Category image'));
        $show->field('status', __('Status'));
        $show->field('description', __('Description'));
        $show->field('restaurant_id', __('Restaurant id'));
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
        $form = new Form(new Category());
        $statuses = Status::pluck('status_name', 'id');
      
        
        $form->hidden('created_by')->value(Admin::user()->id);
        
        $form->text('category_name', __('Category Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->textarea('description', __('Description'))->rules('required');
        $form->text('category_name_ar', __('Category Name Ar'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->textarea('description_ar', __('Description Ar'))->rules('required');
        $form->image('category_image', __('Category Image'))->uniqueName()->move('categories')->rules('required');

        
        if(!Admin::user()->isAdministrator()){
            $form->hidden('recommended_by_admin')->default('0');
        }else{
            $form->select('recommended_by_admin', __('Recommended By Admin'))->options(['1' => 'Yes', '0'=> 'No'])->default('1')->rules(function ($form) {
                return 'required';
            });
        }
        $form->text('tax', __('Tax'))->rules(function ($form) {
            return 'required';
        });
        $form->select('status', __('Status'))->options(Status::where('slug','general')->pluck('status_name','id'))->rules(function ($form) {
            return 'required';
        });
        $form->saving(function ($form) {
           if(!$form->restaurant_id){
              $form->restaurant_id = 0;
           }
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

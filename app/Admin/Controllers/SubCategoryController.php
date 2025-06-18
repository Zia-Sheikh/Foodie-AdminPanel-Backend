<?php

namespace App\Admin\Controllers;

use App\Models\SubCategory;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;

class SubCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Sub Category';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SubCategory());
        if(Admin::user()->isRole('restaurant')){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }

        $grid->column('id', __('Id'));
        $grid->column('sub_category_name', __('Sub Category Name'));
        $grid->column('sub_category_image', __('Sub Category Image'))->image();
        //$grid->column('description', __('Description'));
        $grid->column('restaurant_id', __('Restaurant '))->display(function($restaurant_id){
            $restaurant_name = Restaurant::where('id',$restaurant_id)->value('restaurant_name');
            return $restaurant_name;
        });
     
        $grid->disableExport();
        $grid->actions(function ($actions) {
                $actions->disableView();
            });
        $grid->filter(function ($filter) {
            //Get All status
        $restaurants = Restaurant::pluck('restaurant_name', 'id');

            if(Admin::user()->isRole('restaurant')){
            }else{
                $filter->like('restaurant_id', 'restaurant')->select($restaurants);
            }
            $filter->like('sub_category_name', 'Sub Category Name');
            

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
        $show = new Show(SubCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('sub_category_name', __('Sub category name'));
        $show->field('sub_category_image', __('Sub category image'));
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
        $form = new Form(new SubCategory());
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');
        
        if(Admin::user()->isRole('restaurant')){
            $form->hidden('restaurant_id')->value($restaurant_id);
        }else{
            $form->select('restaurant_id', __('Restaurant '))->options($restaurants)->rules(function ($form) {
            return 'required';
        });
        }
        $form->text('sub_category_name', __('Sub Category Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->image('sub_category_image', __('Sub Category Image'))->uniqueName()->move('sub_categories')->rules('required');
        $form->textarea('description', __('Description'))->rules('required');

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

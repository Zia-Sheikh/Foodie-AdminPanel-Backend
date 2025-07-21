<?php

namespace App\Admin\Controllers;

use App\Models\RestaurantCategory;
use App\Models\Category;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;

class RestaurantCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Restaurant Categories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RestaurantCategory());
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }
        $grid->column('id', __('Id'));
        $grid->column('restaurant_id', __('Restaurant'))->display(function($restaurant_id){
            $restaurant_name = Restaurant::where('id',$restaurant_id)->value('restaurant_name');
            return $restaurant_name;
        });
        $grid->column('category_id', __('Category'))->display(function($category_id){
            $category_name = Category::where('id',$category_id)->value('category_name');
            return $category_name;
        });
       
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
            

        $grid->filter(function ($filter) {
            //Get All status
        $categories = Category::pluck('category_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        if(Admin::user()->isAdministrator()){
                $filter->like('restaurant_id', 'Restaurant')->select($restaurants);
                $filter->equal('category_id', __('Category Id'))->select($categories);
            }else{
                $filter->equal('category_id', __('Category Id'))->select($categories);
            }
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
        $show = new Show(RestaurantCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
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
        $form = new Form(new RestaurantCategory());
        $categories = Category::pluck('category_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');
        
        if(!Admin::user()->isAdministrator()){
            $form->hidden('restaurant_id')->value($restaurant_id);
        }else{
            $form->select('restaurant_id', __('Restaurant'))->options($restaurants);
        }
        $form->select('category_id', __('Category'))->options($categories)->rules(function ($form) {
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

<?php

namespace App\Admin\Controllers;

use App\Models\RestaurantCuisine;
use App\Models\FoodCuisine;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;

class RestaurantCuisineController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Restaurant Cuisines';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RestaurantCuisine());
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }

        $grid->column('id', __('Id'));
        $grid->column('restaurant_id', __('Restaurant'))->display(function($restaurant_id){
            return Restaurant::where('id',$restaurant_id)->value('restaurant_name');
        });
        $grid->column('cuisine_id', __('Cuisine'))->display(function($cuisine_id){
            return FoodCuisine::where('id',$cuisine_id)->value('cuisine_name');
        });
    

        $grid->disableExport();
         $grid->actions(function ($actions) {
                $actions->disableView();
            });

        $grid->filter(function ($filter) {
            //Get All status
            $restaurants = Restaurant::pluck('restaurant_name', 'id');
            $cuisine = FoodCuisine::pluck('cuisine_name', 'id');
            
            
            if(Admin::user()->isAdministrator()){
                $filter->like('restaurant_id', 'restaurant')->select($restaurants);
            }
            $filter->like('cuisine_id', 'Cuisine')->select($cuisine);
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
        $show = new Show(RestaurantCuisine::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('cuisine_id', __('Cuisine id'));
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
        $form = new Form(new RestaurantCuisine());
        $restaurant = Restaurant::pluck('restaurant_name', 'id');
        $cuisine = FoodCuisine::pluck('cuisine_name', 'id');
        $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');
        
        if(!Admin::user()->isAdministrator()){
            $form->hidden('restaurant_id')->value($restaurant_id);
        }else{
            $form->select('restaurant_id', __('Restaurant '))->options($restaurant)->rules(function ($form) {
            return 'required';
        });
        }
        $form->select('cuisine_id', __('Cuisine Name'))->options($cuisine)->rules(function ($form) {
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

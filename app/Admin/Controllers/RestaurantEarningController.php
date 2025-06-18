<?php

namespace App\Admin\Controllers;

use App\Models\RestaurantEarning;
use App\Models\Status;
use App\Models\Order;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;

class RestaurantEarningController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Restaurant Earnings';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RestaurantEarning());
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }
        $grid->column('id', __('Id'));
        $grid->column('order_id', __('Order Id'));
        $grid->column('restaurant_id', __('Restaurant'))->display(function($restaurant_id){
            return Restaurant::where('id',$restaurant_id)->value('restaurant_name');
        });
        $grid->column('amount', __('Amount'));
     


        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
                $actions->disableView();
            });

        $grid->filter(function ($filter) {
            //Get All status
            $restaurants = Restaurant::pluck('restaurant_name', 'id');
            $filter->like('order_id', 'Order');
            if(Admin::user()->isRole('restaurant')){
            }else{
                $filter->like('restaurant_id', 'restaurant')->select($restaurants);
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
        $show = new Show(RestaurantEarning::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_id', __('Order id'));
        $show->field('restaurant_id', __('Restaurant id'));
        $show->field('amount', __('Amount'));
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
        $form = new Form(new RestaurantEarning());
        $restaurant = Restaurant::pluck('restaurant_name', 'id');
        $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');
        
        if(!Admin::user()->isAdministrator()){
            $form->hidden('restaurant_id')->value($restaurant_id);
        }else{
            $form->select('restaurant_id', __('Restaurant'))->options($restaurant)->rules(function ($form) {
            return 'required';
        });
        }
        $form->text('order_id', __('Order Id'));
        $form->decimal('amount', __('Amount'))->rules(function ($form) {
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

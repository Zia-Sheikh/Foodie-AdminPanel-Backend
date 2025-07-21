<?php

namespace App\Admin\Controllers;

use App\Models\RestaurantRating;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RestaurantRatingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Restaurant Rating';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RestaurantRating());

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer Id'))->display(function($customer_id){
            return Customer::where('id',$customer_id)->value('customer_name');
        });
        $grid->column('order_id', __('Order id'));
        $grid->column('restaurant_id', __('Restaurant Id'))->display(function($restaurant_id){
            return Restaurant::where('id',$restaurant_id)->value('restaurant_name');
        });
        $grid->column('rating', __('Rating'));
     

        $grid->disableExport();
        $grid->disableCreateButton();
            $grid->disableActions();

        $grid->filter(function ($filter) {
            //Get All status
            $restaurants = Restaurant::pluck('restaurant_name', 'id');
            $customer = Customer::pluck('customer_name', 'id');
            $filter->like('order_id', 'Order');
            $filter->like('restaurant_id', 'restaurant')->select($restaurants);
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
        $show = new Show(RestaurantRating::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('order_id', __('Order id'));
        $show->field('restaurant_id', __('Restaurant id'));
        $show->field('rating', __('Rating'));
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
        $form = new Form(new RestaurantRating());
        $restaurant = Restaurant::pluck('restaurant_name', 'id');
        $customer = Customer::pluck('customer_name', 'id');

        $form->select('customer_id', __('Customer Name'))->options($customer)->rules(function ($form) {
            return 'required';
        });
        $form->text('order_id', __('Order Id'));
        $form->select('restaurant_id', __('Restaurant Id'))->options($restaurant)->rules(function ($form) {
            return 'required';
        });
        $form->text('rating', __('Rating'));

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

<?php

namespace App\Admin\Controllers;

use App\Models\OrderRating;
use App\Models\Order;
use App\Models\Customer;
use App\Models\DeliveryBoy;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderRatingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Order Ratings';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderRating());

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer'))->display(function($customer_id){
            return Customer::where('id',$customer_id)->value('customer_name');
        });
        $grid->column('restaurant_id', __('Restaurant'))->display(function($restaurant_id){
            return Restaurant::where('id',$restaurant_id)->value('restaurant_name');
        });
        $grid->column('order_id', __('Order Id'));
        $grid->column('rating', __('Rating'));
     
        
        $grid->disableExport();
        $grid->disableCreateButton();
            $grid->disableActions();
        
        $grid->filter(function ($filter) {
            //Get All status
            $customer = Customer::pluck('customer_name', 'id');
            $filter->like('order_id', 'Order');
            $filter->equal('rating', 'rating');
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
        $show = new Show(OrderRating::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('customer id'));
        $show->field('order_id', __('Order id'));
        $show->field('rating', __('Rating'));
        //$show->field('created_at', __('Created at'));
        //$show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new OrderRating());
        $customer = Customer::pluck('customer_name', 'id');

        $form->select('customer_id', __('Customer id'))->options($customer)->rules(function ($form) {
            return 'required';
        });
        $form->text('order_id', __('Order id'));
        $form->text('rating', __('Rating'));
        $form->text('review', __('Review'));

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

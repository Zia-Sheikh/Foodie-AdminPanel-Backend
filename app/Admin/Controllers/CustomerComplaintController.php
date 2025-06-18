<?php

namespace App\Admin\Controllers;

use App\Models\CustomerComplaint;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\Order;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerComplaintController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer Complaints';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomerComplaint());

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer'))->display(function($customer_id){
            $customer_id = Customer::where('id',$customer_id)->value('customer_name');
            return $customer_id;
        });
        $grid->column('restaurant_id', __('Restaurant'))->display(function($restaurant_id){
            $restaurant_id = Restaurant::where('id',$restaurant_id)->value('restaurant_name');
            return $restaurant_id;
        });
        $grid->column('order_id', __('Order Id'));
        $grid->column('complaint', __('Complaint'));
   

         $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function ($filter) {
            //Get All status

        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $customers = Customer::pluck('customer_name', 'id');

        $filter->equal('customer_id', __('Customer'))->select($customers);
        $filter->equal('restaurant_id', __('Restaurant'))->select($restaurants);
        //$filter->like('complaint', __('Complaint'));

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
        $show = new Show(CustomerComplaint::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('restaurant_id', __('Restaurant id'));
        $show->field('order_id', __('Order id'));
        $show->field('complaint', __('Complaint'));
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
        $form = new Form(new CustomerComplaint());
        $customers = Customer::pluck('customer_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $orders = Order::pluck('id', 'id');

        $form->select('order_id', __('Order Id'))->options($orders)->rules(function ($form) {
            return 'required';
        });

        $form->select('customer_id', __('Customer'))->options($customers)->rules(function ($form) {
            return 'required';
        });
        $form->select('restaurant_id', __('Restaurant'))->options($restaurants)->rules(function ($form) {
            return 'required';
        });
        
        $form->textarea('complaint', __('Complaint'))->rules('required');

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

<?php

namespace App\Admin\Controllers;

use App\Models\FavouriteRestaurant;
use App\Models\Customer;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FavouriteRestaurantController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Favourite Restaurant';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FavouriteRestaurant());

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer '))->display(function($customer_id){
            $customer_name = Customer::where('id',$customer_id)->value('customer_name');
            return $customer_name;
        });
        $grid->column('restaurant_id', __('Restaurant '))->display(function($restaurant_id){
            $restaurant_name = Restaurant::where('id',$restaurant_id)->value('restaurant_name');
            return $restaurant_name;
        });
    
        $grid->disableExport();
        $grid->disableCreateButton();
            $grid->disableActions();
        $grid->filter(function ($filter) {
            //Get All status
        $customers = Customer::pluck('customer_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');

        $filter->equal('customer_id', __('Customer id'))->select($customers);
        $filter->equal('restaurant_id', __('Restaurant id'))->select($restaurants);

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
        $show = new Show(FavouriteRestaurant::findOrFail($id));

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
        $form = new Form(new FavouriteRestaurant());
        $customers = Customer::pluck('customer_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');

        $form->select('customer_id', __('Customer'))->options($customers)->rules(function ($form) {
            return 'required';
        });
        $form->select('restaurant_id', __('Restaurant'))->options($restaurants)->rules(function ($form) {
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

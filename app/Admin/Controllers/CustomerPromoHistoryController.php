<?php

namespace App\Admin\Controllers;

use App\Models\CustomerPromoHistory;
use App\Models\Customer;
use App\Models\PromoCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerPromoHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer Promo Histories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomerPromoHistory());

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer'))->display(function($customer_id){
            $customer_id = Customer::where('id',$customer_id)->value('customer_name');
            return $customer_id;
        });
        $grid->column('promo_id', __('Promo'))->display(function($promo_id){
            $promo_name = PromoCode::where('id',$promo_id)->value('promo_name');
            return $promo_name;
        });
      
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->filter(function ($filter) {
            //Get All status
        $customers = Customer::pluck('customer_name', 'id');
        $promos = PromoCode::pluck('promo_name', 'id');

        $filter->equal('customer_id', 'Customer ')->select($customers);
        $filter->equal('promo_id', 'Promo ')->select($promos);

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
        $show = new Show(CustomerPromoHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('promo_id', __('Promo id'));
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
        $form = new Form(new CustomerPromoHistory());
        $customers = Customer::pluck('customer_name', 'id');
        $promos = PromoCode::pluck('promo_name', 'id');


        $form->select('customer_id', __('Customer'))->options($customers)->rules(function ($form) {
            return 'required';
        });
        $form->select('promo_id', __('Promo'))->options($promos)->rules(function ($form) {
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

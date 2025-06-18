<?php

namespace App\Admin\Controllers;

use App\Models\CustomerWalletHistory;
use App\Models\Status;
use App\Models\Customer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerWalletHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer Wallet Histories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomerWalletHistory());

        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer Id'))->display(function($customer_id){
            return Customer::where('id',$customer_id)->value('customer_name');
        });
        $grid->column('type', __('Type'))->display(function($type){
            
            if ($type == 1) {
                return "<span class='label label-warning'>Credit</span>";
            }if ($type == 2) {
                return "<span class='label label-success'>Debit</span>";
            } 
        });
        $grid->column('message', __('Message'));
        $grid->column('amount', __('Amount'));
        $grid->column('transaction_type', __('Transaction Type'))->display(function($amount_type){
            
            if ($amount_type == 1) {
                return "Customer added amount";
            }if ($amount_type == 2) {
                return "Refund amount";
            } 
        });
        
        
        $grid->disableExport();
        $grid->disableCreateButton();
            $grid->disableActions();
         $grid->filter(function ($filter) {
            //Get All status
            $customers = Customer::pluck('customer_name', 'id');
            
            $filter->equal('customer_id', 'Customer')->select($customers);
            
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
        $show = new Show(CustomerWalletHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('type', __('Type'));
        $show->field('message', __('Message'));
        $show->field('amount', __('Amount'));
        $show->field('transaction_type', __('Transaction Type'));
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
        $form = new Form(new CustomerWalletHistory());
         $customers = Customer::pluck('customer_name', 'id');

        $form->select('customer_id', __('Customer Id'))->options($customers)->rules(function ($form) {
            return 'required';
        });
        $form->select('type', __('Type'))->options(['1' => 'Credit', '2'=> 'Debit'])->rules(function ($form) {
            return 'required';
        });
        $form->text('message', __('Message'))->rules(function ($form) {
            return 'required';
        });
        $form->decimal('amount', __('Amount'))->rules(function ($form) {
            return 'required|max:100';
        });
        $form->select('transaction_type', __('Transaction Type'))->options(['1' => 'Customer added amount', '2'=> 'Refund amount','3' => 'Deducted amount'])->rules(function ($form) {
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

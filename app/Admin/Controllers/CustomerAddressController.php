<?php

namespace App\Admin\Controllers;

use App\Models\CustomerAddress;
use App\Models\Customer;
use App\Models\AddressType;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerAddressController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer Address';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomerAddress());

        $grid->column('id', __('Id'));

        $grid->column('customer_id', __('Customer '))->display(function($customer_id){
            $customer_name = Customer::where('id',$customer_id)->value('customer_name');
            return $customer_name;
        });

        $grid->column('address', __('Address'));
        $grid->column('landmark', __('Landmark'));
        $grid->column('address_type', __('Address Type'))->display(function($type){
            if ($type == 1) {
                return "<span class='label label-success'>Home</span>";
            } if ($type == 2) {
                return "<span class='label label-info'>Work</span>";
            } else {
                return "<span class='label label-warning'>Others</span>";
            }
        });

        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } if ($status == 2) {
                return "<span class='label label-danger'>$status_name</span>";
            } 
        });

        

        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function ($filter) {
            //Get All status
        $customers = Customer::pluck('customer_name', 'id');

        $filter->equal('customer_id', 'Customer Id')->select($customers);
        $filter->like('address', __('Address'));
        $filter->like('landmark', __('Landmark'));
        $filter->like('lat', __('Lat'));
        $filter->like('lng', __('Lng'));
        $filter->equal('address_type', __('Address Type'));
       

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
        $show = new Show(CustomerAddress::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('address', __('Address'));
        $show->field('landmark', __('Landmark'));
        $show->field('lat', __('Lat'));
        $show->field('lng', __('Lng'));
        $show->field('address_type', __('Address type'));
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
        $form = new Form(new CustomerAddress());
        $customers = Customer::pluck('customer_name', 'id');
        $statuses = Status::pluck('status_name', 'id');
        $address_types = AddressType::pluck('type_name', 'id');


        $form->select('customer_id', __('Customer Id'))->options($customers)->rules(function ($form) {
            return 'required';
        });

        $form->textarea('address', __('Address'))->rules('required');

        $form->text('landmark', __('Landmark'))->rules(function ($form) {
            return 'required|max:150';
        });

        $form->text('lat', __('Lat'))->rules(function ($form) {
            return 'required|max:150';
        });

        $form->text('lng', __('Lng'))->rules(function ($form) {
            return 'required|max:150';
        });

        $form->select('address_type', __('Address Type'))->options($address_types)->default(1)->rules(function ($form) {
            return 'required';
        });
        $form->select('status', __('Status'))->options(Status::where('slug','general')->pluck('status_name','id'))->rules(function ($form) {
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

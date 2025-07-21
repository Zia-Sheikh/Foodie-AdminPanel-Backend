<?php

namespace App\Admin\Controllers;

use App\Models\OrderStatus;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderStatusController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'OrderStatus';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderStatus());

        $grid->column('id', __('Id'));
        $grid->column('slug', __('Slug'));
        $grid->column('status', __('Status'));
        $grid->column('status_for_customer', __('Status For Customer'));
        $grid->column('status_for_restaurant', __('Status For Restaurant'));
        $grid->column('status_for_deliveryboy', __('Status For Deliveryboy'));
        

        $grid->disableExport();
        $grid->disableCreateButton();
            $grid->disableActions();

        $grid->filter(function ($filter) {
            //Get All status
            $filter->like('slug', __('Slug'));
            $filter->like('status', __('Status'));
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
        $show = new Show(OrderStatus::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('slug', __('Slug'));
        $show->field('status_for_customer', __('Status for customer'));
        $show->field('status_for_restaurant', __('Status for restaurant'));
        $show->field('status_for_deliveryboy', __('Status for deliveryboy'));
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
        $form = new Form(new OrderStatus());

        $form->text('slug', __('Slug'))->rules(function ($form) {
            return 'required';
        });
        $form->text('status', __('Status'))->rules(function ($form) {
            return 'required';
        });
        $form->text('status_for_customer', __('Status For Customer'))->rules(function ($form) {
            return 'required';
        });
        $form->text('status_for_restaurant', __('Status For Restaurant'));
        $form->text('status_for_deliveryboy', __('Status For Deliveryboy'));
        $form->text('status_ar', __('Status Ar'));
        $form->text('status_for_customer_ar', __('Status For Customer Ar'));
        $form->text('status_for_restaurant_ar', __('Status For Restaurant Ar'));
        $form->text('status_for_deliveryboy_ar', __('Status For Deliveryboy Ar'));

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

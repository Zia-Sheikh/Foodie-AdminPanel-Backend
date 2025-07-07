<?php

namespace App\Admin\Controllers;

use App\Models\CustomerAppSetting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerAppSettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer App Setting';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomerAppSetting());

        $grid->column('id', __('Id'));
        $grid->column('app_name', __('App Name'));
        $grid->column('app_logo', __('App Logo'))->image();
        $grid->column('default_currency', __('Default Currency'));
        $grid->column('currency_short_code', __('Currency Short Code'));
        $grid->column('delivery_charge_per_km', __('Delivery Charge Per KM'));
        $grid->column('app_version', __('App Version'));
        $grid->column('referral_message', __('Referral Message'));
        $grid->column('referral_bonus', __('Referral Bonue'));
        $grid->column('platform_fee', __('Platform Fee'));
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->filter(function ($filter) {
            //Get All status
        $filter->like('app_name', __('App name'));
        $filter->like('app_logo', __('App logo'));

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
        $show = new Show(CustomerAppSetting::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('app_name', __('App name'));
        $show->field('app_logo', __('App logo'));
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
        $form = new Form(new CustomerAppSetting());

        $form->text('app_name', __('App Name')) ->rules(function ($form) {
            return 'required|max:150';
        });
        $form->image('app_logo', __('App Logo'))->uniqueName()->rules('required');
        $form->text('default_currency', __('Default currency'))->rules('required');
        $form->text('currency_short_code', __('Currency Short Code'))->rules('required');
        $form->text('restaurant_radius', __('Restaurant Radius'))->rules('required');
        // $form->time('opening_time', __('Opening Time'))->rules('required');
        // $form->time('closing_time', __('Closing Time'))->rules('required');
        $form->hidden('opening_time')->default('00:00:00');
        $form->hidden('closing_time')->default('23:59:59');
        $form->textarea('description', __('Description'))->rules('required');
        // $form->textarea('description_ar', __('Description Ar'))->rules('required');
        $form->text('app_version', __('App Version'))->rules('required');
        $form->textarea('address', __('Address'))->rules('required');
        $form->text('razorpay_key', __('Razorpay Key'))->rules('required');
        $form->decimal('delivery_charge_per_km', __('Delivery Charge Per KM'))->rules('required');
        $form->decimal('max_cash_validation', __('Maximum Cash Validation'))->rules('required');
        // $form->textarea('referral_message', __('Referral Message'))->rules('required');
        // $form->textarea('referral_message_ar', __('Referral Message Ar'))->rules('required');
        $form->text('referral_bonus', __('Referral Bonus'))->rules('required');
        $form->decimal('platform_fee', __('Platfrom Fee'))->rules('required');
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

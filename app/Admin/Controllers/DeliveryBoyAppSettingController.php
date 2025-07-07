<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryBoyAppSetting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DeliveryBoyAppSettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Delivery Boy AppSettings';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeliveryBoyAppSetting());

        $grid->column('id', __('Id'));
        $grid->column('app_name', __('App Name'));
        $grid->column('app_logo', __('App Logo'))->image();
        $grid->column('default_currency', __('Default Currency'));
        $grid->column('currency_short_code', __('Currency Short Code'));
        $grid->column('delivery_charge_per_km', __('Delivery Charge Per KM'));
        $grid->column('booking_searching_radius', __('Booking Searching Radius'));
        $grid->column('app_version', __('App Version'));
    

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->filter(function ($filter) {
            //Get All status
        $filter->like('app_name', __('App name'));
        // $filter->like('app_logo', __('App logo'));

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
        $show = new Show(DeliveryBoyAppSetting::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('app_name', __('App name'));
        $show->field('app_logo', __('App logo'));
        $show->field('default_currency', __('Default currency'));
        $show->field('currency_short_code', __('Currency short code'));
        $show->field('restaurant_radius', __('Restaurant radius'));
        $show->field('opening_time', __('Opening time'));
        $show->field('closing_time', __('Closing time'));
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
        $form = new Form(new DeliveryBoyAppSetting());

          $form->text('app_name', __('App Name')) ->rules(function ($form) {
            return 'required|max:150';
        });
        $form->image('app_logo', __('App Logo'))->uniqueName()->rules('required');
        $form->text('default_currency', __('Default currency'))->rules('required');
        $form->text('currency_short_code', __('Currency Short Code'))->rules('required');
        $form->number('booking_searching_radius', __('Booking Searching Radius'))->rules('required');
        $form->decimal('delivery_charge_per_km', __('Delivery Charge Per KM'))->rules('required');
        $form->textarea('description', __('Description'))->rules('required');
        // $form->textarea('description_ar', __('Description Ar'))->rules('required');
        $form->text('app_version', __('App Version'))->rules('required');
        $form->textarea('address', __('Address'))->rules('required');
        

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

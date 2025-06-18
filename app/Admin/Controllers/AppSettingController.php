<?php

namespace App\Admin\Controllers;

use App\Models\AppSetting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AppSettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App Setting';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AppSetting());

        $grid->column('id', __('Id'));
        $grid->column('app_name', __('App Name'));
        $grid->column('app_logo', __('App Logo'))->image();
        $grid->column('default_currency', __('Default currency'));
        $grid->column('currency_short_code', __('Currency Short Code'));
        $grid->column('referral_message', __('Referral Message'));
        $grid->column('referral_bonus', __('Referral Bonus'));
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
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AppSetting());

        $form->text('app_name', __('App Name')) ->rules(function ($form) {
            return 'required|max:150';
        });
        $form->image('app_logo', __('App Logo'))->uniqueName()->rules('required');
        $form->text('default_currency', __('Default currency'))->rules('required');
        $form->text('currency_short_code', __('Currency Short Code'))->rules('required');
        $form->text('restaurant_radius', __('Restaurant Radius'))->rules('required');
        $form->time('opening_time', __('Opening Time'))->rules('required');
        $form->time('closing_time', __('Closing Time'))->rules('required');
        $form->textarea('referral_message', __('Referral Message'))->rules('required');
        $form->text('referral_bonus', __('Referral Bonus'))->rules('required');
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

<?php

namespace App\Admin\Controllers;

use App\Models\RestaurantSetting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RestaurantSettingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Restaurant Setting';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RestaurantSetting());

        $grid->column('id', __('Id'));
        $grid->column('opening_time', __('Opening Time'));
        $grid->column('closing_time', __('Closing Time'));
       
        $grid->disableExport();
         $grid->disableCreateButton();
        $grid->actions(function ($actions) {
                $actions->disableView();
                $actions->disableDelete();
            });

        $grid->filter(function ($filter) {
        //Get All status
        $filter->like('opening_time', __('Opening Time'));
        $filter->like('closing_time', __('Closing Time'));

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
        $show = new Show(RestaurantSetting::findOrFail($id));

        $show->field('id', __('Id'));
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
        $form = new Form(new RestaurantSetting());

        $form->time('opening_time', __('Opening Time'))->default(date('H:i:s'));
        $form->time('closing_time', __('Closing Time'))->default(date('H:i:s'));

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

<?php

namespace App\Admin\Controllers;

use App\Models\RestaurantTiming;
use App\Models\Restaurant;
use App\Models\Day;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RestaurantTimingController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Restaurant Timing';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RestaurantTiming());

        $grid->column('id', __('Id'));
        $grid->column('restaurant_id', __('Restaurant '))->display(function($restaurant_id){
            $restaurant_name = Restaurant::where('id',$restaurant_id)->value('restaurant_name');
            return $restaurant_name;
        });
        $grid->column('day_id', __('Day'))->display(function($day_id){
            $day_id= Day::where('id',$day_id)->value('day');
            return $day_id;
        });
        $grid->column('start_time', __('Start Time'));
        $grid->column('end_time', __('End Time'));
       
        $grid->disableExport();
        $grid->actions(function ($actions) {
                $actions->disableView();
            });
        $grid->filter(function ($filter) {
        //Get All status
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $days = Day::pluck('day', 'id');

        $filter->equal('restaurant_id', __('Restaurant id'))->select($restaurants);
        $filter->equal('day_id', __('Day id'))->select($days);
        $filter->like('start_time', __('Start time'));
        $filter->like('end_time', __('End time'));
        

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
        $show = new Show(RestaurantTiming::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('restaurant_id', __('Restaurant id'));
        $show->field('day_id', __('Day id'));
        $show->field('start_time', __('Start time'));
        $show->field('end_time', __('End time'));
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
        $form = new Form(new RestaurantTiming());
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $days = Day::pluck('day', 'id');

        $form->select('restaurant_id', __('Restaurant Id '))->options($restaurants)->rules(function ($form) {
            return 'required';
        });
        $form->select('day_id', __('Day '))->options($days)->rules(function ($form) {
            return 'required';
        });
        $form->time('start_time', __('Start Time'))->default(date('H:i:s'));
        $form->time('end_time', __('End Time'))->default(date('H:i:s'));

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

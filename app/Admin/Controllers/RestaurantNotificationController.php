<?php

namespace App\Admin\Controllers;

use App\Models\RestaurantNotification;
use App\Models\Restaurant;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RestaurantNotificationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Restaurant Announcements';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RestaurantNotification());

        $grid->column('id', __('Id'));
        $grid->column('destination_id', __('Destination'))->display(function($restaurants){
            if($restaurants == NULL){
            return "Null";
            }else{
                $restaurants = Restaurant::where('id',$restaurants)->value('restaurant_name');
            return $restaurants;
            }
        });
        $grid->column('title', __('Title'));
        //$grid->column('description', __('Description'));
        $grid->column('image', __('Image'))->image();
      

        $grid->disableExport();
        $grid->disableActions();
        $grid->filter(function ($filter) {
            //Get All status
        $restaurants = Restaurant::pluck('restaurant_name', 'id');

        $filter->like('title', __('Title'));
        $filter->equal('destination_id', __('Destination '))->select($restaurants);

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
        $show = new Show(RestaurantNotification::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('destination_id', __('Destination id'));
        $show->field('title', __('Title'));
        $show->field('description', __('Description'));
        $show->field('image', __('Image'));
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
        $form = new Form(new RestaurantNotification());
        $restaurants = Restaurant::pluck('restaurant_name','id');


        $form->select('destination_id', __('Destination'))->options($restaurants);
        $form->text('title', __('Title')) ->rules(function ($form) {
            return 'required|max:500';
        });
        $form->textarea('description', __('Description'))->rules('required');
        $form->image('image', __('Image'))->uniqueName()->rules('required');

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

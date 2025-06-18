<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryBoyNotification;
use App\Models\DeliveryBoy;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DeliveryBoyNotificationController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Delivery Boy Announcements';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeliveryBoyNotification());

        $grid->column('id', __('Id'));
        $grid->column('destination_id', __('Destination '))->display(function($users){
            if($users == 0){
            return "NILL";
            }else{
                $users = DeliveryBoy::where('id',$users)->value('delivery_boy_name');
            return $users;
            }
        });
        $grid->column('title', __('Title'));
        //$grid->column('description', __('Description'));
        $grid->column('image', __('Image'))->image();
       
        $grid->disableExport();
        $grid->disableActions();

        $grid->filter(function ($filter) {
            //Get All status
         $users = DeliveryBoy::pluck('delivery_boy_name', 'id');

         $filter->like('title', __('Title'));
         $filter->equal('destination_id', __('Destination '))->select($users);
         
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
        $show = new Show(DeliveryBoyNotification::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('notification_type', __('Notification type'));
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
        $form = new Form(new DeliveryBoyNotification());
        $users = DeliveryBoy::pluck('delivery_boy_name','id');


        $form->select('destination_id', __('Destination '))->options($users);
        $form->text('title', __('Title')) ->rules(function ($form) {
            return 'required|max:500';
        });
        $form->textarea('description', __('Description'))->rules('required');
        $form->text('title_ar', __('Title Ar'));
        $form->textarea('description_ar', __('Description Ar'));
        $form->image('image', __('Image'))->uniqueName()->move('notifications')->rules('required');
        
        $form->saving(function ($form) {
           if(!$form->destination_id){
              $form->destination_id = 0;
           }
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

<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryBoyEarning;
use App\Models\Status;
use App\Models\Order;
use App\Models\DeliveryBoy;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DeliveryBoyEarningController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Delivery Boy Earnings';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeliveryBoyEarning());

        $grid->column('id', __('Id'));
        $grid->column('order_id', __('Order Id'));
        $grid->column('delivery_boy_id', __('Delivery Boy'))->display(function($delivery_boy_id){
            return DeliveryBoy::where('id',$delivery_boy_id)->value('Delivery_boy_name');
        });
        $grid->column('amount', __('Amount'));
 

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableActions();

        $grid->filter(function ($filter) {
            //Get All status
            $delivery_boys = DeliveryBoy::pluck('delivery_boy_name', 'id');
            $filter->like('order_id', 'Order');
            $filter->like('delivery_boy_id', 'DeliveryBoy')->select($delivery_boys);
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
        $show = new Show(DeliveryBoyEarning::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('order_id', __('Order id'));
        $show->field('delivery_boy_id', __('Delivery boy id'));
        $show->field('amount', __('Amount'));
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
        $form = new Form(new DeliveryBoyEarning());
        $delivery_boy = DeliveryBoy::pluck('delivery_boy_name', 'id');

        $form->text('order_id', __('Order Id'));
        $form->select('delivery_boy_id', __('Delivery Boy'))->options($delivery_boy)->rules(function ($form) {
            return 'required';
        });
        $form->decimal('amount', __('Amount'))->rules(function ($form) {
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

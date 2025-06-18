<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryBoyWalletHistory;
use App\Models\DeliveryBoy;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DeliveryBoyWalletHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Delivery Boy Wallet Histories';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeliveryBoyWalletHistory());

        $grid->column('id', __('Id'));
        $grid->column('delivery_boy_id', __('Delivery Boy'))->display(function($delivery_boy_id){
            return DeliveryBoy::where('id',$delivery_boy_id)->value('delivery_boy_name');
        });
        $grid->column('type', __('Type'))->display(function($type){
            
            if ($type == 1) {
                return "<span class='label label-warning'>Credit</span>";
            }if ($type == 2) {
                return "<span class='label label-success'>Debit</span>";
            } 
        });
        $grid->column('message', __('Message'));
        $grid->column('amount', __('Amount'));
     


        $grid->disableExport();
        $grid->disableCreateButton();
            $grid->disableActions();
        $grid->filter(function ($filter) {
            //Get All status
            $delivery_boys = DeliveryBoy::pluck('delivery_boy_name', 'id');
            
            $filter->like('message', 'Message');
            
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
        $show = new Show(DeliveryBoyWalletHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('delivery_boy_id', __('Delivery boy id'));
        $show->field('type', __('Type'));
        $show->field('message', __('Message'));
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
        $form = new Form(new DeliveryBoyWalletHistory());
        $delivery_boy = DeliveryBoy::pluck('delivery_boy_name', 'id');

        $form->number('delivery_boy_id', __('Delivery Boy'))->options($delivery_boy)->rules(function ($form) {
            return 'required';
        });
        $form->number('type', __('Type'))->options(['1' => 'Credit', '2'=> 'Debit'])->rules(function ($form) {
            return 'required';
        });
        $form->textarea('message', __('Message'))->rules(function ($form) {
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

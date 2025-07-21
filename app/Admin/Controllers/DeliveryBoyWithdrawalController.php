<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryBoyWithdrawal;
use App\Models\DeliveryBoy;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class DeliveryBoyWithdrawalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Delivery Boy Withdrawals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeliveryBoyWithdrawal());

        $grid->column('id', __('Id'));
        $grid->column('delivery_boy_id', __('Delivery Boy'))->display(function($delivery_boy){
            $delivery_boy = DeliveryBoy::where('id',$delivery_boy)->value('delivery_boy_name');
                return $delivery_boy;
        });
        $grid->column('amount', __('Amount'));
        $grid->column('message', __('Message'));
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 6) {
                return "<span class='label label-warning'>$status_name</span>";
            } if ($status == 7) {
                return "<span class='label label-success'>$status_name</span>";
            }if ($status == 8) {
                return "<span class='label label-danger'>$status_name</span>";
            }
        });
   

        $grid->disableExport();

            $grid->disableCreateButton();
        
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
        

        $grid->filter(function ($filter) {
            //Get All status
            $statuses = Status::where('slug','withdrawal')->pluck('status_name','id');
            $delivery_boys = DeliveryBoy::pluck('delivery_boy_name', 'id');
            
            $filter->equal('delivery_boy_id', 'Delivery Boy')->select($delivery_boys);
            $filter->equal('status', 'Status')->select($statuses);
           
            
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
        $show = new Show(DeliveryBoyWithdrawal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('delivery_boy_id', __('Delivery boy id'));
        $show->field('amount', __('Amount'));
        $show->field('reference_proof', __('Reference proof'));
        $show->field('reference_no', __('Reference no'));
        $show->field('status', __('Status'));
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
        $form = new Form(new DeliveryBoyWithdrawal());
        $delivery_boys = DeliveryBoy::pluck('Delivery_Boy_Name', 'id');
        $statuses = Status::where('slug','general')->pluck('status_name','id');

        $form->select('delivery_boy_id', __('Delivery Boy'))->options($delivery_boys)->rules(function ($form) {
                return 'required';
            });
        $form->hidden('existing_wallet', __('Existing Wallet'));
        $form->decimal('amount', __('Amount'));
        $form->image('reference_proof', __('Reference Proof'))->uniqueName()->move('del_withdrawals');
        $form->text('reference_no', __('Reference No'));
        $form->text('message', __('Message'))->rules('required');
        $form->select('status', __('Status'))->options(Status::where('slug','withdrawal')->pluck('status_name','id'))->rules(function ($form) {
            return 'required';
        });
        
        $form->saved(function (Form $form) {
            if($form->model()->status == 8){
                DB::table('delivery_boys')->where('id',$form->delivery_boy_id)->update([ 'wallet' => $form->existing_wallet ]);
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

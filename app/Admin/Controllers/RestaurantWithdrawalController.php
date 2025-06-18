<?php

namespace App\Admin\Controllers;

use App\Models\RestaurantWithdrawal;
use App\Models\Restaurant;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;
use Illuminate\Support\Facades\DB;

class RestaurantWithdrawalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Restaurant Withdrawals';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RestaurantWithdrawal());
        
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }
        $grid->column('id', __('Id'));
        $grid->column('restaurant_id', __('Restaurant'))->display(function($restaurant){
            $restaurant = Restaurant::where('id',$restaurant)->value('username');
                return $restaurant;
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
                return "<span class='label label-success'>$status_name</span>";
            }
        });
        
      

        $grid->disableExport();
        $grid->disableCreation();
        if(!Admin::user()->isAdministrator()){
            $grid->actions(function ($actions) {
                $actions->disableView();
                $actions->disableEdit();
                $actions->disableDelete();
            });
        }else{
            $grid->actions(function ($actions) {
                $actions->disableView();
                $actions->disableDelete();
            });
        }
        
        $grid->filter(function ($filter) {
            //Get All status
            $statuses = Status::where('slug','withdrawal')->pluck('status_name','id');
            $restaurants = Restaurant::pluck('restaurant_name', 'id');
            
            if(Admin::user()->isRole('restaurant')){
                $filter->equal('status', 'Status')->select($statuses);
            }else{
                $filter->like('restaurant_id', 'restaurant')->select($restaurants);
                $filter->equal('status', 'Status')->select($statuses);
            }
            
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
        $show = new Show(RestaurantWithdrawal::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('restaurant_id', __('Restaurant id'));
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
        $form = new Form(new RestaurantWithdrawal());
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $statuses = Status::where('slug','general')->pluck('status_name','id');
        $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');

        
        if(!Admin::user()->isAdministrator()){
            $form->hidden('restaurant_id')->value($restaurant_id);
        }else{
            $form->select('restaurant_id', __('Restaurant'))->options($restaurants);
        }
        $form->hidden('existing_wallet', __('Existing Wallet'));
        $form->decimal('amount', __('Amount'))->readonly();
        $form->image('reference_proof', __('Reference Proof'))->uniqueName()->move('res_withdrawals');
        $form->text('reference_no', __('Reference No'));
        $form->text('message', __('Message'));
        $form->select('status', __('Status'))->options(Status::where('slug','withdrawal')->pluck('status_name','id'))->rules(function ($form) {
            return 'required';
        });
        $form->saved(function (Form $form) {
            if($form->model()->status == 8){
                DB::table('restaurants')->where('id',$form->restaurant_id)->update([ 'wallet' => $form->existing_wallet ]);
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

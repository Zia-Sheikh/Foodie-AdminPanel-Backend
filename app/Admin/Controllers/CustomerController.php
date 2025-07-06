<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer());

        $grid->column('id', __('Id'));
        $grid->column('customer_name', __('Customer Name'));
        
        $grid->column('profile_picture', __('Profile Picture'))->image();
        $grid->column('email', __('Email'));
     
        $grid->column('phone_with_code', __('Phone With Code'));
        // $grid->column('referral_code', __('Referral Code'));
        // $grid->column('refered_by', __('Refered By'))->display(function($refered_by){
        //     $refered_by = Customer::where('id',$refered_by)->value('customer_name');
        //     if(!$refered_by){
        //         return '----';
        //     }
        // });
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } else {
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
        $filter->like('customer_name', __('Customer name'));
        $filter->like('profile_picture', __('Profile picture'));
        $filter->like('email', __('Email'));
        // $filter->like('password', __('Password'));
        // $filter->like('fcm_token', __('Fcm token'));
        // $filter->like('phone_number', __('Phone number'));
        $filter->like('overall_rating', __('Overall rating'));

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
        $show = new Show(Customer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_name', __('Customer name'));
        $show->field('profile_picture', __('Profile picture'));
        $show->field('email', __('Email'));
        $show->field('password', __('Password'));
        $show->field('fcm_token', __('Fcm token'));
        $show->field('phone_number', __('Phone number'));
        $show->field('overall_rating', __('Overall rating'));
        $show->field('wallet', __('Wallet'));
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
        $form = new Form(new Customer());
        $statuses = Status::where('slug','general')->pluck('status_name','id');

        $form->text('customer_name', __('Customer Name'))->rules(function ($form) {
            return 'required|max:150';
        });

        $form->image('profile_picture', __('Profile Picture'))->uniqueName()->move('customers')->rules('required');
        $form->email('email', __('Email'))->rules(function ($form) {
            return 'required|max:150';
        });

        // $form->password('password', __('Password'))->rules('required');
        $form->text('phone_number', __('Phone Number'))->rules(function ($form) {
            return 'numeric|required';
        });
        $form->text('phone_with_code', __('Phone With Code'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->select('status', __('Status'))->options(Status::where('slug','general')->pluck('status_name','id'))->rules(function ($form) {
            return 'required';
        });
        $form->saving(function ($form) {
            if($form->password && $form->model()->password != $form->password)
            {
                $form->password = $this->getEncryptedPassword($form->password);
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
    
    public function getEncryptedPassword($input, $rounds = 12) {
        $salt = "";
        $saltchars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        for ($i = 0; $i < 22; $i++) {
            $salt .= $saltchars[array_rand($saltchars)];
        }
        return crypt($input, sprintf('$2y$%2d$', $rounds) . $salt);
    }
}

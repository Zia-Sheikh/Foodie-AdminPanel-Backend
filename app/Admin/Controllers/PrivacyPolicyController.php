<?php

namespace App\Admin\Controllers;

use App\Models\PrivacyPolicy;
use App\Models\Status;
use App\Models\UserType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PrivacyPolicyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Privacy Policy';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PrivacyPolicy());

        $grid->column('id', __('Id'));
        $grid->column('privacy_type', __('Privacy Type'))->display(function($user_types){
            $user_types = UserType::where('id',$user_types)->value('user');
            return $user_types;
        });
        $grid->column('title', __('Title'));
        $grid->column('description', __('Description'));
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } if ($status == 2) {
                return "<span class='label label-danger'>$status_name</span>";
            } 
        });


        
        $grid->disableExport();
        $grid->actions(function ($actions) {
                $actions->disableView();
            });

        $grid->filter(function ($filter) {
            //Get All status
        $statuses = Status::pluck('status_name', 'id');
        $user_types = UserType::pluck('user', 'id');

        $filter->equal('status', __('Status'))->select($statuses);
        $filter->equal('privacy_type', __('Privacy Type'))->select($user_types);
        
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
        $show = new Show(PrivacyPolicy::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('privacy_type', __('Privacy type'));
        $show->field('description', __('Description'));
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
        $form = new Form(new PrivacyPolicy());
        $statuses = Status::pluck('status_name', 'id');
        $user_types = UserType::pluck('user', 'id');


        $form->select('privacy_type', __('Privacy Type'))->options($user_types)->rules(function ($form) {
            return 'required';
        });
        $form->text('title', __('Title'))->rules('required');
        $form->textarea('description', __('Description'))->rules('required');
        // $form->text('title_ar', __('Title Ar'))->rules('required');
        // $form->textarea('description_ar', __('Description Ar'))->rules('required');

        $form->select('status', __('Status'))->options(Status::where('slug','general')->pluck('status_name','id'))->rules(function ($form) {
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

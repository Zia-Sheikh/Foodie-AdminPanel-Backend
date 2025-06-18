<?php

namespace App\Admin\Controllers;

use App\Models\Ticket;
use App\Models\UserType;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TicketController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Ticket';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Ticket());

        $grid->column('id', __('Id'));
        $grid->column('ticket_type', __('Ticket Type'))->display(function($user_types){
            $user_types = UserType::where('id',$user_types)->value('user');
            return $user_types;
        });
        $grid->column('raised_by', __('Raised By'));
        $grid->column('query', __('Query'));
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

        $filter->equal('ticket_type', __('Ticket Type'))->select($user_types);
        $filter->like('raised_by', __('Raised By'));
        $filter->like('query', __('Query'));
        $filter->equal('status', __('Status'))->select($statuses);

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
        $show = new Show(Ticket::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('ticket_type', __('Ticket type'));
        $show->field('raised_by', __('Raised by'));
        $show->field('query', __('Query'));
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
        $form = new Form(new Ticket());
        $statuses = Status::pluck('status_name', 'id');
        $user_types = UserType::pluck('user', 'id');



        $form->select('ticket_type', __('Ticket Type'))->options($user_types)->rules(function ($form) {
            return 'required';
        });

        $form->text('raised_by', __('Raised By')) ->rules(function ($form) {
            return 'required|max:150';
        });

        $form->text('query', __('Query')) ->rules(function ($form) {
            return 'required|max:150';
        });

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

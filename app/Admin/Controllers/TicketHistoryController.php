<?php

namespace App\Admin\Controllers;

use App\Models\TicketHistory;
use App\Models\Status;
use App\Models\Ticket;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TicketHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Ticket History';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TicketHistory());

        $grid->column('id', __('Id'));
        $grid->column('ticket_id', __('Ticket '))->display(function($ticket_id){
            $ticket_id = Ticket::where('id',$ticket_id)->value('ticket_type');
            return $ticket_id;
        });
        $grid->column('message', __('Message'));
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
        $tickets = Ticket::pluck('ticket_type' , 'id');

        $filter->equal('ticket_id', __('Ticket id'))->select($tickets);
        $filter->like('message', __('Message'));
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
        $show = new Show(TicketHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('ticket_id', __('Ticket id'));
        $show->field('message', __('Message'));
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
        $form = new Form(new TicketHistory());
        $statuses = Status::pluck('status_name', 'id');
        $tickets = Ticket::pluck('ticket_type' , 'id');


        $form->select('ticket_id', __('Ticket Id'))->options($tickets)->rules(function ($form) {
            return 'required';
        });

        $form->text('message', __('Message'))->rules(function ($form) {
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

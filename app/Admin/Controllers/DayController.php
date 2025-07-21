<?php

namespace App\Admin\Controllers;

use App\Models\Day;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class DayController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Day';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Day());

        $grid->column('id', __('Id'));
        $grid->column('day', __('Day'));
   

        $grid->disableExport();
        $grid->disableCreateButton();
            $grid->disableActions();
        $grid->filter(function ($filter) {
            //Get All status
        $filter->like('day', __('Day'));
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
        $show = new Show(Day::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('day', __('Day'));
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
        $form = new Form(new Day());

        $form->text('day', __('Day'))->rules(function ($form) {
            return 'required|max:150';
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

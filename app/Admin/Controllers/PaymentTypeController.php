<?php

namespace App\Admin\Controllers;

use App\Models\PaymentType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PaymentTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Payment Type';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PaymentType());

        $grid->column('id', __('Id'));
        $grid->column('type_name', __('Type Name'));
        

        $grid->disableExport();
        $grid->disableCreateButton();
            $grid->disableActions();
        $grid->filter(function ($filter) {
            //Get All status

             $filter->like('type_name', __('Type name'));

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
        $show = new Show(PaymentType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type_name', __('Type name'));
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
        $form = new Form(new PaymentType());

        $form->text('type_name', __('Type Name')) ->rules(function ($form) {
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

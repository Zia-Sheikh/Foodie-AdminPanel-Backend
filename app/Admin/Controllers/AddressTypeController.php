<?php

namespace App\Admin\Controllers;

use App\Models\AddressType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AddressTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Address Type';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AddressType());

        $grid->column('id', __('Id'));
        $grid->column('type_name', __('Type name'));
        $grid->column('icon', __('Icon'))->image();
       

        $grid->disableExport();
        
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->filter(function ($filter) {
            //Get All status
        $filter->like('type_name', __('Type name'));
        // $filter->like('icon', __('Icon'));

         });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AddressType());

        $form->text('type_name', __('Type Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        // $form->text('type_name_ar', __('Type Name Ar'))->rules(function ($form) {
        //     return 'required|max:150';
        // });

        $form->image('icon', __('Icon'))->move('address_types')->uniqueName()->rules('required');

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

<?php

namespace App\Admin\Controllers;

use App\Models\OptionGroup;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OptionGroupController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Option Groups';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OptionGroup());

        $grid->column('id', __('Id'));
        $grid->column('option_group_name', __('Group Name'));
        $grid->disableExport();
        if(env('MODE') == 'DEMO'){
            $grid->disableActions();
            $grid->disableCreateButton();
            $grid->disableRowSelector();
        }else{
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
        }
        $grid->filter(function ($filter) {
            $filter->like('option_group_name', __('Group Name'));
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
        $form = new Form(new OptionGroup());
       
        $form->text('option_group_name', __('Group Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->text('option_group_name_ar', __('Group Name Ar'))->rules(function ($form) {
            return 'max:150';
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

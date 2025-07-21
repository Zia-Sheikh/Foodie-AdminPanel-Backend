<?php

namespace App\Admin\Controllers;

use App\Models\PromoType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PromoTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Promo Type';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PromoType());

        $grid->column('id', __('Id'));
        $grid->column('type_name', __('Type Name'));
        
         $grid->disableExport();
        
            $grid->disableCreateButton();
            $grid->disableActions();
       
      
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
        $show = new Show(PromoType::findOrFail($id));

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
        $form = new Form(new PromoType());

        $form->text('type_name', __('Type Name'));

        return $form;
    }
}

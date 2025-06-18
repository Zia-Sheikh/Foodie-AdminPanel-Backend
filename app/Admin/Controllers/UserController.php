<?php

namespace App\Admin\Controllers;

use App\Models\FaqCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class UserController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'FaqCategory';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new FaqCategory());

        $grid->column('id', __('Id'));
        $grid->column('icon', __('Icon'));
        $grid->column('category_name', __('Category name'));
        $grid->column('description', __('Description'));
        $grid->column('status', __('Status'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(FaqCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('icon', __('Icon'));
        $show->field('category_name', __('Category name'));
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
        $form = new Form(new FaqCategory());

        $form->text('icon', __('Icon'));
        $form->text('category_name', __('Category name'));
        $form->text('description', __('Description'));
        $form->number('status', __('Status'));

        return $form;
    }
}

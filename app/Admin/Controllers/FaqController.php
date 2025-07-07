<?php

namespace App\Admin\Controllers;

use App\Models\Faq;
use App\Models\FaqCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FaqController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Faqs';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Faq());

        $grid->column('id', __('Id'));
        $grid->column('faq_category_id', __('Faq Category'))->display(function($faq_categories){
            $faq_categories = FaqCategory::where('id',$faq_categories)->value('category_name');
            return $faq_categories;
        });

        $grid->column('question', __('Question'));
        $grid->column('answer', __('Answer'));
        $grid->column('image', __('Image'))->image();
        
     
        $grid->disableExport();
        if(env('MODE') == 'DEMO'){
            $grid->disableCreateButton();
        }
        
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->filter(function ($filter) {
            //Get All status
        $filter->like('question', __('Question'));
        $filter->like('answer', __('Answer'));
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
        $show = new Show(Faq::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('faq_type', __('Faq type'));
        $show->field('question', __('Question'));
        $show->field('answer', __('Answer'));
        $show->field('image', __('Image'));
        $show->field('created-at', __('Created at'));
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
        $form = new Form(new Faq());
        $faq_categories =FaqCategory::pluck('category_name', 'id');


        $form->select('faq_category_id', __('Faq Category'))->options($faq_categories)->rules(function ($form) {
            return 'required';
        });
        $form->text('question', __('Question'))->rules(function ($form) {
            return 'required|max:250';
        });
        $form->textarea('answer', __('Answer'))->rules(function ($form) {
            return 'required';
        });
        // $form->text('question_ar', __('Question Ar'))->rules(function ($form) {
        //     return 'required|max:250';
        // });
        // $form->textarea('answer_ar', __('Answer Ar'))->rules(function ($form) {
        //     return 'required';
        // });

        $form->image('image', __('Image'))->uniqueName()->move('faqs');
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
                                                                    


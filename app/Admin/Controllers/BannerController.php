<?php

namespace App\Admin\Controllers;

use App\Models\Banner;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class BannerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Banner';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Banner());

        $grid->column('id', __('Id'));
        $grid->column('image', __('Image'))->image();

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

         $filter->equal('status', 'Status')->select($statuses);

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
        $form = new Form(new Banner());
        $statuses = Status::pluck('status_name', 'id');

        $form->image('image', __('Image'))->uniqueName()->move('banners/')->rules('required');
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
    
    public function destroy($id)
    {
        $media = Banner::findOrFail($id);
        $file_path = public_path().'/files/'.$media->file_path;
        unlink($file_path);
        $media->delete();
        return redirect('admin/dashboard');
    }
}

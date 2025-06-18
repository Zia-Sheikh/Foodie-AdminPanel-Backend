<?php

namespace App\Admin\Controllers;

use App\Models\DeliveryBoy;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;

class DeliveryBoyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Delivery Boys';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DeliveryBoy());

        $grid->column('id', __('Id'));
        $grid->column('delivery_boy_name', __('Delivery Boy Name'));
        $grid->column('email', __('Email'));
        //$grid->column('phone_number', __('Phone Number'));
        $grid->column('phone_with_code', __('Phone With Code'));
        $grid->column('profile_picture', __('Profile Picture'))->image();
        $grid->column('online_status', __('Online Status'))->display(function($status){
            if ($status == 1) {
                return "<span class='label label-success'>Yes</span>";
            } else {
                return "<span class='label label-danger'>No</span>";
            }
            });

        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } else {
                return "<span class='label label-danger'>$status_name</span>";
            }
            });
        
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        $grid->filter(function ($filter) {

        $statuses = Status::where('slug','general')->pluck('status_name','id');
            //Get All status
        $filter->like('delivery_boy_name', __('Delivery boy name'));
        $filter->like('email', __('Email'));
        $filter->like('phone_number', __('Phone Number'));
        $filter->equal('status', 'Status')->select($statuses);


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
        $show = new Show(DeliveryBoy::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('delivery_boy_name', __('Delivery Boy Name'));
        $show->field('email', __('Email'));
        $show->field('phone_number', __('Phone number'));
        $show->field('profile_picture', __('Profile picture'));
        $show->field('fcm_token', __('Fcm token'));
        $show->field('overall_rating', __('Overall rating'));
        $show->field('password', __('Password'));
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
        $form = new Form(new DeliveryBoy());
        $statuses = Status::where('slug','general')->pluck('status_name','id');


        $form->text('delivery_boy_name', __('Delivery Boy Name'))->rules(function ($form) {
            return 'required|max:100';
        });
        $form->email('email', __('Email'))->rules(function ($form) {
            if (!$id = $form->model()->id) {
                return 'required|max:100|unique:customers,email|unique:delivery_boys,email';
            } else {
             return 'required|max:100|unique:customers,email|unique:delivery_boys,email';
         }

        });


        $form->text('phone_number', __('Phone Number'))->rules(function ($form) {
            if (!$id = $form->model()->id) {
                return 'numeric|required|unique:delivery_boys,phone_number|unique:customers,phone_number';
            } else {
                return 'numeric|required|unique:customers,phone_number|unique:delivery_boys,phone_number,'.$form->model()->id;
            }
        });
         $form->text('phone_with_code', __('Phone With Code'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->image('profile_picture', __('Profile Picture'))->uniqueName()->move('delivery_boys');
        //$form->text('fcm_token', __('Fcm token'))->rules('required');
        $form->password('password', __('Password'))->rules(function ($form) {
            return 'required';
        });
   
        $form->select('status', __('Status'))->options(Status::where('slug','general')->pluck('status_name','id'))->rules(function ($form) {
            return 'required';
        });

        $form->saving(function ($form) {
            if($form->password && $form->model()->password != $form->password)
            {
                $form->password = $this->getEncryptedPassword($form->password);
            }
        });

        $form->saved(function (Form $form) {
            $this->update_status($form->model()->id,$form->model()->delivery_boy_name);
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

    public function getEncryptedPassword($input, $rounds = 12) {
        $salt = "";
        $saltchars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        for ($i = 0; $i < 22; $i++) {
            $salt .= $saltchars[array_rand($saltchars)];
        }
        return crypt($input, sprintf('$2y$%2d$', $rounds) . $salt);
    }
    
    public function update_status($id,$nme){
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $newPost = $database
        ->getReference('delivery_partners/'.$id)
        ->update([
            'p_id' => $id,
            'nme' => $nme,
            'o_stat' => 0,
            'o_id' => 0,
            'on_stat' => 0,
            'lat' => 0,
            'lng' => 0,
            'bearing' => 0
        ]);
    }
}







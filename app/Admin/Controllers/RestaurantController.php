<?php

namespace App\Admin\Controllers;

use App\Models\Restaurant;
use App\Models\Status;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;

class RestaurantController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Home Chef';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Restaurant());

        $grid->column('id', __('Id'));
        $grid->column('restaurant_name', __('HomeChef Name'));
        $grid->column('restaurant_image', __('HomeChef Image'))->image();
        $grid->column('contact_person_name', __('Contact Person Name'));
        //$grid->column('restaurant_phone_number', __('Restaurant Phone Number'));
        $grid->column('phone_with_code', __('Phone With Code'));
        $grid->column('is_open', __('Is Open'))->display(function($status){
            if($status == 0) {
                return "<span class='label label-danger'>Closed</span>";
            } else {
                return "<span class='label label-success'>Opened</span>";
            } 
        });
        $grid->column('password', __('Password'))->hide();
        $grid->column('username', __('Username'));
        $grid->column('number_of_rating', __('Number of Rating'));
        $grid->column('overall_rating', __('Overall Rating'));
        $grid->column('license_no', __('License No'));
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = Status::where('id',$status)->value('status_name');
            if ($status == 1) {
                return "<span class='label label-success'>$status_name</span>";
            } if ($status == 2) {
                return "<span class='label label-danger'>$status_name</span>";
            } 
        });

        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
                $actions->disableView();
            });
        $grid->filter(function ($filter) {
        //Get All status
        $statuses = Status::pluck('status_name', 'id');

        
        $filter->like('restaurant_name', __('HomeChef Name'));
        $filter->like('address', __('Address'));
        $filter->like('contact_person_name', __('Contact Person Name'));
        $filter->equal('contact_person_phone_number', __('Contact Person Phone Number'));
        $filter->equal('is_open', __('Is Open'))->select(['1' => 'Yes', '2'=> 'No']);
        $filter->like('username', __('Username'));
        $filter->like('lat', __('Lat'));
        $filter->like('lng', __('Lng'));
        $filter->like('number_of_rating', __('Number of Rating'));
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
        $show = new Show(Restaurant::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('restaurant_name', __('Restaurant name'));
        $show->field('restaurant_image', __('Restaurant image'));
        $show->field('address', __('Address'));
        $show->field('contact_person_name', __('Contact person name'));
        $show->field('contact_person_phone_number', __('Contact person phone number'));
        $show->field('is_open', __('Is open'));
        $show->field('password', __('Password'));
        $show->field('username', __('Username'));
        $show->field('lat', __('Lat'));
        $show->field('lng', __('Lng'));
        $show->field('number_of_rating', __('Number of rating'));
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
        $form = new Form(new Restaurant());
         $statuses = Status::pluck('status_name', 'id');

        $form->text('restaurant_name', __('Restaurant Name'))->rules('required|max:150');
        // $form->text('restaurant_name_ar', __('Restaurant Name Ar'));
        $form->image('restaurant_image', __('Restaurant Image'))->move('restaurants')->uniqueName()->rules('required');
        $form->textarea('manual_address', __('Manual Address'))->rules('required');
        $form->textarea('google_address', __('Google Address'));
        $form->text('contact_person_name', __('Contact Person Name'))->rules('required|max:150');
        $form->text('restaurant_phone_number', __('Restaurant Phone Number'))->rules(function ($form) {
                return 'numeric|required';
        });
        $form->text('phone_with_code', __('Phone With Code'))->rules(function ($form) {
            return 'required';
        });

        $form->select('is_open', __('Is open'))->options(['1' => 'Opened', '0'=> 'Closed'])->default('0')->rules('required');
        $form->password('password', __('Password'))->rules('required|max:250');
        $form->text('username', __('Username'))->rules('required|max:150');
        $form->text('licence_no', __('Licence Number'))->rules('required');
        $form->text('lat', __('Latitude'))->rules('required|max:150');
        $form->text('lng', __('Longitude'))->rules('required|max:150');
        $form->text('zip_code', __('Zip Code'))->rules('required|max:150');
        $form->text('license_no', __('License No'))->rules('required|max:150');
        $form->select('status', __('Status'))->options(Status::where('slug','general')->pluck('status_name','id'))->rules(function ($form) {
            return 'required';
        });
        $form->hidden('admin_user_id')->default(0);
        
        $form->saving(function ($form) {
            if($form->password && $form->model()->password != $form->password)
            {
                $form->password = $this->getEncryptedPassword($form->password);
            }
            if($form->username && $form->model()->username != $form->username)
            {
                DB::table('admin_users')->where('id',$form->admin_user_id)->update([ 'username' => $form->username ]);
                
            }
            
            if($form->restaurant_name && $form->model()->restaurant_name != $form->restaurant_name)
            {
                DB::table('admin_users')->where('id',$form->admin_user_id)->update([ 'name' => $form->restaurant_name ]);
                
            }
            
            if(!$form->model()->id){
                $id = DB::table('admin_users')->insertGetId(
                        ['username' => $form->username, 'password' => $form->password, 'name' => $form->restaurant_name, 'avatar' => $form->restaurant_image]
                    );

                    DB::table('admin_role_users')->insert(
                        ['role_id' => 2, 'user_id' => $id ]
                    );
                $form->admin_user_id = $id;
            }
        });  
        
        $form->saved(function (Form $form) {
            $this->update_profile_image($form->admin_user_id,$form->model()->profile_picture);
            $this->update_status($form->model()->id,$form->model()->status,$form->model()->restaurant_name);
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
    
    public function update_status($id,$status,$res_nme){
       $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $newPost = $database
        ->getReference('restaurants/'.$id)
        ->update([
            'res_nme' => $res_nme,
            'is_opn' => 0,
            'o_stat' => 0
        ]);
    }

    public function update_profile_image($id,$avatar){
        DB::table('admin_users')
            ->where('id', $id)
            ->update(['avatar' => $avatar]); 
    }
}

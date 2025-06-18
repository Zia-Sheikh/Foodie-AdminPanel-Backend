<?php

namespace App\Admin\Controllers;

use App\Models\Item;
use App\Models\FoodType;
use App\Models\OptionGroup;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\FoodCuisine;
use App\Models\Restaurant;
use App\Models\RestaurantCategory;
use App\Models\Tag;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Admin;

class ItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Item';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Item());
        
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }
        $grid->column('id', __('Id'));
        $grid->column('restaurant_id', __('Restaurant'))->display(function($res_id){
            $res_name = Restaurant::where('id',$res_id)->value('restaurant_name');
            return $res_name;
        });
        $grid->column('category_id', __('Category '))->display(function($category_id){
            $category_name = Category::where('id',$category_id)->value('category_name');
            return $category_name;
        });
        $grid->column('item_name', __('Item Name'));
       // $grid->column('item_description', __('Item description'));
        $grid->column('food_type', __('Food Type'))->display(function($food_type){
            $food_type = FoodType::where('id',$food_type)->value('type_name');
            return $food_type;
        });
       
        $grid->column('base_price', __('Base Price'));
        $grid->column('serves', __('Serves'));
        $grid->column('preparation_time', __('Preparation Time'));
        $grid->column('next_serving_start_from', __('Serving Time'))->display(function($next_serving_start_from){
            return date('H:i A',strtotime($next_serving_start_from)).'-'.date('H:i A',strtotime($this->next_serving_end_from));
        });
        $grid->column('item_tag', __('Item tag'))->display(function($tag_name){
            $tag_name = Tag::where('id',$tag_name)->value('tag_name');
            return $tag_name;
        });
        $grid->column('is_recommand_tag', __('Is recommand tag'))->display(function($type){
            if ($type == 0) {
                return "<span class='label label-success'>No</span>";
            } if ($type == 1) {
                return "<span class='label label-info'>Yes</span>";
             } 
        });
        $grid->column('in_stock', __('In Stock'))->display(function($type){
            if ($type == 0) {
                return "<span class='label label-success'>No</span>";
            } if ($type == 1) {
                return "<span class='label label-info'>Yes</span>";
             } 
        });
        if(Admin::user()->isAdministrator()){
            $grid->column('is_approved', __('Is Approved'))->display(function($type){
                if ($type == 0) {
                    return "<span class='label label-success'>No</span>";
                } if ($type == 1) {
                    return "<span class='label label-info'>Yes</span>";
                 } 
            });
        }
       
      

        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function ($filter) {
            //Get All status
        $categories = Category::pluck('category_name', 'id');
        $sub_categories = SubCategory::pluck('sub_category_name', 'id');
        $food_types = FoodType::pluck('type_name', 'id');
        $food_cuisines = FoodCuisine::pluck('cuisine_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        
        if(!Admin::user()->isAdministrator()){
            $filter->equal('in_stock', __('In stock'))->select(['0' => 'No', '1'=> 'Yes'])->default('m');
            $filter->equal('is_approved', __('Is Approved'))->select(['0' => 'No', '1'=> 'Yes'])->default('m');
            $filter->like('item_name', __('Item Name'));
            $filter->equal('food_type', __('Food Type'))->select($food_types);
        }else{
            $filter->equal('restaurant_id', __('Restaurant id'))->select($restaurants);
            $filter->equal('in_stock', __('In stock'))->select(['0' => 'No', '1'=> 'yes'])->default('m');
            $filter->equal('is_approved', __('Is Approved'))->select(['0' => 'No', '1'=> 'Yes'])->default('m');
            $filter->like('item_name', __('Item Name'));
            $filter->equal('food_type', __('Food Type'))->select($food_types);
        }

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
        $form = new Form(new Item());
        $categories = Category::where('status',1)->pluck('category_name', 'id');
        $sub_categories = SubCategory::pluck('sub_category_name', 'id');
        $food_types = FoodType::pluck('type_name', 'id');
        $food_cuisines = FoodCuisine::pluck('cuisine_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $tag_name = Tag::pluck('tag_name', 'id');
        $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');
        $category = Category::whereIn('created_by',[1,Admin::user()->id])->where('status',1)->pluck('category_name', 'id');
        
        if(!Admin::user()->isAdministrator()){
            $form->hidden('restaurant_id')->value($restaurant_id);
            $form->select('category_id', __('Category'))->options($category);
        }else{
            $form->select('restaurant_id', __('Restaurant'))->options($restaurants)->help('Optional');
            $form->select('category_id', __('Category'))->options($categories);
        }

        $form->text('item_name', __('Item Name'))->rules(function ($form) {
            return 'required|max:150';
        });
        $form->textarea('item_description', __('Item Description'))->rules('required');
        $form->text('item_name_ar', __('Item Name Ar'))->rules(function ($form) {
            return 'max:250';
        });
        $form->textarea('item_description_ar', __('Item Description Ar'))->rules('required');
        $form->select('food_type', __('Food Type'))->options($food_types)->default(1)->rules(function ($form) {
            return 'required';
        });
        $form->decimal('base_price', __('Base Price'))->rules('required');
        $form->text('serves', __('Serves'))->rules(function ($form) {
            return 'required|max:150';
        });

        $form->select('item_tag', __('Item Tag'))->options($tag_name)->rules(function ($form) {
            return ;
        });
        $form->image('item_image', __('Item Image'))->uniqueName()->move('items/')->rules('required');
        $form->number('preparation_time', __('Preparation Time'))->rules('required');
        $form->select('is_recommand_tag', __('Is Recommand Tag'))->options(['0' => 'No', '1'=> 'Yes'])->default('1')->rules('required');
        $form->select('in_stock', __('In Stock'))->rules('required')->options(['0' => 'No', '1'=> 'Yes'])->default('1');
        $form->timeRange('next_serving_start_from', 'next_serving_end_from', 'Serving Time');
        if(Admin::user()->isAdministrator()){
           $form->select('is_approved', __('Is Approved'))->rules('required')->options(['0' => 'No', '1'=> 'Yes'])->default('1');
        }
        $form->saved(function ($form) {
           $this->check_restaurant_category($form->model()->restaurant_id, $form->model()->category_id);
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
    
    public function check_restaurant_category($restaurant_id,$category_id){
        
        $id = RestaurantCategory::where('restaurant_id',$restaurant_id)->where('category_id',$category_id)->first();
        if(!is_object($id)){
            RestaurantCategory::create([ 'restaurant_id' => $restaurant_id, 'category_id' => $category_id ]);
        }
    }
}

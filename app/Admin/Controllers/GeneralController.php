<?php

namespace App\Admin\Controllers;
use App\Models\Category;
use App\Models\Restaurant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;

class GeneralController extends Controller
{
    use ModelForm;

    public function GetCategory()
    {   $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');
        return Category::where('restaurant_id',$_GET['q'])->get(['id', DB::raw('category_name')]);
    }

      
} 

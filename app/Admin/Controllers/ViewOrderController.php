<?php

namespace App\Admin\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\CustomerAddress;
use App\Models\DeliveryBoy;
use App\Models\CustomerAppSetting;
use App\Models\PaymentMode;
use App\Models\OrderStatus;
use App\Models\Restaurant;
use App\Models\OrderItem;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;
class ViewOrderController extends Controller
{
    public function index($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Order Details');
            $content->description('View');
            $order_details = Order::where('id',$id)->first();
            $app_setting = CustomerAppSetting::first();
            $data = array();
            $data['order_id'] = $order_details->order_id;
            $data['customer_name'] = Customer::where('id',$order_details->customer_id)->value('customer_name');
            $data['restaurant_name'] = Restaurant::where('id',$order_details->restaurant_id)->value('restaurant_name');
            $data['restaurant_phone_number'] = Restaurant::where('id',$order_details->restaurant_id)->value('restaurant_phone_number');
            $data['phone_number'] = Customer::where('id',$order_details->customer_id)->value('phone_number');
            $data['address'] = CustomerAddress::where('id',$order_details->address_id)->value('address');
            $data['delivered_by'] = (DeliveryBoy::where('id',$order_details->delivered_by)->value('delivery_boy_name') != '' ) ? DeliveryBoy::where('id',$order_details->delivered_by)->value('delivery_boy_name') : "---" ;
            $data['payment_mode'] = PaymentMode::where('id',$order_details->payment_mode)->value('payment_name');
            $data['sub_total'] = $app_setting->default_currency.$order_details->sub_total;
            $data['delivery_charge'] = $app_setting->default_currency.$order_details->delivery_charge;
            $data['platform_fee'] = $app_setting->default_currency.$order_details->platform_fee;
            $data['discount'] =  $app_setting->default_currency.$order_details->discount;
            $data['total'] =  $app_setting->default_currency.$order_details->total;
            $data['status'] =  OrderStatus::where('id',$order_details->status)->value('status');
            $order_items = DB::table('order_items')
            ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
            ->select('order_items.item_id','items.item_name','order_items.quantity','order_items.price_per_item','order_items.total')
            ->where('order_items.order_id',$order_details->id)
            ->orderBy('order_items.created_at', 'asc')
            ->get();
            foreach($order_items as $key => $value){
                $order_items[$key]->options = DB::table('order_item_options')->where('item_id',$value->item_id)->where('order_id',$order_details->id)->get();
            }
            $data['order_items'] = $order_items;
            $content->body(view('admin.view_orders', $data));
        });

    }
}

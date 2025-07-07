<?php

namespace App\Admin\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Restaurant;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\PromoCode;
use App\Models\PaymentMode;
use App\Models\DeliveryBoy;
use App\Models\DeliveryBoyAppSetting;
use App\Models\DeliveryBoyEarning;
use App\Models\DeliveryBoyWalletHistory;
use App\Models\RestaurantAppSetting;
use App\Models\RestaurantEarning;
use App\Models\RestaurantWalletHistory;
use App\Models\OrderCommission;
use App\Models\PartnerRejection;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;
use Admin;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;

class OrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Order';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order());
        
        $grid->model()->orderBy('id','desc');
        if(!Admin::user()->isAdministrator()){
            $grid->model()->where('restaurant_id', Restaurant::where('admin_user_id',Admin::user()->id)->value('id'));
        }
        $grid->column('id', __('Id'));
        $grid->column('customer_id', __('Customer '))->display(function($customer_id){
            $customer_name = Customer::where('id',$customer_id)->value('customer_name');
            return $customer_name;
        });
        $grid->column('restaurant_id', __('Restaurant '))->display(function($restaurant_id){
            $restaurant_name = Restaurant::where('id',$restaurant_id)->value('restaurant_name');
            return $restaurant_name;
        });
        $grid->column('total', __('Total'));
        $grid->column('sub_total', __('Sub Total'));
        $grid->column('discount', __('Discount'));
        $grid->column('tax', __('Tax'));
        $grid->column('platform_fee', __('Platform Fee'));
        $grid->column('promo_id', __('Promo '))->display(function($promo_codes){
            if($promo_codes == 0){
                return "NILL";
            }else{
                $promo_codes = PromoCode::where('id',$promo_codes)->value('promo_name');
                return $promo_codes;
            }
        });
        $grid->column('delivery_charge', __('Delivery Charge'));
        $grid->column('delivery_instruction', __('Delivery Instruction'))->display(function($status){
            if ($status == "") {
                return "NILL";
            } else{
                return "$this->delivery_instruction";
            }
        });
        $grid->column('payment_mode', __('Payment Mode'))->display(function($status){
            $status_name = PaymentMode::where('id',$status)->value('payment_name');
                return"$status_name";
        });
        $grid->column('delivered_by', __('Delivered by'))->display(function($delivered_by){
            if($delivered_by){
                return DeliveryBoy::where('id',$delivered_by)->value('delivery_boy_name');
            }else{
                return '---';
            }
        });
        $grid->column('status', __('Status'))->display(function($status){
            $status_name = OrderStatus::where('id',$status)->value('status');
            if ($status == 8) {
                return "<span class='label label-success'>$status_name</span>";
            } else {
                return "<span class='label label-warning'>$status_name</span>";
            } 
        });
        $grid->column('token', __('Token'));
        $grid->column('View Orders')->display(function () {
            return "<a href='/admin/view_orders/".$this->id."'><span class='label label-info'>View Orders</span></a>";
        });

        $grid->disableExport();
        // $grid->disableCreateButton();
        $grid->actions(function ($actions) {
                $actions->disableView();
            });
        $grid->filter(function ($filter) {
            //Get All status
            $res_statuses = OrderStatus::whereIn('slug',['restaurant_approved','restaurant_rejected','ready_to_dispatch','cancelled_by_restaurant'])->pluck('status', 'id');
            $payments = PaymentMode::pluck('payment_name', 'id');
            $statuses = OrderStatus::pluck('status', 'id');
            $restaurants = Restaurant::pluck('restaurant_name', 'id');
            $customers = Customer::pluck('customer_name', 'id');
            $promo_codes = Promocode::pluck('promo_name', 'id');
            $delivery_boys = DeliveryBoy::pluck('delivery_boy_name', 'id');
            
        if(!Admin::user()->isAdministrator()){
            $filter->equal('status', __('Status'))->select($statuses);
            $filter->equal('payment_mode', __('Payment Mode'))->select($payments);
        }else{
            $filter->equal('customer_id', __('Customer'))->select($customers);
            $filter->equal('restaurant_id', __('Restaurant'))->select($restaurants);
            $filter->equal('promo_id', __('Promo'))->select($promo_codes);
            $filter->equal('status', __('Status'))->select($statuses);
            $filter->equal('payment_mode', __('Payment Mode'))->select($payments);
            $filter->equal('delivered_by', __('Delivered By'))->select($delivery_boys);
        }
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
        $show = new Show(Order::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('customer_id', __('Customer id'));
        $show->field('restaurant_id', __('Rest id'));
        $show->field('address_id', __('Address id'));
        $show->field('total', __('Total'));
        $show->field('sub_total', __('Sub total'));
        $show->field('discount', __('Discount'));
        $show->field('tax', __('Tax'));
        $show->field('promo_id', __('Promo id'));
        $show->field('delivery_charge', __('Delivery charge'));
        $show->field('special_instruction', __('Special instruction'));
        $show->field('delivery_instruction', __('Delivery instruction'));
        $show->field('status', __('Status'));
        $show->field('placed_at', __('Placed at'));
        $show->field('delivered_at', __('Delivered at'));
        $show->field('payment_mode', __('Payment mode'));
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
        $form = new Form(new Order());
        $statuses = OrderStatus::pluck('status', 'id');
        $res_statuses = OrderStatus::whereIn('slug',['restaurant_approved','restaurant_rejected','ready_to_dispatch','cancelled_by_restaurant'])->pluck('status', 'id');
        $delivery_boys = DeliveryBoy::where('status',1)->pluck('delivery_boy_name', 'id');
        $restaurants = Restaurant::pluck('restaurant_name', 'id');
        $restaurant_id = Restaurant::where('admin_user_id',Admin::user()->id)->value('id');

        if(!Admin::user()->isAdministrator()){
            $form->hidden('restaurant_id')->value($restaurant_id);
        }else{
            $form->select('restaurant_id', __('Restaurant '))->options($restaurants);
        }
        if(!Admin::user()->isAdministrator()){
            $form->select('status', __('Status'))->options($res_statuses)->default(1)->rules(function ($form) {
            return 'required';
        });
        }else{
           $form->select('delivered_by', __('Delivered by'))->options($delivery_boys);
           $form->select('status', __('Status'))->options($statuses)->default(1)->rules(function ($form) {
            return 'required';
        });
        }
        $form->saving(function (Form $form) {
           if($form->delivered_by > 0 && $form->status ==1){
                $error = new MessageBag([
                    'title'   => 'Warning',
                    'message' => 'Please change order status...',
                ]);

                return back()->with(compact('error'));
           }
        });
        
        $form->saved(function (Form $form) {
            
            $res_id = DB::table('restaurants')->where('id',$form->restaurant_id)->value('id');
            $slug = DB::table('order_statuses')->where('id',$form->status)->value('slug');
            $payment_type = DB::table('payment_modes')->where('id',$form->model()->payment_mode)->value('slug');
            
            if($slug == "cancelled_by_customer"){
            if($payment_type != "cash" && $order_slug == "order_placed"){
                $old_wallet = Customer::where('id',$form->model()->customer_id)->value('wallet');
                $new_wallet = $old_wallet + $form->model()->total;
                Customer::where('id',$form->model()->customer_id)->update([ 'wallet' => $new_wallet ]);
                
                $data['customer_id'] = $form->model()->customer_id;
                $data['type'] = 1;
                $data['message'] ="Amount refunded to wallet";
                $data['amount'] = $form->model()->total;
                $data['transaction_type'] = 2;
                CustomerWalletHistory::create($data);  
            }else if($slug != "order_placed"){
                $old_wallet = Customer::where('id',$form->model()->customer_id)->value('wallet');
                $new_wallet = $old_wallet - $form->model()->total;
                Customer::where('id',$form->model()->customer_id)->update([ 'wallet' => $new_wallet ]);
                
                $data['customer_id'] = $form->model()->customer_id;
                $data['type'] = 2;
                $data['message'] ="Cancellation charge deducted from your wallet";
                $data['amount'] = $form->model()->total;
                $data['transaction_type'] = 3;
                CustomerWalletHistory::create($data); 
            }
        } 
        if($slug == "cancelled_by_restaurant" || $slug == "cancelled_by_deliveryboy"){
            if($payment_type != "cash"){
                $old_wallet = Customer::where('id',$form->model()->customer_id)->value('wallet');
                $new_wallet = $old_wallet + $form->model()->total;
                Customer::where('id',$form->model()->customer_id)->update([ 'wallet' => $new_wallet ]);
                
                $data['customer_id'] = $form->model()->customer_id;
                $data['type'] = 1;
                $data['message'] ="Amount refunded to wallet";
                $data['amount'] = $form->model()->total;
                $data['transaction_type'] = 2;
                CustomerWalletHistory::create($data);  
            }
        } 
        
        if($slug == "restaurant_approved" || $slug == "restaurant_rejected"){
            $this->check_restaurant_booking($form->model()->restaurant_id);
            if($slug  == "restaurant_rejected" && $payment_type != "cash"){
                $this->add_customer_wallet($form->model()->customer_id,$form->model()->total);
            }
            
        } 
        if($slug == "ready_to_dispatch"){
            $this->find_partner($form->model()->id,$res_id);
        }   
        if($slug == "delivered"){
            $this->commission_calculations($form->model()->id);
            $this->update_deliveryboy_status($form->delivered_by);
        }
         
        $this->update_status($form->model()->id,$form->status);
        //$this->find_fcm_message('order_status_'.$old_label->id,$order->customer_id,0,0);
           
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
    
    public function update_status($id,$status){
        
            $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
                $database = $factory->createDatabase();
                $database->getReference('orders/'.$id)
                ->update([
                    'status' => $status
                ]);
                
    }
    
    public function check_restaurant_booking($id){
            $count = DB::table('orders')->where('restaurant_id',$id)->where('status',1)->count();
            $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();
            
            if($count == 0){
                DB::table('restaurants')->where('id',$id)->update(['order_status' => 0]);
                $database->getReference('restaurants/'.$id)
                ->update([
                    'o_stat' => 0,
                ]);
            }
            
            if($count){
                if(!DB::table('restaurants')->where('id',$id)->value('order_status')){
                    DB::table('restaurants')->where('id',$id)->update(['order_status' => 1]);
                    $database->getReference('restaurants/'.$id)
                    ->update([
                        'o_stat' => 1,
                    ]);
                }
            }
        
    }
    
    
    public function update_deliveryboy_status($del_id){
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();
            $database->getReference('delivery_partners/'.$del_id)
                ->update([
                    'o_id' => 0,
                    'o_stat' => 0
                ]);
                
    }
    
    public function commission_calculations($order_id){
        $order = Order::where('id',$order_id)->first();
        $cus_address = DB::table('customer_addresses')->where('id',$order->address_id)->first();
        $res = DB::table('restaurants')->where('id',$order->restaurant_id)->first();
        $distance =  $this->distance($cus_address->lat,$cus_address->lng,$res->lat,$res->lng,'K');
        $admin_percent = RestaurantAppSetting::where('id',1)->value('order_commission');
        $delivery_charge = DeliveryBoyAppSetting::where('id',1)->value('delivery_charge_per_km');
        
        $admin_commission = ($order->amount / 100) * $admin_percent; 
        $admin_commission = number_format((float)$admin_commission, 2, '.', '');
        
        $restaurant_commission = $order->amount - $admin_commission;
        $restaurant_commission = number_format((float)$restaurant_commission, 2, '.', '');
        
        $delivery_boy_commission = $distance*$delivery_charge;
        $delivery_boy_commission = number_format((float)$delivery_boy_commission, 2, '.', '');
        
        $order_commission['order_id'] = $order_id;
        $order_commission['role'] = 'restaurant';
        $order_commission['user_id'] = $order->restaurant_id;
        $order_commission['amount'] = $restaurant_commission;
        OrderCommission::create($order_commission);
        
        $order_commission['order_id'] = $order_id;
        $order_commission['role'] = 'deliveryboy';
        $order_commission['user_id'] = $order->delivered_by;
        $order_commission['amount'] = $delivery_boy_commission;
        OrderCommission::create($order_commission);
        
        $order_commission['order_id'] = $order_id;
        $order_commission['role'] = 'admin';
        $order_commission['user_id'] = 1;
        $order_commission['amount'] = $admin_commission;
        OrderCommission::create($order_commission);
        
        RestaurantEarning::create([ 'order_id' => $order_id, 'restaurant_id' => $order->restaurant_id, 'amount' => $delivery_boy_commission]);
        RestaurantWalletHistory::create([ 'restaurant_id' => $order->restaurant_id, 'type' => 1, 'message' => 'Your earnings credited for this order #'.$order->id, 'amount' => $restaurant_commission]);
        
        $wallet = Restaurant::where('id',$order->restaurant_id)->value('wallet');
        $new_wallet = $wallet + $restaurant_commission;
        $new_wallet = number_format((float)$new_wallet, 2, '.', '');
        
        Restaurant::where('id',$order->restaurant_id)->update([ 'wallet' => $new_wallet]);
        
        DeliveryBoyEarning::create([ 'order_id' => $order_id, 'delivery_boy_id' => $order->delivered_by, 'amount' => $delivery_boy_commission]);
        DeliveryBoyWalletHistory::create([ 'delivery_boy_id' => $order->delivered_by, 'type' => 1, 'message' => 'Your earnings credited for this order #'.$order->id, 'amount' => $delivery_boy_commission]);
        
        $del_wallet = DeliveryBoy::where('id',$order->delivered_by)->value('wallet');
        $del_new_wallet = $del_wallet + $delivery_boy_commission;
        $del_new_wallet = number_format((float)$del_new_wallet, 2, '.', '');
        
        DeliveryBoy::where('id',$order->delivered_by)->update([ 'wallet' => $del_new_wallet]);
        
    }
    
    public function distance($lat1, $lon1, $lat2, $lon2, $unit) {

      $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtoupper($unit);
    
      if ($unit == "K") {
         $km = ($miles * 1.609344);
         if($km < 1){
            return 1;
         }else{
            return (int) $km;
         }
      } else if ($unit == "N") {
         return ($miles * 0.8684);
      } else {
         return $miles;
      }
    }
    
    public function find_partner($order_id,$res_id)
    {
        $res_lat = DB::table('restaurants')->where('id',$res_id)->value('lat');
        $res_lng = DB::table('restaurants')->where('id',$res_id)->value('lng');
        
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        
        $partners = $database->getReference('/delivery_partners')
                    ->getSnapshot()->getValue();
        //print_r($partners);exit;
        $rejected_partners = PartnerRejection::where('order_id',$order_id)->pluck('partner_id')->toArray();
        $min_partner_id = 0;
        $min_distance = 0;
        $booking_searching_radius = DB::table('delivery_boy_app_settings')->where('id',1)->value('booking_searching_radius');
        
        $i=0;
        foreach($partners as $key => $value){
            if(is_array($value)){
                if($value['o_stat'] == 0 && $value['on_stat'] == 1){
                    if(!in_array($value['p_id'], $rejected_partners)){
                        $distance = $this->distance($res_lat, $res_lng, $value['lat'], $value['lng'], 'K') ;
                        //$driver_wallet = Driver::where('id',$value['driver_id'])->value('wallet');
                            if($distance <= $booking_searching_radius){
                                if($min_distance == 0 && $i == 0){
                                    $min_distance = $distance;
                                    $min_partner_id = $value['p_id'];
                                }else if($distance < $min_distance){
                                    $min_distance = $distance;
                                    $min_partner_id = $value['p_id'];
                                }
                                $i++;
                            }
                        }   
                    }
            }
            
        }    
        if($min_partner_id == 0){
            return response()->json([
                "message" => 'Sorry partners not available right now',
                "status" => 0
            ]);
        }else{
            $newPost = $database
            ->getReference('delivery_partners/'.$min_partner_id)
            ->update([
                'o_stat' => 1,
                'o_id' => $order_id
            ]);
        }
    }
    
     public function add_customer_wallet($cust_id,$total){
        
            $old_wallet = Customer::where('id',$cust_id)->value('wallet');
                $new_wallet = $old_wallet + $total;
                Customer::where('id',$cust_id)->update([ 'wallet' => $new_wallet ]);
                
                $data['customer_id'] = $cust_id;
                $data['type'] = 1;
                $data['message'] ="Amount refunded to wallet";
                $data['amount'] = $total;
                $data['transaction_type'] = 2;
                CustomerWalletHistory::create($data); 
    }

}

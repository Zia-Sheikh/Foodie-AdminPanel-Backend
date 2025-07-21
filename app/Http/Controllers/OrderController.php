<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Customer;
use App\Models\CustomerWalletHistory;
use App\Models\Restaurant;
use App\Models\CustomerAppSetting;
use App\Models\OrderItem;
use App\Models\OrderItemOptions;
use App\Models\OrderStatus;
use App\Models\PaymentMode;
use App\Models\PromoCode;
use App\Models\CustomerAddress;
use App\Models\RestaurantAppSetting;
use App\Models\OrderCommission;
use App\Models\RestaurantEarning;
use App\Models\RestaurantWalletHistory;
use App\Models\DeliveryBoyEarning;
use App\Models\DeliveryBoyWalletHistory;
use App\Models\DeliveryBoy;
use App\Models\DeliveryBoyAppSetting;
use App\Models\OrderRating;
use App\Models\PartnerRejection;
use App\Models\CustomerPromoHistory;
use Mail;
use Validator;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{   
    public function calculate_tax(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'items' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $items = json_decode(stripslashes($input['items']), true);
        
        $tax = 0;
        foreach($items as $key => $value){
            $tax_percent = DB::table('items')->join('categories','categories.id','=','items.category_id')->where('items.id','=',$value['item_id'])->value('categories.tax');
            $amount = ($tax_percent / 100) * $value['net_total'];
            $tax = $tax+$amount;
        }
        return response()->json([
            "result" => $tax,
            "message" => 'Success',
            "status" => 1
        ]);
    }
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'restaurant_id' => 'required',
            'total' => 'required',
            'discount' => 'required',
            'platform_fee' => 'required',
            'sub_total' => 'required',
            'tax' => 'required',
            'delivery_charge' => 'required',
            'promo_id' => 'required',
            'payment_mode' => 'required',
            'items' => 'required',
            'options' => 'required',
            'order_type' => 'required',
            'order_date' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
       
        if($input['order_type'] != 1){
            $input['delivery_charge'] = 0;
            $input['status'] = 2;
        }else{
            $input['status'] = 1;
        }
        
        $date = explode('/',$input['order_date']);
        $input['order_date'] = date('Y-m-d H:i:s', strtotime($input['order_date']));
        $items = json_decode(stripslashes($input['items']), true);
        $options = json_decode(stripslashes($input['options']), true);
        $payment_type = PaymentMode::where('id',$input['payment_mode'])->value('slug');
        if($payment_type == "wallet"){
            $payment = $this->deduct_wallet($input['customer_id'],$input['total']);
            if($payment == 0){
                return response()->json([
                    "message" => 'Your wallet balance is low!',
                    "status" => 0
                ]);
            }
        }
        
        $order = Order::create($input);
        if($input['order_type'] != 1){
            Order::where('id',$order->id)->update([ 'token' => 'ORD'.$input['restaurant_id'].'-'.$order->id ]);   
        }
        Customer::where('id',$input['customer_id'])->update([ 'last_active_address'=>$input['address_id']]);
        if (is_object($order)) {
            foreach ($items as $key => $value) {
                if($value){
                   $insert['order_id'] = $order->id;
                   $insert['item_id'] = $value['item_id'];
                   $insert['item_name'] = $value['item_name'];
                   $insert['quantity'] = $value['qty'];
                   $insert['price_per_item'] = $value['price'];
                   $insert['total'] = $value['net_total'];
                   OrderItem::create($insert); 
                }
            }
            foreach ($options as $key => $value) {
                if($value){
                   $insert['order_id'] = $order->id;
                   $insert['item_id'] = $value['item_id'];
                   $insert['slug'] = $value['slug'];
                   $insert['slug_value'] = $value['slug_value'];
                   $insert['qty'] = $value['qty'];
                   $insert['price'] = $value['price'];
                   $insert['total'] = $value['total'];
                   OrderItemOptions::create($insert); 
                }
            }
            //$this->find_fcm_message('order_status_'.$order->status,$order->customer_id,0,0);
            $this->order_registers($order->id);
            $this->check_restaurant_booking($input['restaurant_id']);
            $this->update_status($order->id,$input['status']);
            $fcm_token = DB::table('customers')->where('id',$input['customer_id'])->value('fcm_token');
            $this->send_order_fcm(1,$fcm_token,1);
            return response()->json([
                "result" => $order->id,
                "message" => 'Order Placed Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    
    public function deduct_wallet($customer_id,$amount){
        
        $old_wallet = Customer::where('id',$customer_id)->value('wallet');
        if($old_wallet < $amount){
            return 0;
        }
        $data['customer_id'] = $customer_id;
        $data['type'] = 1;
        $data['message'] ="Paid by wallet";
        $data['amount'] = $amount;
        $data['transaction_type'] = 3;
        CustomerWalletHistory::create($data);
    
        $new_wallet = $old_wallet - $amount;
        Customer::where('id',$customer_id)->update([ 'wallet' => $new_wallet ]);
        
        return 1;
    }
    
    
    
    public function order_registers($id){
        
        $app_setting = CustomerAppSetting::where('id',1)->first();
        
        $data = array();
        $orders = Order::where('id',$id)->first();
        $customer = Customer::where('id',$orders->customer_id)->first();
        $data['order_id'] = $orders->order_id;
        $data['logo'] = $app_setting->logo;
        $data['name'] = $customer->customer_name;
        $data['address'] = CustomerAddress::where('id',$orders->address_id)->value('address');
        $data['items'] = json_decode($orders->items, TRUE);
        $data['total'] = $orders->total;
        $data['discount'] = $orders->discount;
        $data['delivery_charge'] = $orders->delivery_charge;
        $data['sub_total'] = $orders->sub_total;
        $data['tax'] = $orders->tax;
        $data['payment_mode'] = PaymentMode::where('id',$orders->payment_mode)->value('payment_name');
        $mail_header = array("data" => $data);
        //$this->send_order_mail($mail_header,'Order Placed Successfully',$customer->email,'mail_templates.invoice');
        //$this->send_order_mail($mail_header,'New order Received',$app_setting->email,'mail_templates.new_order');
    }
    
    public function restaurant_order_accept(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'type' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['type'] == "accept"){
            $status = Db::table('order_statuses')->where('slug','restaurant_approved')->value('id');
        }else{
            $status = Db::table('order_statuses')->where('slug','restaurant_rejected')->value('id');
        }
        Order::where('id',$input['order_id'])->update([ 'status' => $status]);
        $order = Order::where('id',$input['order_id'])->first();
        $payment_type = PaymentMode::where('id',$order->payment_mode)->value('slug');
        if($input['type']  == "reject" && $payment_type != "cash"){
            $old_wallet = Customer::where('id',$order->customer_id)->value('wallet');
            $new_wallet = $old_wallet + $order->total;
            Customer::where('id',$order->customer_id)->update([ 'wallet' => $new_wallet ]);
            
            if($input['lang'] == 'en'){
                $message = "Amount refunded to wallet";
            }else{
                $message = "تم إرجاع المبلغ إلى المحفظة";
            }
            $data['customer_id'] = $order->customer_id;
            $data['type'] = 3;
            $data['message'] = $message;
            $data['amount'] = $order->total;
            $data['transaction_type'] = 1;
            CustomerWalletHistory::create($data); 
        }
        $this->check_restaurant_booking($order->restaurant_id);
        $this->update_status($input['order_id'],$status);
        //$this->find_fcm_message('order_status_'.$order->status,$order->customer_id,0,0);
        $fcm_token = DB::table('customers')->where('id',$order->customer_id)->value('fcm_token');
        $this->send_order_fcm($status,$fcm_token,1);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

   public function check_restaurant_booking($id){
        $res_data = DB::table('restaurants')->where('id',$id)->first();
        
            $count = DB::table('orders')->where('restaurant_id',$id)->where('status',1)->count();
            $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();
            if($count == 0){
                DB::table('restaurants')->where('id',$id)->update(['order_status' => 0]);
                $database->getReference('restaurants/'.$id)
                ->update([
                    'o_stat' => 0,
                ]);
            }else{
                if(!DB::table('restaurants')->where('id',$id)->value('order_status')){
                    DB::table('restaurants')->where('id',$id)->update(['order_status' => 1]);
                    $database->getReference('restaurants/'.$id)
                    ->update([
                        'o_stat' => 1,
                    ]);
                }
            }
    }
    
     public function commission_calculations($order_id,$lang){
        $order = Order::where('id',$order_id)->first();
        $cus_address = CustomerAddress::where('id',$order->address_id)->first();
        $res = Restaurant::where('id',$order->restaurant_id)->first();
        $distance =  $this->distance($cus_address->lat,$cus_address->lng,$res->lat,$res->lng,'K');
        $admin_percent = RestaurantAppSetting::where('id',1)->value('order_commission');
        $delivery_charge = DeliveryBoyAppSetting::where('id',1)->value('delivery_charge_per_km');
        
        $admin_commission = ($order->total / 100) * $admin_percent; 
        $admin_commission = number_format((float)$admin_commission, 2, '.', '');
        
        $restaurant_commission = $order->total - $admin_commission;
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
        
        if($lang == 'en'){
            $message = 'Your earnings credited for this order #';
        }else{
            $message = 'تم إضافة أرباحك لهذا الطلب #';
        }
        
        RestaurantEarning::create([ 'order_id' => $order_id, 'restaurant_id' => $order->restaurant_id, 'amount' => $delivery_boy_commission]);
        RestaurantWalletHistory::create([ 'restaurant_id' => $order->restaurant_id, 'type' => 1, 'message' => $message.$order->id, 'amount' => $restaurant_commission]);
        
        $wallet = Restaurant::where('id',$order->restaurant_id)->value('wallet');
        $new_wallet = $wallet + $restaurant_commission;
        $new_wallet = number_format((float)$new_wallet, 2, '.', '');
        
        Restaurant::where('id',$order->restaurant_id)->update([ 'wallet' => $new_wallet]);
        
        DeliveryBoyEarning::create([ 'order_id' => $order_id, 'delivery_boy_id' => $order->delivered_by, 'amount' => $delivery_boy_commission]);
        DeliveryBoyWalletHistory::create([ 'delivery_boy_id' => $order->delivered_by, 'type' => 1, 'message' => $message.$order->id, 'amount' => $delivery_boy_commission]);
        
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

    
    public function rating_upload(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'restaurant_id' => 'required',
            'order_id' => 'required',
            'rating' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $ratings = OrderRating::create($input);
        if (is_object($ratings)) {
            Order::where('id',$input['order_id'])->update([ 'rating_update_status' => 1]);
            $res_id = Order::where('id',$input['order_id'])->value('restaurant_id');
            $this->restaurant_rating($res_id);
            return response()->json([
                "result" => $ratings,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }

    }
    
    public function restaurant_rating($res_id)
    {
        $ratings_data = OrderRating::where('restaurant_id',$res_id)->get();
        $data_sum = OrderRating::where('restaurant_id',$res_id)->get()->sum("rating");
        $data = $data_sum / count($ratings_data);
        if($data){
            Restaurant::where('id',$res_id)->update(['overall_rating'=>number_format((float)$data, 1, '.', ''), 'number_of_rating'=> count($ratings_data)]);
        }
    }
    
    public function customer_cancel_order(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $order = Order::where('id',$input['order_id'])->first();
        $order_slug = OrderStatus::where('id',$order->status)->value('slug');
        $payment_type = PaymentMode::where('id',$order->payment_mode)->value('slug');
        if($payment_type != "cash" && $order_slug == "order_placed"){
            if($input['lang'] == 'en'){
                $message = 'Amount refunded to wallet';
            }else{
                $message = 'تم إرجاع المبلغ إلى المحفظة';
            }
            $old_wallet = Customer::where('id',$order->customer_id)->value('wallet');
            $new_wallet = $old_wallet + $order->total;
            Customer::where('id',$order->customer_id)->update([ 'wallet' => $new_wallet ]);
            $data['customer_id'] = $order->customer_id;
            $data['type'] = 1;
            $data['message'] = $message;
            $data['amount'] = $order->total;
            $data['transaction_type'] = 2;
            CustomerWalletHistory::create($data);  
        }else if($order_slug != "order_placed"){
            if($input['lang'] == 'en'){
                $message = 'Cancellation charge deducted from your wallet';
            }else{
                $message = 'سيتم خصم رسوم الإلغاء من محفظتك';
            }
            
            $old_wallet = Customer::where('id',$order->customer_id)->value('wallet');
            $new_wallet = $old_wallet - $order->total;
            Customer::where('id',$order->customer_id)->update([ 'wallet' => $new_wallet ]);
            $data['customer_id'] = $order->customer_id;
            $data['type'] = 2;
            $data['message'] = $message;
            $data['amount'] = $order->total;
            $data['transaction_type'] = 3;
            CustomerWalletHistory::create($data); 
        }
        $status = Db::table('order_statuses')->where('slug','cancelled_by_customer')->value('id');
        Order::where('id',$input['order_id'])->update([ 'status' => $status]);
        $order = Order::where('id',$input['order_id'])->first();
        $this->update_status($input['order_id'],$status);
        if($order->delivered_by){
            $this->update_deliveryboy_status($order->delivered_by);
        }
        return response()->json([
            "result" => $order,
            "message" => "Success",
            "status" => 1
        ]);
    }
   
   public function order_status_change(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'slug' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $order = Order::where('id',$input['order_id'])->first();
        $payment_type = PaymentMode::where('id',$order->payment_mode)->value('slug');
        $order_slug = OrderStatus::where('id',$order->status)->value('slug');
        $status = DB::table('order_statuses')->where('slug',$input['slug'])->value('id');
        if(is_object($order)){
            Order::where('id',$input['order_id'])->update([ 'status' => $status ]);
        }

        /*if($input['slug'] == "cancelled_by_customer"){
            if($payment_type != "cash" && $order_slug == "order_placed"){
                $old_wallet = Customer::where('id',$order->customer_id)->value('wallet');
                $new_wallet = $old_wallet + $order->total;
                Customer::where('id',$order->customer_id)->update([ 'wallet' => $new_wallet ]);
                
                $data['customer_id'] = $order->customer_id;
                $data['type'] = 1;
                $data['message'] ="Amount refunded to wallet";
                $data['amount'] = $order->total;
                $data['transaction_type'] = 2;
                CustomerWalletHistory::create($data);  
            }else if($payment_type == "cash" && $order_slug != "order_placed"){
                $old_wallet = Customer::where('id',$order->customer_id)->value('wallet');
                $new_wallet = $old_wallet - $order->total;
                Customer::where('id',$order->customer_id)->update([ 'wallet' => $new_wallet ]);
                
                $data['customer_id'] = $order->customer_id;
                $data['type'] = 2;
                $data['message'] ="Cancellation charge deducted from your wallet";
                $data['amount'] = $order->total;
                $data['transaction_type'] = 3;
                CustomerWalletHistory::create($data); 
            }
        } */
        if($input['slug'] == "cancelled_by_restaurant" || $input['slug'] == "cancelled_by_deliveryboy"){
            if($payment_type != "cash"){
                $old_wallet = Customer::where('id',$order->customer_id)->value('wallet');
                $new_wallet = $old_wallet + $order->total;
                Customer::where('id',$order->customer_id)->update([ 'wallet' => $new_wallet ]);
                
                if($input['lang'] == 'en'){
                    $message = 'Amount refunded to wallet';
                }else{
                    $message = 'تم إرجاع المبلغ إلى المحفظة';
                }
                
                $data['customer_id'] = $order->customer_id;
                $data['type'] = 1;
                $data['message'] = $message;
                $data['amount'] = $order->total;
                $data['transaction_type'] = 2;
                CustomerWalletHistory::create($data);  
            }
            
            if($order->delivered_by){
                $this->update_deliveryboy_status($order->delivered_by);
            }
        } 
        
        if($input['slug'] == "ready_to_dispatch" && $order->order_type == 1){
            $this->find_partner($input['order_id']);
        }   
        if($input['slug'] == "delivered"){
            if($order->order_type == 1){
                $this->commission_calculations($input['order_id'],$input['lang']);
                $this->update_deliveryboy_status($order->delivered_by);
            }
            
            $this->update_promo_histories($order->promo_id,$order->id);
        }
         
        $this->update_status($input['order_id'],$status);
        //$this->find_fcm_message('order_status_'.$old_label->id,$order->customer_id,0,0);
        $fcm_token = DB::table('customers')->where('id',$order->customer_id)->value('fcm_token');
        $this->send_order_fcm($status,$fcm_token,1);
        return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
            
    }

    public function update_promo_histories($promo_id,$customer_id){
        if($promo_id){
            CustomerPromoHistory::create([ 'customer_id' => $customer_id, "promo_id" =>$promo_id ]);
        }
    }
    
    public function update_status($id,$status){
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
            $database = $factory->createDatabase();
            $database->getReference('orders/'.$id)
            ->update([
                'status' => $status
            ]);
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
    
    public function find_partner($order_id)
    {
        $order = DB::table('orders')
                 ->leftjoin('restaurants','restaurants.id','orders.restaurant_id')
                 ->select('orders.*','restaurants.lat','restaurants.lng')
                 ->where('orders.id',$order_id)->first();
        
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
        //print_r($partners);exit;
        foreach($partners as $key => $value){
            if(is_array($value)){
                if(@$value['on_stat'] && $value['o_stat'] == 0 && $value['on_stat'] == 1){
                    if(!in_array($value['p_id'], $rejected_partners)){
                        $distance = $this->distance($order->lat, $order->lng, $value['lat'], $value['lng'], 'K') ;
                        //print_r($distance);exit;
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
                    }else{
                        
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
    
    public function partner_accept(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'partner_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        DB::table('orders')->where('id',$input['order_id'])->update(['delivered_by' => $input['partner_id']]);
        
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        
        $newPost = $database
        ->getReference('delivery_partners/'.$input['partner_id'])
        ->update([
            'o_stat' => 2
        ]);
  
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
        
    }
    
    public function partner_reject(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'partner_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
         DB::table('orders')->where('id',$input['order_id'])->update(['delivered_by' => 0 ]);
        
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        
        $newPost = $database
        ->getReference('delivery_partners/'.$input['partner_id'])
        ->update([
            'o_stat' => 0,
            'o_id' => 0
        ]);
        
        
        $data['partner_id'] = $input['partner_id'];
        $data['order_id'] = $input['order_id'];
        PartnerRejection::create($data);
        
        $this->find_partner($input['order_id']);
        
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function partner_cron()
    {
       
        $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
            ->where('order_statuses.slug','ready_to_dispatch')
            ->where('orders.delivered_by',0)
            ->where('orders.order_type',1)
            ->get();
            
        foreach($orders as $key => $value){
                $order_id = $value->id;
                //print_r($order_id);exit;
                $this->find_partner($order_id);
        }
        
    }
    
    public function sendError($message) {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    }
    
   

}

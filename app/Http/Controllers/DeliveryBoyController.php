<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryBoy;
use App\Models\DeliveryBoyEarning;
use App\Models\DeliveryBoyWalletHistory;
use App\Models\DeliveryBoyWithdrawal;
use Validator;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Carbon\Carbon;
use App\FcmNotification;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Illuminate\Support\Facades\DB;

class DeliveryBoyController extends Controller
{
    //
    public function login(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
            'password' => 'required',
            'fcm_token' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            //return $this->sendError($validator->errors());
        }

        $credentials = request(['phone_with_code', 'password']);
        $delivery_boy = DeliveryBoy::where('phone_with_code',$credentials['phone_with_code'])->first();

        if (!($delivery_boy)) {
            return response()->json([
                "message" => 'Invalid phone number or password',
                "status" => 0
            ]);
        }
        
        if (Hash::check($credentials['password'], $delivery_boy->password)) {
            if($delivery_boy->status == 1){
                
                DeliveryBoy::where('id',$delivery_boy->id)->update([ 'fcm_token' => $input['fcm_token']]);
                return response()->json([
                    "result" => $delivery_boy,
                    "message" => 'Success',
                    "status" => 1
                ]);   
            }else{
                if($input['lang'] == 'en'){
                    $message = 'Your account has been blocked';
                }else{
                    $message = 'لقد تم حظر حسابك';
                }
                return response()->json([
                    "message" => $message,
                    "status" => 0
                ]);
            }
        }else{
            if($input['lang'] == 'en'){
                $message = 'Invalid phone number or password';
            }else{
                $message = 'رقم الهاتف أو كلمة المرور غير صالحة';
            }
            return response()->json([
                "message" => $message,
                "status" => 0
            ]);
        }

    }

    public function check_phone(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = array();
        $delivery_boy = DeliveryBoy::where('phone_with_code',$input['phone_with_code'])->first();

        if(is_object($delivery_boy)){
            $data['is_available'] = 1;
            $data['otp'] = "";
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            $data['is_available'] = 0;
            return response()->json([
                "message" => 'Your number is not registered, please contact admin',
                "status" => 2
            ]);
        }
    }
    

    public function profile_update(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
            
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if($request->password){
            $options = [
                'cost' => 12,
            ];
            $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
            $input['status'] = 1;
        }else{
            unset($input['password']);
        }

        if (DeliveryBoy::where('id',$input['id'])->update($input)) {
            return response()->json([
                "result" => DeliveryBoy::select('id','email','phone_number','delivery_boy_name','status')->where('id',$input['id'])->first(),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }

    } 

    public function get_profile(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $delivery_boy = DeliveryBoy::where('id',$input['id'])->first();
        if(is_object($delivery_boy)){
            return response()->json([
                "result" => $delivery_boy,
                "message" => 'Success',
                "status" => 1
            ]);
        }
        else{
            return response()->json([
                "message" => 'Something went wrong',
                "status" => 0
            ]);
        }
    }

        public function profile_picture(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'image' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/delivery_boys');
            $image->move($destinationPath, $name);
            return response()->json([
                "result" => 'delivery_boys/'.$name,
                "message" => 'Success',
                "status" => 1
            ]);
            
        }
    }

    public function profile_picture_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'profile_picture' => 'required'
        ]);
        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        if (DeliveryBoy::where('id',$input['id'])->update($input)) {
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong...',
                "status" => 0
            ]);
        }

    }

    public function forget_password(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $delivery_boy = DeliveryBoy::where('phone_with_code',$input['phone_with_code'])->first();
        

        if(is_object($delivery_boy)){
            $data['id'] = $delivery_boy->id;
            $data['otp'] = rand(1000,9999);
            if(env('MODE') != 'DEMO'){
                if($input['lang'] == 'en'){
                    $message = "Hi, from ".env('APP_NAME'). "  , Your OTP code is:".$data['otp'];
                }else{
                    $message = "رمز OTP الخاص بك هو:".$data['otp'];
                }
                $this->sendSms($input['phone_with_code'],$message);
            }
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "result" => 'Please enter valid phone number',
                "status" => 0
            ]);
            
        }
    }


    public function reset_password(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);

        if(DeliveryBoy::where('id',$input['id'])->update($input)){
            return response()->json([
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Sorry something went wrong',
                "status" => 0
            ]);
        }
    }

    public function change_online_status(Request $request){
        $input = $request->all();
        DeliveryBoy::where('id',$input['id'])->update([ 'online_status' => $input['online_status']]);
         $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $newPost = $database
        ->getReference('delivery_partners/'.$input['id'])
        ->update([
            'on_stat' => (int) $input['online_status']
        ]);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function delivery_boy_earning(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data['total_earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->get()->sum("amount");
        $data['today_earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->whereDay('created_at', now()->day)->sum("amount");
        $data['earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->get();
        
        if($data){
            return response()->json([
                "result" => $data,
                "count" => count($data),
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

    public function delivery_boy_wallet_histories(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $now = Carbon::now();
        //echo $now->endOfWeek();exit;
        $data['wallet_amount'] = DeliveryBoy::where('id',$input['id'])->value('wallet');
        $data['this_month_earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('amount');
        
        $data['this_week_earnings'] = DeliveryBoyEarning::where('delivery_boy_id',$input['id'])->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount');
        
        $data['wallets'] = DeliveryBoyWalletHistory::where('delivery_boy_id',$input['id'])->orderBy('created_at', 'desc')->get();
        
        
        if($data){
            return response()->json([
                "result" => $data,
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
    
    public function dashborad(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $picked_orders = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_deliveryboy','order_statuses.slug')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->where('order_statuses.slug','order_picked')
            ->get()->count();
        $completed_orders = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_deliveryboy','order_statuses.slug')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->where('order_statuses.slug','delivered')
            ->get()->count();
        $pending_orders = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_deliveryboy','order_statuses.slug')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->whereIn('order_statuses.slug',['ready_to_dispatch','reached_restaurant','order_picked','at_point'])
            ->get()->count();

        $data['picked_up'] = $picked_orders;
        $data['completed'] = $completed_orders;
        $data['pending'] = $pending_orders;
        
        if ($data) {
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
        
    }
    
    public function get_pending_orders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_id' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address','restaurants.google_address','restaurants.manual_address','restaurants.lat','restaurants.lng','restaurants.zip_code','restaurants.restaurant_name','restaurants.restaurant_phone_number')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->whereIn('order_statuses.slug',['ready_to_dispatch','reached_restaurant','order_picked','at_point'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        }else{
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address','restaurants.google_address','restaurants.manual_address','restaurants.lat','restaurants.lng','restaurants.zip_code','restaurants.restaurant_name_ar as restaurant_name','restaurants.restaurant_phone_number')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->whereIn('order_statuses.slug',['ready_to_dispatch','reached_restaurant','order_picked','at_point'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        }
        
        if($input['lang'] == 'en'){    
            foreach($orders as $key => $value){
                $orders[$key]->item_list = DB::table('order_items')
                ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                ->select('order_items.*','items.item_name','food_types.type_name','food_types.icon')
                ->where('order_id',$value->id)
                ->get();
            }
        }else{
            foreach($orders as $key => $value){
                $orders[$key]->item_list = DB::table('order_items')
                ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                ->select('order_items.*','items.item_name_ar as item_name','food_types.type_name','food_types.icon')
                ->where('order_id',$value->id)
                ->get();
            }
        }
            
        if ($orders) {
            return response()->json([
                "result" => $orders,
                "count" => count($orders),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    
    public function get_orders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_id' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address','customer_addresses.lat as cus_lat','customer_addresses.lng as cus_lng','restaurants.google_address','restaurants.manual_address','restaurants.lat as res_lat','restaurants.lng as res_lng','restaurants.zip_code','restaurants.restaurant_name','restaurants.restaurant_phone_number')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        }else{
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address','customer_addresses.lat as cus_lat','customer_addresses.lng as cus_lng','restaurants.google_address','restaurants.manual_address','restaurants.lat as res_lat','restaurants.lng as res_lng','restaurants.zip_code','restaurants.restaurant_name_ar as restaurant_name','restaurants.restaurant_phone_number')
            ->where('orders.delivered_by',$input['delivery_boy_id'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        }
        
        if($input['lang'] == 'en'){
            foreach($orders as $key => $value){
                    $orders[$key]->item_list = DB::table('order_items')
                    ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->select('order_items.*','items.item_name','food_types.type_name','food_types.icon')
                    ->where('order_id',$value->id)
                    ->get();
            }
        }else{
            foreach($orders as $key => $value){
                    $orders[$key]->item_list = DB::table('order_items')
                    ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->select('order_items.*','items.item_name_ar as item_name','food_types.type_name_ar as type_name','food_types.icon')
                    ->where('order_id',$value->id)
                    ->get();
            }
        }
        
        if($orders) {
            return response()->json([
                "result" => $orders,
                "count" => count($orders),
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    
    public function get_deliveryboy_order_detail(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'order_id' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if($input['lang'] == 'en'){
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('delivery_boys', 'delivery_boys.id', '=', 'orders.delivered_by')
            ->leftJoin('promo_codes', 'promo_codes.id', '=', 'orders.promo_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.id as status_id','order_statuses.status_for_deliveryboy','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.licence_no', 'restaurants.restaurant_name','restaurants.manual_address','restaurants.google_address','restaurants.lat as res_lat','restaurants.lng as res_lng','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat as cus_lat','customer_addresses.lng as cus_lng','customer_addresses.landmark','customers.customer_name','customers.phone_with_code','customers.profile_picture','promo_codes.promo_name')
            ->where('orders.id',$input['order_id'])
            ->first();
        }else{
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('delivery_boys', 'delivery_boys.id', '=', 'orders.delivered_by')
            ->leftJoin('promo_codes', 'promo_codes.id', '=', 'orders.promo_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.id as status_id','order_statuses.status_for_deliveryboy_ar as status_for_deliveryboy','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.licence_no', 'restaurants.restaurant_name_ar as restaurant_name','restaurants.manual_address','restaurants.google_address','restaurants.lat as res_lat','restaurants.lng as res_lng','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat as cus_lat','customer_addresses.lng as cus_lng','customer_addresses.landmark','customers.customer_name','customers.phone_with_code','customers.profile_picture','promo_codes.promo_name_ar as promo_name')
            ->where('orders.id',$input['order_id'])
            ->first();
        }
        
        if($orders->delivered_by){
            $partner_details = DB::table('delivery_boys')->where('id',$orders->delivered_by)->first();
            $partner_order_count = DB::table('orders')->where('id',$orders->delivered_by)->count();
            if(is_object($partner_details)){
                $orders->delivery_boy_name = $partner_details->delivery_boy_name;
                $orders->delivery_boy_image = $partner_details->profile_picture;
                $orders->delivery_boy_phone_number = $partner_details->phone_number;
                $orders->delivery_boy_order_count = $partner_order_count;
            }
        }
        //$new_status_id = DB::table('order_statuses')->where('status',$orders->status_id)->value('id');
        $new_status = $orders->status_id + 1;
        //echo $orders->status_id;exit;
        if($input['lang'] == 'en'){
            $new_status_name = DB::table('order_statuses')->where('id',$new_status)->value('status');
            $new_status_for_deliveryboy = DB::table('order_statuses')->where('id',$new_status)->value('status_for_deliveryboy');
        }else{
            $new_status_name = DB::table('order_statuses')->where('id',$new_status)->value('status_ar as status');
            $new_status_for_deliveryboy = DB::table('order_statuses')->where('id',$new_status)->value('status_for_deliveryboy_ar as status_for_deliveryboy');
        }
        
        $new_slug = DB::table('order_statuses')->where('id',$new_status)->value('slug');
        $orders->new_status = $new_status_name;
        $orders->new_slug = $new_slug;
        $orders->new_status_for_deliveryboy = $new_status_for_deliveryboy;
        if($input['lang'] == 'en'){
            $orders->item_list = DB::table('order_items')
                    ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->select('order_items.*','items.item_name','food_types.type_name','food_types.icon')
                    ->where('order_id',$orders->id)
                    ->get();
        }else{
            $orders->item_list = DB::table('order_items')
                    ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->select('order_items.*','items.item_name_ar as item_name','food_types.type_name_ar as type_name','food_types.icon')
                    ->where('order_id',$orders->id)
                    ->get();
        }
        
        if ($orders) {
            return response()->json([
                "result" => $orders,
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    
    public function withdrawal_request(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'delivery_boy_id' => 'required',
            'amount' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $input['status'] = 6;
        if($input['lang'] == 'en'){
            $message = "Your withdrawal request successfully submitted";
        }else{
            $message = "تم إرسال طلب السحب الخاص بك بنجاح";
        }
        $input['message'] = $message;
        $del_wallet = DeliveryBoy::where('id',$input['delivery_boy_id'])->value('wallet');
        $new_wallet = $del_wallet-$input['amount'];
        $input['existing_wallet'] = $del_wallet;
        if($input['amount'] <= $del_wallet ){
            $delivery_boy = DeliveryBoyWithdrawal::create($input);  
            $status = DeliveryBoyWithdrawal::where('delivery_boy_id',$input['delivery_boy_id'])->where('id',$delivery_boy->id)->value('status');
            if($status==6){
                 DeliveryBoy::where('id',$input['delivery_boy_id'])->update([ 'wallet' => $new_wallet]);
            }
        if (is_object($delivery_boy)) {
            return response()->json([
                "result" => $delivery_boy,
                "message" => 'success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
        }else{
             return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
        
        
    }
    
    public function withdrawal_history(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data['wallet_amount'] = DeliveryBoy::where('id',$input['id'])->value('wallet');
        
        if($input['lang'] == 'en'){
            $data['withdraw'] =  DB::table('delivery_boy_withdrawals')
                ->leftjoin('statuses', 'statuses.id', '=', 'delivery_boy_withdrawals.status')
                ->select('delivery_boy_withdrawals.*', 'statuses.status_name')
                ->where('delivery_boy_withdrawals.delivery_boy_id',$input['id'])
                ->orderBy('delivery_boy_withdrawals.created_at', 'desc')
                ->get();
        }else{
            $data['withdraw'] =  DB::table('delivery_boy_withdrawals')
                ->leftjoin('statuses', 'statuses.id', '=', 'delivery_boy_withdrawals.status')
                ->select('delivery_boy_withdrawals.*', 'statuses.status_name_ar as status_name')
                ->where('delivery_boy_withdrawals.delivery_boy_id',$input['id'])
                ->orderBy('delivery_boy_withdrawals.created_at', 'desc')
                ->get();
        }
        
        if($data){
            return response()->json([
                "result" => $data,
                "count" => count($data),
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
    
    public function delivery_boy_earning_month_wise(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data = DB::table('delivery_boy_earnings')
                ->selectRaw('
                    SUM(amount) AS earnings,
                    MONTH(created_at) as month
                ')->where('delivery_boy_id',$input['id'])
                ->groupByRaw('MONTH(created_at)')
                ->get()->toArray();
        if($input['lang'] == 'en'){
            $month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        }else{
            $month = ["يناير", "فبراير", "مارس", "أبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"];
        }
        
        $x = [];
        $y = [];
        foreach($data as $key => $value){
            $x[$key] = $month[$value->month -1];
            $y[$key] = (int) $value->earnings;
        }
        
        
        return response()->json([
            "result" => ['x' => $x, 'y' => implode(",",$y)],
            "message" => 'Success',
            "status" => 1
        ]);
       

    }

    public function sendError($message) {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    } 
}


 
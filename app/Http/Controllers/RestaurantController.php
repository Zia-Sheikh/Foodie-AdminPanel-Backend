<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\RestaurantEarning;
use App\Models\RestaurantWalletHistory;
use App\Models\RestaurantWithdrawal;
use App\Models\CustomerWalletHistory;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Item;
use App\Models\CustomerComplaint;
use Validator;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Carbon\Carbon;
use App\FcmNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\File;

class RestaurantController extends Controller
{
    
    public function login(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'username' => 'required',
            'password' => 'required',
            'fcm_token' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $credentials = request(['username', 'password']);
        $restaurant = Restaurant::where('username',$credentials['username'])->first();

        if (!($restaurant)) {
            if($input['lang'] == 'en'){
                $message = 'Invalid username or password';
            }else{
                $message = 'اسم المستخدم أو كلمة المرور غير صالحة';
            }
            return response()->json([
                "message" => $message,
                "status" => 0
            ]);
        }
        
        if (Hash::check($credentials['password'], $restaurant->password)) {
            if($restaurant->status == 1){
                Restaurant::where('id',$restaurant->id)->update([ 'fcm_token' => $input['fcm_token']]);
                return response()->json([
                    "result" => $restaurant,
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
                $message = 'Your account has been blocked';
            }else{
                $message = 'لقد تم حظر حسابك';
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
        $customer = Restaurant::where('phone_with_code',$input['phone_with_code'])->first();

        if(is_object($customer)){
            $data['is_available'] = 1;
            $data['otp'] = "";
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
           $data['is_available'] = 0;
            $data['otp'] = rand(1000,9999);
            if(env('MODE') != 'DEMO'){
                $message = "Hi".env('APP_NAME'). "  , Your OTP code is:".$data['otp'];
                $this->sendSms($input['phone_with_code'],$message);
            }
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }
    }
    
    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_name' => 'required',
            'username' => 'required|unique:restaurants,username',
            'password' => 'required',
            'restaurant_phone_number' => 'required|unique:restaurants,restaurant_phone_number',
            'phone_with_code' => 'required',
            'manual_address' => 'required',
            'contact_person_name' => 'required',
            'google_address' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'zip_code' => 'required',
            'fcm_token' => 'required',
               
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
        $input['status'] = 1;
        $input['restaurant_image'] = 'static_images/restaurant.jpg';
        $id = DB::table('admin_users')->insertGetId(
                ['username' => $input['username'], 'password' => $input['password'], 'name' => $input['restaurant_name'], 'avatar' => 'static_images/restaurant.jpg']
            );

            DB::table('admin_role_users')->insert(
                ['role_id' => 2, 'user_id' => $id ]
            );
        
        $input['admin_user_id'] = $id;
        

        $restaurant = Restaurant::create($input);
        $res = Restaurant::where('id',$restaurant->id)->first();

        if (is_object($restaurant)) {
            $this->update_status($restaurant->id,$restaurant->restaurant_name,$restaurant->is_open);
            return response()->json([
                "result" => $res,
                "message" => 'Registered Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }

    }
    
    public function update_status($id,$res_nme,$is_opn){
        
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
    
    public function certificate_upload(Request $request){

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
            $destinationPath = public_path('/uploads/certificates');
            $image->move($destinationPath, $name);
            return response()->json([
                "result" => 'certificates/'.$name,
                "message" => 'Success',
                "status" => 1
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

        if (Restaurant::where('id',$input['id'])->update($input)) {
            return response()->json([
                "result" => Restaurant::select('id','restaurant_name','manual_address','contact_person_name','restaurant_phone_number','email','status')->where('id',$input['id'])->first(),
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

        $restaurant = Restaurant::where('id',$input['id'])->first();
        if(is_object($restaurant)){
            return response()->json([
                "result" => $restaurant,
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

    public function restaurant_image(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/uploads/restaurants');
            $image->move($destinationPath, $name);
            return response()->json([
                "result" => 'restaurants/'.$name,
                "message" => 'Success',
                "status" => 1
            ]);
            
        }
    }

    public function restaurant_image_update(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'restaurant_image' => 'required'
            
        ]);

        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        if (Restaurant::where('id',$input['id'])->update($input)) {
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

        $restaurant = Restaurant::where('phone_with_code',$input['phone_with_code'])->first();
        
        if(is_object($restaurant)){
            $data['id'] = $restaurant->id;
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

        if(Restaurant::where('id',$input['id'])->update($input)){
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
    
    public function restaurant_earning(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data['total_earnings'] = RestaurantEarning::where('restaurant_id',$input['id'])->get()->sum("amount");
        $data['today_earnings'] = RestaurantEarning::where('restaurant_id',$input['id'])->whereDay('created_at', now()->day)->sum("amount");
        $data['earnings'] = RestaurantEarning::where('restaurant_id',$input['id'])->get();
        
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

    public function restaurant_wallet_histories(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $now = Carbon::now();
        $data['wallet_amount'] = Restaurant::where('id',$input['id'])->value('wallet');
        $data['this_month_earnings'] = RestaurantEarning::where('restaurant_id',$input['id'])->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('amount');
        
        $data['this_week_earnings'] = RestaurantEarning::where('restaurant_id',$input['id'])->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('amount');
        
        $data['wallets'] = RestaurantWalletHistory::where('restaurant_id',$input['id'])->orderBy('created_at', 'desc')->get();
        
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
    
    public function change_online_status(Request $request){
        $input = $request->all();
        Restaurant::where('id',$input['id'])->update([ 'is_open' => $input['is_open']]);
        
        $factory = (new Factory())->withDatabaseUri(env('FIREBASE_DB'));
        $database = $factory->createDatabase();
        $newPost = $database
        ->getReference('restaurants/'.$input['id'])
        ->update([
            'is_opn' => (int) $input['is_open']
        ]);
    
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function restaurant_withdrawal_request(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'amount' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $input['status'] = 6;
        if($input['lang'] == 'en'){
            $input['message'] = "Your withdrawal request successfully submitted";
        }else{
            $input['message'] = "تم إرسال طلب السحب الخاص بك بنجاح";
        }
         
        $res_wallet = Restaurant::where('id',$input['restaurant_id'])->value('wallet');
        $new_wallet = $res_wallet-$input['amount'];
        $input['existing_wallet'] = $res_wallet;
        if($input['amount'] <= $res_wallet ){
          $restaurant = RestaurantWithdrawal::create($input);  
          
        $status = RestaurantWithdrawal::where('restaurant_id',$input['restaurant_id'])->where('id',$restaurant->id)->value('status');
            if($status==6){
                 Restaurant::where('id',$input['restaurant_id'])->update([ 'wallet' => $new_wallet]);
            }
        if (is_object($restaurant)) {
            return response()->json([
                "result" => $restaurant,
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
                "message" => 'Please check your wallet amount',
                "status" => 0
            ]);
        }
        
        
    }
    
    public function restaurant_withdrawal_history(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data['wallet_amount'] = Restaurant::where('id',$input['id'])->value('wallet');
        if($input['lang'] == 'en'){
            $data['withdraw'] =  DB::table('restaurant_withdrawals')
                ->leftjoin('statuses', 'statuses.id', '=', 'restaurant_withdrawals.status')
                ->select('restaurant_withdrawals.*', 'statuses.status_name')
                ->orderBy('restaurant_withdrawals.created_at', 'desc')
                ->get();
        }else{
            $data['withdraw'] =  DB::table('restaurant_withdrawals')
                ->leftjoin('statuses', 'statuses.id', '=', 'restaurant_withdrawals.status')
                ->select('restaurant_withdrawals.*', 'statuses.status_name_ar as status_name')
                ->orderBy('restaurant_withdrawals.created_at', 'desc')
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
    
    public function get_orders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
            ->where('orders.restaurant_id',$input['restaurant_id'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
        }else{
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
            ->where('orders.restaurant_id',$input['restaurant_id'])
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
    
    public function get_pending_orders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'lang' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['search']){
            $input['status_filter'] = 0;
            $input['order_type'] = 0;
            $search = $input['search'];
        }else{
            $search = '';
        }
        
        if($input['status_filter'] == 0){
            $status_slug = ['restaurant_approved','ready_to_dispatch','reached_restaurant','order_picked','at_point','order_picked','delivered'];
        }else if($input['status_filter'] == 1){
            $status_slug = ['restaurant_approved','ready_to_dispatch','reached_restaurant','order_picked','at_point'];
        }else if($input['status_filter'] == 2){
            $status_slug = ['order_picked'];
        }else if($input['status_filter'] == 3){
            $status_slug = ['delivered'];
        }
        
        if($input['order_type'] == 0){
            $order_types = [1,2];
        }else if($input['order_type'] == 1){
            $order_types = [1];
        }else if($input['order_type'] == 2){
            $order_types = [2];
        }
        
        if($search){
            if($input['lang'] == 'en'){
                $data['orders'] = DB::table('orders')
                ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
                ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->where('orders.token',$search)
                ->orderBy('orders.created_at', 'desc')
                ->get();
            }else{
                $data['orders'] = DB::table('orders')
                ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
                ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->where('orders.token',$search)
                ->orderBy('orders.created_at', 'desc')
                ->get();
            }
        }else{
            if($input['lang'] == 'en'){
                $data['orders'] = DB::table('orders')
                ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
                ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->whereIn('order_statuses.slug',$status_slug)
                ->whereIn('orders.order_type',$order_types)
                ->orderBy('orders.created_at', 'desc')
                ->get();
            }else{
                $data['orders'] = DB::table('orders')
                ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
                ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
                ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->whereIn('order_statuses.slug',$status_slug)
                ->whereIn('orders.order_type',$order_types)
                ->orderBy('orders.created_at', 'desc')
                ->get();
            }
        }
        
        
        if($input['lang'] == 'en'){    
            foreach($data['orders'] as $key => $value){
                    $data['orders'][$key]->item_list = DB::table('order_items')
                    ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->select('order_items.*','items.item_name','food_types.type_name','food_types.icon')
                    ->where('order_id',$value->id)
                    ->get();
            }
        }else{
            foreach($data['orders'] as $key => $value){
                    $data['orders'][$key]->item_list = DB::table('order_items')
                    ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->select('order_items.*','items.item_name_ar as item_name','food_types.type_name_ar as type_name','food_types.icon')
                    ->where('order_id',$value->id)
                    ->get();
            }
        }
        $data['picked_orders'] = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.slug')
            ->where('orders.restaurant_id',$input['restaurant_id'])
            ->where('order_statuses.slug','order_picked')
            ->get()->count();
        $data['completed_orders'] = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.slug')
            ->where('orders.restaurant_id',$input['restaurant_id'])
            ->where('order_statuses.slug','delivered')
            ->get()->count();
        $data['pending_orders'] = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.slug')
            ->where('orders.restaurant_id',$input['restaurant_id'])
            ->whereIn('order_statuses.slug',['restaurant_approved','ready_to_dispatch','reached_restaurant','order_picked','at_point'])
            ->get()->count();
        $data['delivery_count'] = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.slug')
            ->where('orders.restaurant_id',$input['restaurant_id'])
            ->where('orders.order_type',1)
            ->get()->count();
        $data['takeaway_count'] = DB::table('orders')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.slug')
            ->where('orders.restaurant_id',$input['restaurant_id'])
            ->where('orders.order_type',2)
            ->get()->count();
        if ($data) {
            return response()->json([
                "result" => $data,
                "count" => count($data),
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

    public function dashborad(Request $request){
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if($input['lang'] == 'en'){
            $picked_orders = DB::table('orders')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.slug')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->where('order_statuses.slug','order_picked')
                ->get()->count();
            $completed_orders = DB::table('orders')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.slug')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->where('order_statuses.slug','delivered')
                ->get()->count();
            $pending_orders = DB::table('orders')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.slug')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->whereIn('order_statuses.slug',['restaurant_approved','ready_to_dispatch','reached_restaurant','order_picked','at_point'])
                ->get()->count();
        }else{
            $picked_orders = DB::table('orders')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.slug')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->where('order_statuses.slug','order_picked')
                ->get()->count();
            $completed_orders = DB::table('orders')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.slug')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->where('order_statuses.slug','delivered')
                ->get()->count();
            $pending_orders = DB::table('orders')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.slug')
                ->where('orders.restaurant_id',$input['restaurant_id'])
                ->whereIn('order_statuses.slug',['restaurant_approved','ready_to_dispatch','reached_restaurant','order_picked','at_point'])
                ->get()->count();
        }
        

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
    
    public function stock_update(Request $request){
        $input = $request->all();
        
        Item::where('restaurant_id',$input['restaurant_id'])->where('id',$input['item_id'])->update([ 'in_stock' => $input['in_stock']]);
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'item_id' =>'required',
            'in_stock' =>'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_order_request(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
            ->where('orders.restaurant_id',$input['restaurant_id'])
            ->where('order_statuses.slug','order_placed')
            ->orderBy('orders.created_at', 'desc')
            ->get();
            
            foreach($orders as $key => $value){
                $orders[$key]->item_list = DB::table('order_items')
                ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                ->select('order_items.*','items.item_name','food_types.type_name','food_types.icon')
                ->where('order_id',$value->id)
                ->get();
            }
        }else{
            $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_restaurant_ar as status_for_restaurant','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'customers.phone_number', 'customers.customer_name','customers.profile_picture','customer_addresses.address')
            ->where('orders.restaurant_id',$input['restaurant_id'])
            ->where('order_statuses.slug','order_placed')
            ->orderBy('orders.created_at', 'desc')
            ->get();
            
            foreach($orders as $key => $value){
                $orders[$key]->item_list = DB::table('order_items')
                ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                ->select('order_items.*','items.item_name_ar as item_name','food_types.type_name_ar as type_name','food_types.icon')
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

    public function get_restaurant_order_detail(Request $request)
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
            ->select('orders.*','order_statuses.status_for_customer','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.licence_no', 'restaurants.restaurant_name','restaurants.manual_address','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat','customer_addresses.lng','customer_addresses.landmark','customers.customer_name','customers.phone_with_code','customers.profile_picture','promo_codes.promo_name')
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
            ->select('orders.*','order_statuses.status_for_customer_ar as status_for_customer','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.licence_no', 'restaurants.restaurant_name_ar as restaurant_name','restaurants.manual_address','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat','customer_addresses.lng','customer_addresses.landmark','customers.customer_name','customers.phone_with_code','customers.profile_picture','promo_codes.promo_name_ar as promo_name')
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
                    ->select('order_items.*','items.item_name_ar as item_name','food_types.type_name','food_types.icon')
                    ->where('order_id',$orders->id)
                    ->get();
        }
        foreach($orders->item_list as $key => $val){
            $orders->item_list[$key]->options = DB::table('order_item_options')->where('item_id',$val->item_id)->where('order_id',$orders->id)->get();
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

    public function get_menu(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        $data = [];
        $rest = Restaurant::where('id',$input['restaurant_id'])->first();
        
        if($input['lang'] == 'en'){
            $categories = DB::table('restaurant_categories')
                    ->join('categories','categories.id','restaurant_categories.category_id')
                    ->select('categories.*')
                    ->where('categories.status',1)
                    ->where('restaurant_categories.restaurant_id',$input['restaurant_id'])->get();
        }else{
            $categories = DB::table('restaurant_categories')
                    ->join('categories','categories.id','restaurant_categories.category_id')
                    ->select('categories.*','categories.category_name_ar as category_name')
                    ->where('categories.status',1)
                    ->where('restaurant_categories.restaurant_id',$input['restaurant_id'])->get();
        }
        
        $menus = [];
        if($input['lang'] == 'en'){
            foreach($categories as $key => $value){
                    $value->data = DB::table('items')
                    ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->leftJoin('tags', 'tags.id', '=', 'items.item_tag')
                    ->leftJoin('restaurants', 'restaurants.id', '=', 'items.restaurant_id')
                    ->select('items.*','categories.category_name','food_types.type_name','food_types.icon','tags.tag_name','restaurants.restaurant_name')
                    ->where('items.category_id', $value->id)
                    ->where('items.restaurant_id', $input['restaurant_id'])
                    ->get();
                
                if(count($value->data) == 0){
                    $menus[] = $value;
                }
            }
        }else{
            foreach($categories as $key => $value){
                    $value->data = DB::table('items')
                    ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->leftJoin('tags', 'tags.id', '=', 'items.item_tag')
                    ->leftJoin('restaurants', 'restaurants.id', '=', 'items.restaurant_id')
                    ->select('items.*','items.item_name_ar as item_name','items.item_description_ar as item_description','categories.category_name_ar as category_name','food_types.type_name_ar as type_name','food_types.icon','tags.tag_name_ar as tag_name','restaurants.restaurant_name_ar as restaurant_name')
                    ->where('items.category_id', $value->id)
                    ->where('items.restaurant_id', $input['restaurant_id'])
                    ->get();
                
                if(count($value->data) == 0){
                    $menus[] = $value;
                }
            }
        }
        
        if($input['lang'] == 'en'){
            $cuisines = DB::table('restaurant_cuisines')
                            ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                            ->where('restaurant_cuisines.restaurant_id',$rest->id)
                            ->pluck('food_cuisines.cuisine_name')->toArray();
        }else{
            $cuisines = DB::table('restaurant_cuisines')
                            ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                            ->where('restaurant_cuisines.restaurant_id',$rest->id)
                            ->pluck('food_cuisines.cuisine_name_ar as cuisine_name')->toArray();
        }
        $rest->cuisines = implode(',', $cuisines);
        $data['categories'] = $categories;
        $data['restaurant'] = $rest;
        

        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function get_complaints(Request $request)
    {   
         $input = $request->all();

        $validator =  Validator::make($input,[
            'restaurant_id' => 'required',
        ]);

         if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        //$data = CustomerComplaint::where('restaurant_id',$input['restaurant_id'])->get();
        $data = DB::table('customer_complaints')
            ->leftJoin('customers', 'customers.id', '=', 'customer_complaints.customer_id')
            ->select('customer_complaints.*','customers.customer_name')
            ->where('customer_complaints.restaurant_id',$input['restaurant_id'])
            ->orderBy('customer_complaints.created_at', 'desc')
            ->get();
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function download_qr($id)
    {
        $path = public_path('qr_codes/RES-'.$id.'.png');
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path);
    }

    public function sendError($message) {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    } 
}

       


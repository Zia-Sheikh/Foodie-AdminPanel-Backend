<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\CustomerWalletHistory;
use App\Models\AppSetting;
use App\Models\AddressType;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\PromoCode;
use App\Models\FavouriteRestaurant;
use App\Models\CustomerAppSetting;
use App\Models\CustomerPromoHistory;
use App\Models\Item;
use App\Models\Tax;
use Validator;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use App\FcmNotification;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Cartalyst\Stripe\Stripe;
use Illuminate\Support\Facades\DB;


class CustomerController extends Controller
{
    public function register(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_name' => 'required',
            'phone_number' => 'required|numeric|unique:customers,phone_number',
            'phone_with_code' => 'required',
            'password' => 'required',
            'fcm_token' => 'required',
            'lang' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $options = [
            'cost' => 12,
        ];
        $input['password'] = password_hash($input["password"], PASSWORD_DEFAULT, $options);
        $input['status'] = 1;
        
        
        $refered_by = $input['referral_code'];
        $referrel_id = Customer::where('referral_code',$refered_by)->value('id');
        if($referrel_id){
            $input['refered_by'] = $refered_by;
        }else{
            $input['refered_by'] = '';
        }
        
        $customer = Customer::create($input);
        $cus = Customer::where('id',$customer->id)->first();

        if (is_object($cus)) {
            if($refered_by != '' && $referrel_id){
                $referral_amount = CustomerAppSetting::where('id',1)->value('referral_bonus');
                $existing_wallet_amount = Customer::where('referral_code',$refered_by)->value('wallet');
                $wallet = $existing_wallet_amount + $referral_amount;
                Customer::where('referral_code',$refered_by)->update(['wallet' => $wallet]);
                Customer::where('id',$customer->id)->update(['refered_by' => $referrel_id]);
                if($input['lang'] == 'en'){
                    $content = "Referral Bonus";
                }else{
                    $content = "مكافأة الإحالة";
                }
                
                CustomerWalletHistory::create(['customer_id' => $referrel_id, 'type' => 3, 'message' => $content, 'amount' => $referral_amount, 'transaction_type' => 1  ]);
            }
            $cus->referral_code = 'CUS'.str_pad($cus->id,5,"0",STR_PAD_LEFT);
            Customer::where('id',$cus->id)->update(['referral_code' => $cus->referral_code]);
            return response()->json([
                "result" => $cus,
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
    
    
    public function check_phone(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
        	'phone_with_code' => 'required',
        	'lang' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data = array();
        $customer = Customer::where('phone_with_code',$input['phone_with_code'])->first();

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
            if($input['lang'] == 'en'){
                $message = "Hi, from ".env('APP_NAME'). "  , Your OTP code is:".$data['otp'];
            }else{
                $message = "رمز OTP الخاص بك هو:".$data['otp'];
            }
            
            if(env('MODE') != 'DEMO'){
                $this->sendSms($input['phone_with_code'],$message);
            }
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }
    }

    public function login(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'phone_with_code' => 'required',
            'password' => 'required',
            'fcm_token' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $credentials = request(['phone_with_code', 'password']);
        $customer = Customer::where('phone_with_code',$credentials['phone_with_code'])->first();
        
        if (!($customer)) {
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
        
        if (Hash::check($credentials['password'], $customer->password)) {
            if($customer->status == 1){
                
                Customer::where('id',$customer->id)->update([ 'fcm_token' => $input['fcm_token']]);
                
                return response()->json([
                    "result" => $customer,
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

        if (Customer::where('id',$input['id'])->update($input)) {
            return response()->json([
                "result" => Customer::select('id','email','phone_number','customer_name','profile_picture','status')->where('id',$input['id'])->first(),
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
    
    
    public function get_last_active_address(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'lang' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if($input['lang'] == 'en'){
            $address = DB::table('customers')
                    ->join('customer_addresses','customer_addresses.id','customers.last_active_address')
                    ->join('address_types','address_types.id','customer_addresses.address_type')
                    ->select('customer_addresses.*','address_types.type_name')
                    ->where('customers.id',$input['customer_id'])->first();
        }else{
            $address = DB::table('customers')
                    ->join('customer_addresses','customer_addresses.id','customers.last_active_address')
                    ->join('address_types','address_types.id','customer_addresses.address_type')
                    ->select('customer_addresses.*','address_types.type_name_ar as type_name')
                    ->where('customers.id',$input['customer_id'])->first();
        }
        
                                         
        if(is_object($address)){
            return response()->json([
                "result" => $address,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            return response()->json([
                "message" => 'Sorry no address found',
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

        $customer = Customer::where('id',$input['id'])->first();
        if(is_object($customer)){
            return response()->json([
                "result" => $customer,
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
        $customer = Customer::where('phone_with_code',$input['phone_with_code'])->first();
        

        if(is_object($customer)){
            $data['id'] = $customer->id;
            $data['otp'] = rand(1000,9999);
            if($input['lang'] == 'en'){
                $message = "Hi, from ".env('APP_NAME'). "  , Your OTP code is:".$data['otp'];
            }else{
                $message = "رمز OTP الخاص بك هو:".$data['otp'];
            }
            if(env('MODE') != 'DEMO'){
                $this->sendSms($input['phone_with_code'],$message);
            }
            return response()->json([
                "result" => $data,
                "message" => 'Success',
                "status" => 1
            ]);
        }else{
            if($input['lang'] == 'en'){
                $message = "Please enter valid phone number";
            }else{
                $message = "الرجاء إدخال رقم هاتف صالح";
            }
    		return response()->json([
                "result" => $message,
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

        if(Customer::where('id',$input['id'])->update($input)){
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

    public function profile_picture(Request $request){

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
            $destinationPath = public_path('/uploads/customers');
            $image->move($destinationPath, $name);
            return response()->json([
                "result" => 'customers/'.$name,
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
        
        if (Customer::where('id',$input['id'])->update($input)) {
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
    
    public function stripe_payment(Request $request){
        $input = $request->all();
        $stripe = new Stripe();
        $currency_code = AppSetting::value('currency_short_code');
        
        try {
            $charge = $stripe->charges()->create([
                'source' => $input['token'],
                'currency' => $currency_code,
                'amount'   => $input['amount'],
                'description' => 'For booking'
            ]);
            
            $data['order_id'] = 0;
            $data['customer_id'] = $input['customer_id'];
            $data['payment_mode'] = 2;
            $data['payment_response'] = $charge['id'];
            
                return response()->json([
                    "result" => $charge['id'],
                    "message" => 'Success',
                    "status" => 1
                ]);
            
        }
        catch (customException $e) {
            return response()->json([
                "message" => 'Sorry something went wrong',
                "status" => 0
            ]);
        }
    }
    
    public function get_address_type(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data = AddressType::get();
        }else{
            $data = AddressType::select('*','type_name_ar as type_name')->get();
        }
        
    
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function get_banners()
    {

       $data = Banner::get()->all();
        
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function get_categories(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data = Category::where('recommended_by_admin',1)->where('status',1)->get();
        }else{
            $data = Category::select('*','category_name_ar as category_name','description_ar as description')->where('recommended_by_admin',1)->where('status',1)->get();
        }
        
   
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function find_eta($from_lat,$from_lng,$to_lat,$to_lng){
        return 10;
    }
    
    public function get_google_distance($lat1, $lat2, $long1, $long2)
    {
        $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=pl-PL&key=".env('MAP_KEY');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response, true);
        $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
        $time = $response_a['rows'][0]['elements'][0]['duration']['text'];
        
        $data['distance_with_mes'] = str_replace(",",".",$dist);
        $data['distance'] = substr($data['distance_with_mes'], 0, strlen($dist) - 3);
        $data['time'] = $time;
        
        return $data;
    }
    

    public function get_restaurant_menu(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'customer_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'food_type' => 'required',
            'lang' => 'required',
        ]);
        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        $data = [];
        
        //Timing calculation
        $max_time = DB::table('items')->where('restaurant_id',$input['restaurant_id'])->where('is_approved',1)->max('preparation_time');
        $rest = Restaurant::where('id',$input['restaurant_id'])->first();
        $data['max_time'] = $max_time;
        //$data['distance'] = $this->distance($input['lat'],$input['lng'],$rest->lat, $rest->lng, 'K');
        $distance = $this->get_google_distance($input['lat'],$rest->lat,$input['lng'], $rest->lng);
        $data['distance'] = $distance['distance'];
        if(!$data['distance']){
            $data['distance'] = 0;
        }
        $data['distance_time'] = $distance['time'];
        
        if($input['lang'] == 'en'){
            $categories = DB::table('restaurant_categories')
                    ->join('categories','categories.id','restaurant_categories.category_id')
                    ->select('categories.*')
                    ->where('categories.status',1)
                    ->where('restaurant_categories.restaurant_id',$input['restaurant_id'])->get();
        }else{
            $categories = DB::table('restaurant_categories')
                    ->join('categories','categories.id','restaurant_categories.category_id')
                    ->select('categories.*','categories.category_name_ar as category_name','description_ar as description')
                    ->where('categories.status',1)
                    ->where('restaurant_categories.restaurant_id',$input['restaurant_id'])->get();
        }
        
        $data['r_categories'] = $categories;
        $menus = [];
        foreach($categories as $key => $value){
            if($input['lang'] == 'en'){
                if($input['food_type'] == 0){
                    $value->data = DB::table('items')
                    ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->leftJoin('tags', 'tags.id', '=', 'items.item_tag')
                    ->leftJoin('restaurants', 'restaurants.id', '=', 'items.restaurant_id')
                    ->select('items.*','categories.category_name','food_types.type_name','food_types.icon','tags.tag_name','restaurants.restaurant_name')
                    ->where('items.category_id', $value->id)
                    ->where('items.restaurant_id', $input['restaurant_id'])
                    ->get();
                }else{
                     $value->data = DB::table('items')
                    ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->leftJoin('tags', 'tags.id', '=', 'items.item_tag')
                    ->leftJoin('restaurants', 'restaurants.id', '=', 'items.restaurant_id')
                    ->select('items.*','categories.category_name','food_types.type_name','food_types.icon','tags.tag_name','restaurants.restaurant_name')
                    ->where('items.category_id', $value->id)
                    ->where('items.restaurant_id', $input['restaurant_id'])
                    ->where('items.food_type', $input['food_type'])
                    ->get();
                }   
            }else{
                if($input['food_type'] == 0){
                    $value->data = DB::table('items')
                    ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->leftJoin('tags', 'tags.id', '=', 'items.item_tag')
                    ->leftJoin('restaurants', 'restaurants.id', '=', 'items.restaurant_id')
                    ->select('items.*','items.item_name_ar as item_name','items.item_description_ar as item_description','categories.category_name_ar as category_name','food_types.type_name_ar as type_name','food_types.icon','tags.tag_name_ar as tag_name','restaurants.restaurant_name_ar as restaurant_name')
                    ->where('items.category_id', $value->id)
                    ->where('items.restaurant_id', $input['restaurant_id'])
                    ->get();
                }else{
                     $value->data = DB::table('items')
                    ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                    ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                    ->leftJoin('tags', 'tags.id', '=', 'items.item_tag')
                    ->leftJoin('restaurants', 'restaurants.id', '=', 'items.restaurant_id')
                    ->select('items.*','items.item_name_ar as item_name','items.item_description_ar as item_description','categories.category_name_ar as category_name','food_types.type_name_ar as type_name','food_types.icon','tags.tag_name_ar as tag_name','restaurants.restaurant_name_ar as restaurant_name')
                    ->where('items.category_id', $value->id)
                    ->where('items.restaurant_id', $input['restaurant_id'])
                    ->where('items.food_type', $input['food_type'])
                    ->get();
                }
            }
            foreach($value->data as $key_op => $value_op){
                $groups = DB::table('item_options')->groupBy('option_group_id')->where('item_id',$value_op->id)->pluck('option_group_id')->toArray();
                if(is_array($groups)){
                    if($input['lang'] == 'en'){
                        $option_groups = DB::table('option_groups')->select('option_group_name','id')->whereIn('id',$groups)->get();
                        foreach($option_groups as $key_og  => $value_og){
                            $option_groups[$key_og]->options = DB::table('item_options')->select('id as value','option_name as label','price')->where('item_id',$value_op->id)->where('option_group_id',$value_og->id)->get();
                        }
                    }else{
                        $option_groups = DB::table('option_groups')->select('option_group_name_ar as option_group_name','id')->whereIn('id',$groups)->get();
                        foreach($option_groups as $key_og  => $value_og){
                            $option_groups[$key_og]->options = DB::table('item_options')->select('id as value','option_name_ar as label','price')->where('item_id',$value_op->id)->where('option_group_id',$value_og->id)->get();
                        }
                    }
                    $value->data[$key_op]->options = $option_groups;
                }else{
                    $value->data[$key_op]->options = [];
                }
                //$value->data[$key_op]->options = DB::table('item_options')->where('item_id',$value_op->id)->get();
                
            }
            if(count($value->data) == 0){
                $menus[] = $value;
            }
        }
        $data['veg_count'] = DB::table('items')->where('food_type', 1)->where('restaurant_id', $input['restaurant_id'])->count();
        $data['non_veg_count'] = DB::table('items')->where('food_type', 2)->where('restaurant_id', $input['restaurant_id'])->count();
        $data['egg_count'] = DB::table('items')->where('food_type', 3)->where('restaurant_id', $input['restaurant_id'])->count();
        if($input['lang'] == 'en'){
            $data['promo'] = PromoCode::where('status',1)->get();
        }else{
            $data['promo'] = PromoCode::select('*','promo_name_ar as promo_name','description_ar as description')->where('status',1)->get();
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
        $rest->is_favourite = DB::table('favourite_restaurants')->where('customer_id',$input['customer_id'])->where('restaurant_id',$input['restaurant_id'])->count();
        $data['categories'] = $categories;
        $data['restaurant'] = $rest;
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function restaurant_list(Request $request)
    {   
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data = DB::table('restaurants')
                ->select('restaurants.*')
                ->where('restaurants.status',1)
                ->where('restaurants.is_open',1)->get();
        }else{
            $data = DB::table('restaurants')
                ->select('restaurants.*','restaurants.restaurant_name_ar as restaurant_name')
                ->where('restaurants.status',1)
                ->where('restaurants.is_open',1)->get();
        }
        
            
        
        $restaurant_radius = CustomerAppSetting::where('id',1)->value('restaurant_radius');
        $result = [];
        
        
        
        if(env('MODE') == 'DEMO'){
            if($input['lang'] == 'en'){
                foreach($data as $key => $value){
                    $cuisines = DB::table('restaurant_cuisines')
                            ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                            ->where('restaurant_cuisines.restaurant_id',$value->id)
                            ->pluck('food_cuisines.cuisine_name')->toArray();
                    $data[$key]->cuisines = implode(',', $cuisines);
                    array_push($result,$data[$key]);    
                }
            }else{
                foreach($data as $key => $value){
                    $cuisines = DB::table('restaurant_cuisines')
                            ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                            ->where('restaurant_cuisines.restaurant_id',$value->id)
                            ->pluck('food_cuisines.cuisine_name_ar as food_cuisines.cuisine_name')->toArray();
                    $data[$key]->cuisines = implode(',', $cuisines);
                    array_push($result,$data[$key]);    
                }
            }
        }else{
            if($input['lang'] == 'en'){
                foreach($data as $key => $value){
                    $distance =  $this->distance($value->lat,$value->lng,$input['lat'],$input['lng'],'K');
                    if($distance <= $restaurant_radius){
                        $cuisines = DB::table('restaurant_cuisines')
                                ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                                ->where('restaurant_cuisines.restaurant_id',$value->id)
                                ->pluck('food_cuisines.cuisine_name')->toArray();
                        $data[$key]->cuisines = implode(',', $cuisines);
                        array_push($result,$data[$key]);
                    }
                }
            }else{
                foreach($data as $key => $value){
                    $distance =  $this->distance($value->lat,$value->lng,$input['lat'],$input['lng'],'K');
                    if($distance <= $restaurant_radius){
                        $cuisines = DB::table('restaurant_cuisines')
                                ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                                ->where('restaurant_cuisines.restaurant_id',$value->id)
                                ->pluck('food_cuisines.cuisine_name_ar as food_cuisines.cuisine_name')->toArray();
                        $data[$key]->cuisines = implode(',', $cuisines);
                        array_push($result,$data[$key]);
                    }
                }
            }
        }
        
        return response()->json([
            "result" => $result,
            "count" => count($result),
            "message" => 'Success',
            "status" => 1
        ]);
    
    }
    
    public function home_search(Request $request)
    {   
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'lang' => 'required',
        ]);

        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        $search = $input['search'];
        
        if($input['lang'] == 'en'){
            $data = DB::table('restaurants')
            ->select('restaurants.*')
            ->where('restaurants.status',1)->where('restaurants.is_open',1)->where('restaurant_name', 'LIKE', "%$search%")->get();
        }else{
            $data = DB::table('restaurants')
            ->select('restaurants.*','restaurants.restaurant_name_ar as restaurant_name')
            ->where('restaurants.status',1)->where('restaurants.is_open',1)->where('restaurant_name', 'LIKE', "%$search%")->get();
        }
        
        
        $restaurant_radius = CustomerAppSetting::where('id',1)->value('restaurant_radius');
        $result = [];
        
        foreach($data as $key => $value){
            $distance =  $this->distance($value->lat,$value->lng,$input['lat'],$input['lng'],'K');
            if($distance <= $restaurant_radius){
                if($input['lang'] == 'en'){
                    $cuisines = DB::table('restaurant_cuisines')
                            ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                            ->where('restaurant_cuisines.restaurant_id',$value->id)
                            ->pluck('food_cuisines.cuisine_name')->toArray();
                }else{
                    $cuisines = DB::table('restaurant_cuisines')
                            ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                            ->where('restaurant_cuisines.restaurant_id',$value->id)
                            ->pluck('food_cuisines.cuisine_name_ar as cuisine_name')->toArray();
                }
                $data[$key]->cuisines = implode(',', $cuisines);
                array_push($result,$data[$key]);
            }
        }
        
        return response()->json([
            "result" => $result,
            "count" => count($result),
            "message" => 'Success',
            "status" => 1
        ]);
    
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

    public function update_favourite_restaurant(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'restaurant_id' => 'required',
        ]);

         if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $check = FavouriteRestaurant::where('restaurant_id',$input['restaurant_id'])->where('customer_id',$input['customer_id'])->first();
        if(is_object($check)){
            FavouriteRestaurant::where('restaurant_id',$input['restaurant_id'])->where('customer_id',$input['customer_id'])->delete();
        }else{
            $fav = FavouriteRestaurant::create($input);
        }
        
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function get_favourite_restaurant(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data =  DB::table('favourite_restaurants')
                ->leftjoin('restaurants', 'restaurants.id', '=', 'favourite_restaurants.restaurant_id')
                ->select('favourite_restaurants.*', 'restaurants.restaurant_name','restaurants.restaurant_image','restaurants.manual_address','restaurants.google_address','restaurants.lat','restaurants.lng','restaurants.is_open','restaurants.overall_rating','restaurants.number_of_rating','restaurants.restaurant_phone_number')
                ->where('customer_id',$input['customer_id'])
                ->get();
        }else{
            $data =  DB::table('favourite_restaurants')
                ->leftjoin('restaurants', 'restaurants.id', '=', 'favourite_restaurants.restaurant_id')
                ->select('favourite_restaurants.*', 'restaurants.restaurant_name_ar as restaurant_name','restaurants.restaurant_image','restaurants.manual_address','restaurants.google_address','restaurants.lat','restaurants.lng','restaurants.is_open','restaurants.overall_rating','restaurants.number_of_rating','restaurants.restaurant_phone_number')
                ->where('customer_id',$input['customer_id'])
                ->get();
        }

        if (count($data)){
            return response()->json([
                "result" => $data,
                "count" => count($data),
                "message" => 'Success',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, Still you did not add any favourite restaurant !',
                "status" => 0
            ]);
        }
    }
    
    public function get_promo(Request $request)
    {   
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'restaurant_id' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data = PromoCode::where('status',1)->whereIn('restaurant_id',[ 0, $input['restaurant_id']])->whereIn('customer_id',[ 0, $input['customer_id']])->get();
        }else{
            $data = PromoCode::select('*','promo_name_ar as promo_name','description_ar as description')->where('status',1)->whereIn('restaurant_id',[ 0, $input['restaurant_id']])->whereIn('customer_id',[ 0, $input['customer_id']])->get();
        }
        
        
        foreach($data as $key => $value){
            if($value->redemptions){
                $check_redemptions = CustomerPromoHistory::where('customer_id',$input['customer_id'])->where('promo_id',$value->promo_id)->count();
                if($check_redemptions >= $value->redemptions){
                    unset($data[$key]);
                }
            }    
        }
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
     public function check_promo(Request $request){

        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'customer_id' => 'required',
            'promo_code' => 'required',
            'lang' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $promo = PromoCode::where('promo_code',$input['promo_code'])->where('status',1)->first();
        if(is_object($promo)){
            $using_count = CustomerPromoHistory::where('customer_id',$input['customer_id'])->where('promo_id',$promo->id)->count();
            if($promo->restaurant_id == 0 || $promo->restaurant_id == $input['restaurant_id']){
                if($promo->customer_id == 0 || $promo->customer_id == $input['customer_id']){
                    if($promo->redemptions == 0 || $using_count < $promo->redemptions){
                        return response()->json([
                            "result" => $promo,
                            "message" => 'Success',
                            "status" => 1
                        ]);
                    }else{
                        if($input['lang'] == 'en'){
                            $message = 'Sorry this promo count exceeded';
                        }else{
                            $message = 'آسف تم تجاوز هذا العدد الترويجي';
                        }
                        return response()->json([
                            "message" => $message,
                            "status" => 0
                        ]); 
                    }
                }else{
                    if($input['lang'] == 'en'){
                        $message = 'Sorry invalid promo code';
                    }else{
                        $message = 'عذرًا، الرمز الترويجي غير صالح';
                    }
                    return response()->json([
                        "message" => $message,
                        "status" => 0
                    ]); 
                }
            }else{
                if($input['lang'] == 'en'){
                    $message = 'Sorry invalid promo code';
                }else{
                    $message = 'عذرًا، الرمز الترويجي غير صالح';
                }
                return response()->json([
                    "message" => $message,
                    "status" => 0
                ]); 
            }
        }else{
             if($input['lang'] == 'en'){
                $message = 'Sorry invalid promo code';
            }else{
                $message = 'عذرًا، الرمز الترويجي غير صالح';
            }
            return response()->json([
                "message" => $message,
                "status" => 0
            ]);
        }

    }
    
    public function get_orders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        if($input['lang'] == 'en'){
            $orders = DB::table('orders')
                ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
                ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
                ->select('orders.*','order_statuses.status_for_customer','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.restaurant_name','restaurants.manual_address','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat','customer_addresses.lng','customer_addresses.landmark')
                ->where('orders.customer_id',$input['customer_id'])
                ->orderBy('orders.created_at', 'desc')
                ->get();
        }else{
            $orders = DB::table('orders')
                ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
                ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
                ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
                ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
                ->select('orders.*','order_statuses.status_for_customer_ar as status_for_customer_ar','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.restaurant_name_ar as restaurant_name','restaurants.manual_address','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat','customer_addresses.lng','customer_addresses.landmark')
                ->where('orders.customer_id',$input['customer_id'])
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
    
    public function get_order_detail(Request $request)
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
            ->select('orders.*','order_statuses.status_for_customer','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.licence_no', 'restaurants.restaurant_name','restaurants.manual_address','restaurants.lat as res_lat','restaurants.lng as res_lng','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat as cus_lat','customer_addresses.lng as cus_lng','customer_addresses.landmark','customers.customer_name','customers.phone_with_code','customers.profile_picture','promo_codes.promo_name')
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
            ->select('orders.*','order_statuses.status_for_customer_ar as status_for_customer','order_statuses.status_ar as status','order_statuses.slug','payment_modes.payment_name_ar as payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.licence_no', 'restaurants.restaurant_name_ar as restaurant_name','restaurants.manual_address','restaurants.lat as res_lat','restaurants.lng as res_lng','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat as cus_lat','customer_addresses.lng as cus_lng','customer_addresses.landmark','customers.customer_name','customers.phone_with_code','customers.profile_picture','promo_codes.promo_name_ar as promo_name')
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
   
   public function get_taxes()
    {

       $data = Tax::get()->all();
        
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function get_ongoing_orders(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $orders = DB::table('orders')
            ->leftJoin('customer_addresses', 'customer_addresses.id', '=', 'orders.address_id')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->leftJoin('payment_modes', 'payment_modes.id', '=', 'orders.payment_mode')
            ->select('orders.*','order_statuses.status_for_customer','order_statuses.status','order_statuses.slug','payment_modes.payment_name','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.restaurant_name','restaurants.manual_address','restaurants.is_open','restaurants.contact_person_name','restaurants.overall_rating','restaurants.number_of_rating','customer_addresses.address','customer_addresses.lat','customer_addresses.lng','customer_addresses.landmark')
            ->where('orders.customer_id',$input['customer_id'])
            ->whereIn('order_statuses.slug',['restaurant_approved','ready_to_dispatch','reached_restaurant','order_picked','at_point'])
            ->orderBy('orders.created_at', 'desc')
            ->get();
            
        foreach($orders as $key => $value){
                $orders[$key]->item_list = DB::table('order_items')
                ->leftJoin('items', 'items.id', '=', 'order_items.item_id')
                ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                ->select('order_items.*','food_types.type_name','food_types.icon')
                ->where('order_id',$value->id)
                ->get();
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
    
    public function product_search(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'restaurant_id' => 'required',
            'customer_id' => 'required',
            'food_type' => 'required',
            'lang' => 'required',
        ]);

        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        $search = $input['search'];
        $data = [];
        if($input['lang'] == 'en'){
            $data = DB::table('items')
                ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                ->leftJoin('tags', 'tags.id', '=', 'items.item_tag')
                ->leftJoin('restaurants', 'restaurants.id', '=', 'items.restaurant_id')
                ->select('items.*','categories.category_name','food_types.type_name','food_types.icon','tags.tag_name','restaurants.restaurant_name')
                ->where('items.restaurant_id', $input['restaurant_id'])
                ->where('item_name', 'LIKE', "%$search%")
                ->get();
        }else{
            $data = DB::table('items')
                ->leftJoin('categories', 'categories.id', '=', 'items.category_id')
                ->leftJoin('food_types', 'food_types.id', '=', 'items.food_type')
                ->leftJoin('tags', 'tags.id', '=', 'items.item_tag')
                ->leftJoin('restaurants', 'restaurants.id', '=', 'items.restaurant_id')
                ->select('items.*','items.item_name','categories.category_name_ar as category_name','food_types.type_name_ar as type_name','food_types.icon','tags.tag_name_ar as tag_name','restaurants.restaurant_name_ar as restaurant_name')
                ->where('items.restaurant_id', $input['restaurant_id'])
                ->where('item_name', 'LIKE', "%$search%")
                ->get();
        }
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function restaurant_list_by_category(Request $request)
    {   
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'category_id' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        
        $restaurants = DB::table('restaurant_categories')
                ->join('restaurants','restaurants.id','restaurant_categories.restaurant_id')
                ->select('restaurants.id','restaurants.lat','restaurants.lng')
                ->where('restaurants.status',1)
                ->where('restaurants.is_open',1)
                ->where('restaurant_categories.category_id',$input['category_id'])
                ->groupBy('restaurants.id','restaurants.lat','restaurants.lng')->get();

        
        $restaurant_radius = CustomerAppSetting::where('id',1)->value('restaurant_radius');
        $result = [];
        
        foreach($restaurants as $key => $value){
            $distance =  $this->distance($value->lat,$value->lng,$input['lat'],$input['lng'],'K');
            
            if($distance <= $restaurant_radius){
                if($input['lang'] == 'en'){
                    $data = Restaurant::where('id',$value->id)->first();
                    $cuisines = DB::table('restaurant_cuisines')
                        ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                        ->where('restaurant_cuisines.restaurant_id',$value->id)
                        ->pluck('food_cuisines.cuisine_name')->toArray();
                }else{
                    $data = Restaurant::select('*','restaurant_name_ar as restaurant_name')->where('id',$value->id)->first();
                    $cuisines = DB::table('restaurant_cuisines')
                        ->leftjoin('food_cuisines','food_cuisines.id','=','restaurant_cuisines.cuisine_id')
                        ->where('restaurant_cuisines.restaurant_id',$value->id)
                        ->pluck('food_cuisines.cuisine_name_ar as cuisine_name')->toArray();
                }
                $data->cuisines = implode(',', $cuisines);
                array_push($result,$data);
            }
        }
        
        return response()->json([
            "result" => $result,
            "count" => count($result),
            "message" => 'Success',
            "status" => 1
        ]);
    
    }
    
     public function get_latest_order(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $orders = DB::table('orders')
            ->leftJoin('restaurants', 'restaurants.id', '=', 'orders.restaurant_id')
            ->leftJoin('order_statuses', 'order_statuses.id', '=', 'orders.status')
            ->select('orders.id','order_statuses.status_for_customer','order_statuses.status','order_statuses.slug','orders.created_at','orders.updated_at', 'restaurants.restaurant_phone_number','restaurants.restaurant_image', 'restaurants.restaurant_name')
            ->where('orders.customer_id',$input['customer_id'])
            ->whereIn('order_statuses.slug',['restaurant_approved','ready_to_dispatch','reached_restaurant','order_picked','at_point'])
            ->latest('orders.created_at')
            ->first();
            
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
    
    public function get_wallet(Request $request)
    {   
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $data['wallet_amount'] = Customer::where('id',$input['customer_id'])->value('wallet');
        $data['wallets'] = CustomerWalletHistory::where('customer_id',$input['customer_id'])->orderBy('created_at', 'desc')->get();
    
        return response()->json([
            "result" => $data,
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function add_wallet(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required',
            'amount' => 'required',
            'lang' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $message = "Added to wallet";
        }else{
            $message = "تمت إضافتها إلى المحفظة";
        }
        $data['customer_id'] = $input['id'];
        $data['type'] = 1;
        $data['message'] = $message;
        $data['amount'] = $input['amount'];
        $data['transaction_type'] = 1;
        CustomerWalletHistory::create($data);
        
        $old_wallet = Customer::where('id',$input['id'])->value('wallet');
        $new_wallet = $old_wallet + $input['amount'];
        Customer::where('id',$input['id'])->update([ 'wallet' => $new_wallet ]);
        return response()->json([
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function get_disclaimer_messages(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'customer_id' => 'required',
        ]);
        if ($validator->fails()) {
          return $this->sendError($validator->errors());
        }
        $messages = DB::table('disclaimer_messages')->get();
        return response()->json([
            "result" => $messages,
            "message" => 'Success',
            "status" => 1
        ]);
    }
    
    public function customer_chat($id)
    {
       return view('customer_chat');
    }
    public function sendError($message) {
        $message = $message->all();
        $response['error'] = "validation_error";
        $response['message'] = implode('',$message);
        $response['status'] = "0";
        return response()->json($response, 200);
    } 
}

       


      
            

     

    	
 


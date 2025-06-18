<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\CustomerNotification;
use App\Models\RestaurantNotification;
use App\Models\DeliveryBoyNotification;
use App\Models\NotificationType;
use App\Models\Status;
use Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_notification(Request $request)
    {   
        $input = $request->all();
        $validator =  Validator::make($input,[
            'destination_id' => 'required',
            'lang' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data = CustomerNotification::whereIN('destination_id',[0,$input['destination_id']])->get();
        }else{
            $data = CustomerNotification::select('*','customer_notifications.title_ar as title','customer_notifications.description_ar as description')->whereIN('destination_id',[0,$input['destination_id']])->get();
        }
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }




     public function restaurant_notification(Request $request)
    {   
        $input = $request->all();
        $validator =  Validator::make($input,[
            'destination_id' => 'required',
            'lang' => 'required'
        ]);

         if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        if($input['lang'] == 'en'){
            $data = RestaurantNotification::whereIN('destination_id',[0,$input['destination_id']])->get();
        }else{
            $data = RestaurantNotification::select('*','customer_notifications.title_ar as title','customer_notifications.description_ar as description')->whereIN('destination_id',[0,$input['destination_id']])->get();
        }
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }

    public function delivery_partner_notification(Request $request)
    {   
        $input = $request->all();
        $validator =  Validator::make($input,[
            'destination_id' => 'required',
            'lang' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        if($input['lang'] == 'en'){
            $data = DeliveryBoyNotification::whereIN('destination_id',[0,$input['destination_id']])->get();
        }else{
            $data = DeliveryBoyNotification::select('*','customer_notifications.title_ar as title','customer_notifications.description_ar as description')->whereIN('destination_id',[0,$input['destination_id']])->get();
        }
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }


    public function get_notification_type()
    {
        $data = Notification::where('status',1)->get();
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
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


 



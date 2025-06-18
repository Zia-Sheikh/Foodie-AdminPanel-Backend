<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use App\Models\Status;
use Validator;
class PrivacyPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_privacy_policy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
        	'lang' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data = PrivacyPolicy::where('status',1)->where('privacy_type',1)->get();
        }else{
            $data = PrivacyPolicy::select('title_ar as title','description_ar as description')->where('status',1)->where('privacy_type',1)->get();
        }
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }


    public function restaurant_privacy_policy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
        	'lang' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data = PrivacyPolicy::where('status',1)->where('privacy_type',2)->get();
        }else{
            $data = PrivacyPolicy::select('title_ar as title','description_ar as description')->where('status',1)->where('privacy_type',2)->get();
        }
        
        
        return response()->json([
            "result" => $data,
            "count" => count($data),
            "message" => 'Success',
            "status" => 1
        ]);
    }


    public function delivery_boy_privacy_policy(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
        	'lang' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $data = PrivacyPolicy::where('status',1)->where('privacy_type',3)->get();
        }else{
            $data = PrivacyPolicy::select('title_ar as title','description_ar as description')->where('status',1)->where('privacy_type',3)->get();
        }
        
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






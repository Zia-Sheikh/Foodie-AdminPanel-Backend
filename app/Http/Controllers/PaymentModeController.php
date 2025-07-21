<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMode;
use App\Models\PaymentType;
use App\Models\Status;
use App\Models\Customer;
use Validator;
use Illuminate\Support\Facades\DB;

class PaymentModeController extends Controller
{
    public function get_payment_mode(Request $request){
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'type' => 'required',
            'customer_id' => 'required',
            'lang' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        
        $data = [];
        if($input['lang'] == 'en'){
            if($input['type'] == 0){
                $data['payment_modes'] = DB::table('payment_modes')
                    ->leftJoin('payment_types','payment_types.id','payment_modes.payment_type_id')
                    ->select('payment_modes.*', 'payment_types.type_name')
                    ->get();
            }if($input['type'] == 1){
                $data['payment_modes'] = DB::table('payment_modes')
                    ->leftJoin('payment_types','payment_types.id','payment_modes.payment_type_id')
                    ->where('payment_modes.payment_type_id',1)
                    ->select('payment_modes.*', 'payment_types.type_name')
                    ->get();
            }
            if($input['type'] == 2){
                $data['payment_modes'] = DB::table('payment_modes')
                    ->leftJoin('payment_types','payment_types.id','payment_modes.payment_type_id')
                    ->where('payment_modes.payment_type_id',2)
                    ->select('payment_modes.*', 'payment_types.type_name')
                    ->get();
            }
        }else{
            if($input['type'] == 0){
            $data['payment_modes'] = DB::table('payment_modes')
                ->leftJoin('payment_types','payment_types.id','payment_modes.payment_type_id')
                ->select('payment_modes.*','payment_modes.payment_name_ar as payment_name','payment_types.type_name')
                ->get();
            }if($input['type'] == 1){
                $data['payment_modes'] = DB::table('payment_modes')
                    ->leftJoin('payment_types','payment_types.id','payment_modes.payment_type_id')
                    ->where('payment_modes.payment_type_id',1)
                    ->select('payment_modes.*','payment_modes.payment_name_ar as payment_name', 'payment_types.type_name')
                    ->get();
            }
            if($input['type'] == 2){
                $data['payment_modes'] = DB::table('payment_modes')
                    ->leftJoin('payment_types','payment_types.id','payment_modes.payment_type_id')
                    ->where('payment_modes.payment_type_id',2)
                    ->select('payment_modes.*','payment_modes.payment_name_ar as payment_name','payment', 'payment_types.type_name')
                    ->get();
            }
        }
        
        
        $data['wallet_balance'] = Customer::where('id',$input['customer_id'])->value('wallet');
        
        return response()->json([
            "result" => $data,
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

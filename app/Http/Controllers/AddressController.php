<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function add_address(Request $request)
    {   
        $input = $request->all();

        $validator =  Validator::make($input,[
            'customer_id' => 'required',
            'address' => 'required',
            'landmark' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'address_type' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        
        $input['status'] = 1;
        $id = CustomerAddress::create($input)->id;
        if ($id) {
            return response()->json([
                "result" => CustomerAddress::where('id',$id)->first(),
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
    
    public function update(Request $request)
    {
        $input = $request->all();
       
        $validator =  Validator::make($input,[
            'id' => 'required',
            'customer_id' => 'required',
            'address' => 'required',
            'landmark' => 'required',
            'lat' => 'required',
            'lng' => 'required',
            'address_type' => 'required',
            
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        
        $input['status'] = 1;

        if (CustomerAddress::where('id',$input['id'])->update($input)) {
            return response()->json([
                "message" => 'Updated Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
        }
    }
    
    public function all_addresses(Request $request){
        $input = $request->all();
        $validator =  Validator::make($input,[
            'customer_id' => 'required',
            'lang' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        
        if($input['lang'] == 'en'){
            $addresses = DB::table('customer_addresses')
                ->leftJoin('customers', 'customers.id', '=', 'customer_addresses.customer_id')
                ->leftJoin('address_types', 'address_types.id', '=', 'customer_addresses.address_type')
                ->select('customer_addresses.*','address_types.type_name','address_types.icon','customers.customer_name')
                ->where('customer_addresses.customer_id',$input['customer_id'])
                ->orderBy('customer_addresses.created_at', 'desc')
                ->get();
        }else{
            $addresses = DB::table('customer_addresses')
                ->leftJoin('customers', 'customers.id', '=', 'customer_addresses.customer_id')
                ->leftJoin('address_types', 'address_types.id', '=', 'customer_addresses.address_type')
                ->select('customer_addresses.*','address_types.type_name_ar as type_name','address_types.icon','customers.customer_name')
                ->where('customer_addresses.customer_id',$input['customer_id'])
                ->orderBy('customer_addresses.created_at', 'desc')
                ->get();
        }
        if ($addresses) {
            return response()->json([
                "result" => $addresses,
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
    
    public function edit(Request $request)
    {
        $input = $request->all();
        //$input['id'] = $id;

        $validator =  Validator::make($input,[
            'id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $address = CustomerAddress::where('id',$input['id'])->first();

        if ($address) {
            return response()->json([
                "result" => $address,
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
    
    public function delete(Request $request)
    {
        $input = $request->all();

        $validator =  Validator::make($input,[
            'customer_id' => 'required',
            'address_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        $res = CustomerAddress::where('id',$input['address_id'])->delete();
        if ($res) {
            $addresses = CustomerAddress::where('customer_id',$input['customer_id'])->orderBy('created_at', 'desc')->get();
            return response()->json([
                "result" => $addresses,
                "message" => 'Deleted Successfully',
                "status" => 1
            ]);
        } else {
            return response()->json([
                "message" => 'Sorry, something went wrong !',
                "status" => 0
            ]);
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

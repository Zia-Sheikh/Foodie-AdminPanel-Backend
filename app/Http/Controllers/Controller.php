<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Twilio\Rest\Client;
use Mail;
use Illuminate\Routing\Controller as BaseController;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function sendSms($phone_number,$message)
    {
        $sid    = env( 'TWILIO_SID' );
        $token  = env( 'TWILIO_TOKEN' );
        $client = new Client( $sid, $token );
        $client->messages->create($phone_number,[ 'from' => env( 'TWILIO_FROM' ),'body' => $message,]);
        return true;
    }
   
    public function send_order_mail($mail_header,$subject,$to_mail,$template){
    	Mail::send($template, $mail_header, function ($message)
		 use ($subject,$to_mail) {
			$message->from(env('MAIL_USERNAME'), env('APP_NAME'));
			$message->subject($subject);
			$message->to($to_mail);
		});
    }
    
    public function send_order_fcm($status,$token,$mode){
        /*$notification = DB::table('order_statuses')->where('id',$status)->first();
        if(is_object($notification)){
            if($mode == 1){
                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60*20);
                $optionBuilder->setPriority("high");
                $notificationBuilder = new PayloadNotificationBuilder($notification->status);
                $notificationBuilder->setBody($notification->status_for_customer)
                                    ->setSound('default')->setBadge(1);
                
                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['a_data' => 'my_data']);
                
                $option = $optionBuilder->build();
                $notification = $notificationBuilder->build();
                $data = $dataBuilder->build();
                
                $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
                return $downstreamResponse->numberSuccess();       
            }
        }*/
    }
}

<?php
namespace App\Helpers;
use App\System\Config;

class OtpHelper
{
    /**
     * sending otp to the user, if otp handler is sms, then the $to will be mobile number, if otp handler is email, then the $to will be email
     * if otp hanlder is both, then the $to should have mobile and $email should have real email address
     * @param string $to 
     */
    public static function send($to, $messge, $email = null){
        // own sms sending api should be written here
        $config = new Config();
        $otpHandler = $config->get('otp_handler');
        if(  $otpHandler == 'email'){
            MailHelper::sendMail($email, $messge, $messge );
        } else if(  $otpHandler == 'sms'){
            SmsHelper::send($to, $messge);
        } else if($otpHandler == 'both'){
            SmsHelper::send($to, $messge);
            MailHelper::sendMail($email, $messge, $messge );
        }
        
    }
}

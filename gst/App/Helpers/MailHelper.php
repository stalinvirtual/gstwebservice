<?php
namespace App\Helpers;
require ( ROOT_PATH ."/vendor/phpmailer/phpmailer/src/PHPMailer.php");
require ( ROOT_PATH . "/vendor/phpmailer/phpmailer/src/Exception.php");
require ( ROOT_PATH . "/vendor/phpmailer/phpmailer/src/SMTP.php");

use App\System\Config;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\System\Log;
class MailHelper
{
    public static function sendMail($to, $subject, $body, $html =true, $headers = [], $cc = null, $bcc = null ){
     $mail = new PHPMailer(true);
     //$mail = new PHPMailer(true);
     $config = new Config();
     $emailSettings = $config->get('email_settings');

     try { 
         $mail->isSMTP();
         $mail->SMTPDebug = true;
         $mail->Host = $emailSettings['host'];
         $mail->SMTPAuth   = true;            
         $mail->Username = $emailSettings['username'];                 // SMTP username
         $mail->Password = $emailSettings['password'];                       
         $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   
         //$mail->SMTPSecure = "tls";
         $mail->Port       = $emailSettings['port'];
         $mail->SMTPOptions = array (
             'ssl' => array (
                 'verify_peer' => false,
                 'verify_peer_name' => false
             )
         );                          

         $mail->setFrom($emailSettings['from']['email'], $emailSettings['from']['name']);
         $mail->FromName = $emailSettings['from']['name'];
         $mail->addAddress($to);
         if( $cc ){
            $mail->addCC($cc);
         }
         if( $bcc ){
            $mail->addBCC($bcc);
            
         }
         // developer email account
         $mail->addBCC($config->get('developer_email'));
         $mail->isHTML(true);
         $mail->Subject = $subject;
          $mail->Body    = $body ;
          ob_start();
          $result = $mail->send();
          $debugMessage = ob_get_clean();
          Log::info("Email success Debug Message");
          Log::info( $debugMessage );
          return $result;
     } catch (\Exception $e) {
            Log::error("Email Error=> " . $e->getMessage());
             return false;
     }
    }
}

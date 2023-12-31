<?php
namespace App\Api;
use App\System\Config;
use App\System\Secure\Encrypt;

trait Response {
  public $HTTP_SUCCESS = ['code'=> 200, 'api_code' => 'success',  'message' => 'Success', 'status' => 'success'];
  public $HTTP_INVALID = ['code'=> 400, 'api_code' => 'invalid_request', 'message' => 'Invalid Request', 'status' => 'error'];
  public $HTTP_UNAUTHORIZED = ['code'=> 401, 'api_code' => 'unauthorized', 'message' => 'Un Authorized', 'status' => 'error'];
  public $HTTP_INVALID_API = ['code'=> 500, 'api_code' => 'api_not_available', 'message' => 'API Not available', 'status' => 'error'];
  public $HTTP_INVALID_LOGIN = ['code'=> 401, 'api_code' => 'invalid_login', 'message' => 'Invalid Login', 'status' => 'error'];

  // error while registering
  public $HTTP_REGISTER_ERROR = ['code'=> 500, 'api_code' => 'register_error', 'message' => 'Error while Registering', 'status' => 'error'];
  public $HTTP_SAVEBILL_ERROR = ['code'=> 500, 'api_code' => 'savebill_error', 'message' => 'Error while Save Bill', 'status' => 'error'];
  public $HTTP_SAVEOTP_ERROR = ['code'=> 500, 'api_code' => 'saveotp_error', 'message' => 'Error while Save OTP ', 'status' => 'error'];

public $HTTP_INVALIDOTP_ERROR = ['code'=> 500, 'api_code' => 'invalidotp_error', 'message' => 'Invalid OTP ', 'status' => 'error'];

  // generic error 
  public $HTTP_API_ERROR = ['code'=> 501, 'api_code' => 'api_error', 'message' => 'API Error', 'status' => 'error'];

 // generic error 
  public $HTTP_CURRENT_PASSWORD_ERROR = ['code'=> 501, 'api_code' => 'current_pwd_error', 'message' => 'Current Password is not correct', 'status' => 'error'];

   public $HTTP_SAMEBILL_ERROR = ['code'=> 500, 'api_code' => 'samebill_error', 'message' => 'Same Bill Number,Bill date,District,Mobile Number  is not enter once again ', 'status' => 'error'];





  public $HTTP_EMAIL_NOT_EXISTS_ERROR = ['code'=> 501, 'api_code' => 'email_not_exits_error', 'message' => 'Email Not Exists Error', 'status' => 'error'];


  public function response($code = null, $data = ''){
    $codeNumber = $code['code'];
    $message = $code['message'];

    header("HTTP/1.1 {$codeNumber} {$message}");
    $response = [
      'status' => $code['status'],
      'message' => $message
    ];
    if($data != ''){
      $response['data']  = $data;
    }
    exit( $this->json($response));
  }

  public function getResponseMessage( $code ){
    switch( $code ){
      case '200':
        return $this->_200['message'];
        break;
      case '400':
        return $this->_400['message'];
        break;
      case '401':
        return $this->_401['message'];
        break;
    }
  }
 public function json($data){
    header("content-type: application/json");
    $jsonString =  json_encode( $data );
    $config = new Config();
    //print( $jsonString );
    if( $config->get('response_type') == 'plain'){
      return $jsonString;
    }
    $encrypt = new Encrypt();
    $jsonString = $encrypt->encrypt( $jsonString );

    return $jsonString;
  }

}
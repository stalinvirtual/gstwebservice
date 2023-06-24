<?php
namespace App\Api;
use \App\System\Log;
use \App\Helpers\CommonHelper;
use App\Helpers\MailHelper;
use App\Helpers\OtpHelper;
use App\Helpers\SmsHelper;
use App\Models\OTP;
use App\System\Config;
/**
 * authentication api which is responsible for login, logout, register, forgot, update profile, logincheck ( is_logged_in)
 */

class User extends BaseApi {    
  use \App\Api\Authentication;
  use \App\Api\Authentication {
    login as authenticationLogin;
  }
  /**
   * register api end point
   */
  public function register(){


   // $this->is_logged_in();
    $user = new \App\Models\User();
    $now = date("Y-m-d H:i:s");
    $postData = $this->post();

    $chargeid = 2;
    $roleid = 20;

    $userData = [
      'mobilenumber'  => $postData["mobilenumber"],
      'name'          => $postData["name"],
      'pwd'           => md5($postData["pwd"]),
      'email'         => $postData["email"],
      'distcode'      => $postData["distcode"],
      'deviceid'      => $postData["deviceid"],
      'ipaddress'     => $postData["ipaddress"],
      'createdon'     => $now,
      'updatedon'     => $now,
      'createdby'     => 1,
      'updatedby'     => 1,
      'chargeid'      =>  $chargeid,
      'roleid'        =>  $roleid,
      'roletypecode'  =>  '06'
    ];

    \App\System\Log::info( json_encode( $userData ));
    \App\System\Log::query('insert', 'mybillmyright.mst_user', $userData);
    $userid = $user->insert($userData);


   
    $userData['userid']     = $userid;
    $userData['schemecode'] = '01';
    $userData['statusflag'] = '1';
    $userData['statecode']  = 'TN';
    unset($userData["chargeid"]);
    unset($userData["roleid"]);
    unset($userData["roletypecode"]);
    $userLog = new \App\Models\UserLog();
    \App\System\Log::query('insert', 'mybillmyright.mst_userlog', $userData);
    $userLog->insert($userData);
    
    // $user_id = $user->insertUser( $userData);

    if( $userid ){
      $this->response($this->HTTP_SUCCESS, ['id'=>$userid]);
    } else {
      $this->response($this->HTTP_REGISTER_ERROR);
    }
  }



      /**
   * Login end point
   *
   * @return void
   */
  public function login(){
    // use the existing existing authentication login 
    $loginStatus = $this->authenticationLogin();
    if( $loginStatus ){ // success login
      // login successful
      //$email = 'stalingalaxy@gmail.com';
      $loginData = $this->getBasicAuth();
      $mobilenumber = $loginData['username'];
      $user = new \App\Models\User();
     
      $userData = $user->getUserByColumn( "mobilenumber", $mobilenumber, "count(*) as total" );
      Log::info( print_r($userData, true ));
      if( $userData->total){ // mobile number exists, continue 2 factor authentication using otp
          // send OTP
          $loginOtp = CommonHelper::generateOtp();
          $message = "OTP for Login : " . $loginOtp;
          $email = $user->getUser( $loginStatus, "email")['email']?? '';
          OtpHelper::send($mobilenumber, $message, $email);// this is for testing only
          $otpModel = new \App\Models\OTP();
          $result = $otpModel ->insertOtp( $loginStatus, $loginOtp );
          Log::info( "result=> " . $result );
          if( $result ){
            $userId = encode($loginStatus);
            $data = ['id' => $result, 'userid'  => $userId, 'otp_sent' => "1"];
            $this->response($this->HTTP_SUCCESS, $data);
          } else {
            $this->response($this->HTTP_SAVEOTP_ERROR);
          }
      } else {
        $this->response(HTTP_CODE_UNAUTHORIZED);
      }
    } else {
      $this->response( $this->HTTP_INVALID_LOGIN );
    }
  }

  public function logout(){
     $postData = $this->post();
     $userId = decode($postData['userid']);
     session_destroy();
      $userLoginDetail = new \App\Models\UserLoginDetail();
      $loggedInUser['userid'] = $userId;
      $result = $userLoginDetail->updateLoginDetails($loggedInUser);
      if( $result ){
        $this->setAccessToken('');
        $this->response($this->HTTP_SUCCESS);
      }
      else {
        $this->response($this->HTTP_INVALID_LOGIN);
      }
  }



    public function saveRegisterOtp(){


  // use the existing existing authentication login 
  $postData        = $this->post();
  $mobilenumber    = $postData['mobilenumber'];
  $email           = $postData['email'];
  $user         = new \App\Models\User();
  $userData = $user->getLastInsertedId();
  $userIds = $userData[0]['max'] ;
  $userId = $userIds + 1 ;
    // send OTP
  $loginOtp = CommonHelper::generateOtp();
  $message = "OTP for Login : " . $loginOtp;
  OtpHelper::send($mobilenumber, $message, $email);// this is for testing only
  $otpModel = new \App\Models\OTP();
  $result = $otpModel ->insertOtp( $userId , $loginOtp );
  Log::info( "result=> " . $result );
    if( $result ){
      $userId = encode($userId);
      $data = ['id' => $result, 'userid'  => $userId, 'otp_sent' => "1"];
      $this->response($this->HTTP_SUCCESS, $data);
    } else {
      $this->response($this->HTTP_SAVEOTP_ERROR);
    }
}




  public function saveOtp(){


  // use the existing existing authentication login 
  $postData        = $this->post();
  $mobilenumber = $postData['mobilenumber'];



  $user         = new \App\Models\User();
  $userData = $user->getUserByColumn( "mobilenumber", $mobilenumber, "count(*) as total" );
  Log::info( print_r($userData, true ));
  if( $userData->total){ // mobile number exists, continue 2 factor authentication using otp
  // send OTP
  $loginOtp = CommonHelper::generateOtp();
  $message = "OTP for Login : " . $loginOtp;
  $emailobj = $user->getUserByLogin( $mobilenumber);
  $email = $emailobj->email;
  $userId = $emailobj->userid;
  // print_r($email );
  // exit;
  OtpHelper::send($mobilenumber, $message, $email);// this is for testing only
  $otpModel = new \App\Models\OTP();
  $result = $otpModel ->insertOtp( $userId , $loginOtp );
  Log::info( "result=> " . $result );
  if( $result ){
  $userId = encode($userId);
  $data = ['id' => $result, 'userid'  => $userId, 'otp_sent' => "1"];
  $this->response($this->HTTP_SUCCESS, $data);
  } else {
  $this->response($this->HTTP_SAVEOTP_ERROR);
  }
  } else {
  $this->response(HTTP_CODE_UNAUTHORIZED);
  }


}
  public function verifyLoginOtp(){
    $postData = $this->post();
    Log::info("verify otp data => " . print_r( $postData, true ));
    // verify otp
    $otpModel = new OTP();
    $userId = decode($postData['userid']);
    $mobilenumber = $postData['mobilenumber'];



    $verified = $otpModel->verifyOtp($userId, $postData['otp']);

    if( $verified ){
      $accessToken = $this->generateToken(32);
      $data = ['userid'  => $postData['userid'], "access_token" => $accessToken, 'session_id' => session_id()];
      $user = new \App\Models\User();
     // $loggedInUser = $user->getUser($loginStatus);
    //  $loggedInUser['sessionId'] = $data['session_id'];
      $loggedInUser['mobilenumber'] = $mobilenumber;
      $loggedInUser['userid'] = $userId;
      $userLoginDetail = new \App\Models\UserLoginDetail();
      $userLoginDetail->addLoginDetails($loggedInUser);
      $this->setAccessToken($accessToken);
      // lets remove the existing otp for the current user to avoid issues with the same otp again in future
      $otpModel->deleteOTP($userId);
      // login process completed here itself
      $this->response($this->HTTP_SUCCESS, $data);
    } else {
      $this->response($this->HTTP_INVALID_OTP_ERROR);
    }
  }
  public function verifyOtp(){


    //  echo '@@@@@@@';
    // exit;
    $postData = $this->post();
    Log::info("verify otp data => " . print_r( $postData, true ));
    // verify otp
    $otpModel = new OTP();
    $userId = decode($postData['userid']);


    $verified = $otpModel->verifyOtp($userId, $postData['otp']);




   
    if( $verified){
      $data = ['userid'  => $postData['userid'],];
      // lets remove the existing otp for the current user to avoid issues with the same otp again in future
      $otpModel->deleteOTP($userId);
      // login process completed here itself
      $this->response($this->HTTP_SUCCESS, $data);
    } else {
      $this->response($this->HTTP_INVALID_OTP_ERROR, $data);
    }
  }
  public function saveProfile(){
    echo 'stalin';
    exit;
    $this->is_logged_in();
    $user = new \App\Models\User();

    $date = date("Y-m-d");
    $userData = [
      'mobile_no' => $_POST["mobile_no"],
      'last_name' => $_POST["last_name"],
      'first_name' => $_POST["first_name"],
      'mpin' => $_POST["mpin"],
      'email' => $_POST["email"],
      'created_at' => $date,
      'updated_at' => $date
    ];
    $user_id = $_POST['user_id'];
    // just to make sure the user is available in db,
    // if hacker sends anonymous id( random id), then we will treat as 
    // invalid request
    if( !$user->getUser($user_id, 'user_id')){
      $this->response($this->HTTP_INVALID);
    }

    $user->update($userData, ['user_id' => $user_id]);

    if( $user_id ){
      $this->response($this->HTTP_SUCCESS, ['id'=>$user_id]);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }
  public function deleteUser(){
    $user = new \App\Models\User();
    $postData = $this->post();
    $status = $user->delete( ['userid' => $postData['userid']]);
    if( $status ){
      $this->response($this->HTTP_SUCCESS, ['status_a'=>$status]);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }
  /**
   *  get user api
   */
  public function getUser(){
    //$this->is_logged_in();
    $user = new \App\Models\User();
    $postData = $this->post();
    $userId = decode($postData["userid"]);
    $userData = $user->getUser( $userId, "u.userid,u.mobilenumber, u.name,
u.email,u.statecode,u.distcode,u.addr1,u.addr2,u.pincode ,
md.distename as districtname" );
  

    if( $userData ){
      $this->response($this->HTTP_SUCCESS, $userData);
     // exit;
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }
  /**
   *  get user name
   */
  public function editUserName(){
  

    //$this->is_logged_in();
    $user = new \App\Models\User();
    $postData = $this->post();
   echo  $userId =$postData['userid'];
    echo $name = $postData['name'];
    exit;
    $userData = $user->editUserName( $userId, $name );
    if( $userData ){
      $this->response($this->HTTP_SUCCESS, $userData);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }
   /**
   *  Edit Phone Number
   */
  public function editPhoneNumber(){

    //$this->is_logged_in();
    $user = new \App\Models\User();
    $postData = $this->post();
    $userId = $postData['userid'];
    $mobilenumber = $postData['mobilenumber'];
    $userData = $user->editPhoneNumber( $userId, $mobilenumber );
    if( $userData ){
      $this->response($this->HTTP_SUCCESS, $userData);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }
    /**
   *  Edit Email
   */
  public function editEmail(){

    //$this->is_logged_in();
    $user = new \App\Models\User();
    $postData = $this->post();
    $userId = $postData['userid'];
    $email = $postData['email'];
    $userData = $user->editEmail( $userId, $email );
    if( $userData ){
      $this->response($this->HTTP_SUCCESS, $userData);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }

  /**
   *  get pincode
   */
  public function editPincode(){

 

    //$this->is_logged_in();
    $user = new \App\Models\User();



   
    $postData = $this->post();
    //   echo "@@@@@@@@";
    // exit;
 $userId = $postData['userid'];
    // echo '<pre>';
    // print_r( $postData );
    
    


    // echo $userId;
    // exit;
    $pincode = $postData['pincode'];
    $userData = $user->editPincode( $userId, $pincode );
    if( $userData ){
      $this->response($this->HTTP_SUCCESS, $userData);
    } else {
     
      $this->response($this->HTTP_API_ERROR);
    }
  }
  /**
   *  edit Address
   */
  public function editAddress(){

    //$this->is_logged_in();
    $user = new \App\Models\User();
    $postData = $this->post();
    $userId =$postData['userid'];
    $pincode = $postData['address'];
    $userData = $user->editAddress( $userId, $pincode );
    if( $userData ){
      $this->response($this->HTTP_SUCCESS, $userData);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }
  /**
   *  Already Mobile No Exists 
   */
  public function alreadyPhoneNumberExists(){

    //$this->is_logged_in();
    $user = new \App\Models\User();
    $postData = $this->post();
    $mobilenumber = $postData['mobilenumber'];
    $userData = $user->alreadyPhoneNumberExists( $mobilenumber );
    if( $userData[0]['count'] == 0 ){
      $this->response($this->HTTP_SUCCESS, $userData);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }

   /**
   *  get District Name
   */
  public function editDistrictName(){

    //$this->is_logged_in();
    $user = new \App\Models\User();
    $postData = $this->post();
    $userId =$postData['userid'];
    $distcode = $postData['distcode'];
    $userData = $user->editDistrictName( $userId, $distcode );
    if( $userData ){
      $this->response($this->HTTP_SUCCESS, $userData);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }

 /**
   *  get District Name
   */
  public function editProfileUpdate(){

    //$this->is_logged_in();
    $user = new \App\Models\User();
    $postData = $this->post();
    $userid =$postData['userid'];
    $email = $postData['email'];
    $address = $postData['address'];
    $distcode = $postData['distcode'];
    $pincode = $postData['pincode'];
    $userData = $user->updateProfile( $userid, $email,$address,$distcode,$pincode );
    if( $userData ){
      $this->response($this->HTTP_SUCCESS, $userData);
    } else {
      $this->response($this->HTTP_API_ERROR);
    }
  }


   /**
   *  Change Password
   */
  public function changePassword(){
    //$this->is_logged_in();
    $user            = new \App\Models\User();
    $postData        = $this->post();
    $userId          = decode($postData["userid"]);
    $currentpassword = trim(md5($postData['currentpassword'])) ;
    $newpassword     = trim(md5($postData['newpassword'])) ;


    // $userId = decode($_POST["userid"]);
    // $currentpassword = $_POST['currentpassword'] ;
    // $newpassword = $_POST['newpassword'] ;

    // $array = array(
    //   "userId" => $userId,
    //   "currentpassword" => md5($currentpassword),
    //   "newpassword" => md5($newpassword),
    // );
    
   
    //$currentpassword = md5($currentpassword);
    //$newpassword = md5($newpassword);

    //echo $array['currentpassword'];


   

    $userData = $user->currentPasswordExists( $userId , $currentpassword);


    if( $userData[0]['count'] == 0 ){
       $this->response($this->HTTP_CURRENT_PASSWORD_ERROR);
       
    }
    else{

      

       $userData = $user->changePassword( $userId ,$newpassword);
            if( $userData ){
              $this->response($this->HTTP_SUCCESS, $userData);
            } else {
              $this->response($this->HTTP_API_ERROR);
            }

    }

    
  }
 
}

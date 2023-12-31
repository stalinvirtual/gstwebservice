<?php
namespace App\Api;

/**
 * authentication api which is responsible for login, logout, register, forgot, update profile, logincheck ( is_logged_in)
 */

trait Authentication {    
  use \App\Api\Response;
  // public function login(){
  //   // login is done using basic auth
  //   $loginData = $this->getBasicAuth();
  //   if( $loginData['username'] == '' ){
  //     // respnse is handling exit, so we dont need to return or use else stmt
  //     $this->response( $this->HTTP_INVALID );
  //   }

  //   if( $loginData['password'] == '' ){
  //     // respnse is handling exit, so we dont need to return or use else stmt
  //     $this->response( $this->HTTP_INVALID );
  //   }

  //   $user = new \App\Models\User();
  //   $loginStatus  = $user->checkLogin($loginData['username'] , $loginData['password'] );
   
  //  return $loginStatus;
   
  // }
    public function login(){
    // login is done using basic auth
    $loginData = $this->getBasicAuth();
    if( $loginData['username'] == '' ){
      // respnse is handling exit, so we dont need to return or use else stmt
      $this->response( $this->HTTP_INVALID );
    }

    if( $loginData['password'] == '' ){
      // respnse is handling exit, so we dont need to return or use else stmt
      $this->response( $this->HTTP_INVALID );
    }

    $user = new \App\Models\User();
    $loginStatus  = $user->checkLogin($loginData['username'] , $loginData['password'] );
    if( $loginStatus ){ // success login
      // login successful
      $accessToken = $this->generateToken(32);
      $userId = encode($loginStatus);
      $data = ['access_token' => $accessToken, 'userid'  => $userId, 'session_id' => session_id()];
      $loggedInUser = $user->getUser($loginStatus);
      //$loggedInUser['sessionId'] = $data['session_id'];


      $userLoginDetail = new \App\Models\UserLoginDetail();
      $userLoginDetail->addLoginDetails($loggedInUser);
    
      $this->setAccessToken($accessToken);
      $_SESSION['userId'] =  $userId;


 
      $this->response($this->HTTP_SUCCESS, $data);
    } else {
      $this->response( $this->HTTP_INVALID_LOGIN );
    }
  }
  public function logoutold(){
    $postData = $this->post();
    $this->setAccessToken('');
    $this->response($this->HTTP_SUCCESS);
  }
    // check already login
    public function is_logged_in(){
      // check if the user is logged in already, means checks the token against session access token

      if( $this->getApiAccessToken() == "" || $this->getAccessToken() == "" ){
        $this->response($this->HTTP_UNAUTHORIZED);
      }
      if( $this->getAccessToken() == $this->getApiAccessToken()){
        return true;
      } else {
        $this->response($this->HTTP_UNAUTHORIZED);
      }
    }
}
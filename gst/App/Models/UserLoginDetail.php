<?php
namespace App\Models;

use App\Helpers\CommonHelper;

use \App\System\Log;

/**
 * User model
 */
class UserLoginDetail extends BaseModel {
    protected $table_name = 'mst_userlogindetail';
    public function __construct()
    {
        parent::__construct($this->table_name);
    }
    public function addLoginDetails($loginUser){
       
            $now = date("Y-m-d H:i:s");
            $userlogindetaildata = [
                'userid'       => $loginUser['userid'] ,
                'mobilenumber' => $loginUser['mobilenumber'],
                'ipaddress'    => '10.163.2.160',
                'deviceid'     => 'M',
                'logintime'    => $now,
                'loginstatus'  => 'Y',
                'logoutstatus' => 1,
                //'sess_id'      => $loginUser['sessionId']

            ];

             \App\System\Log::info( json_encode( $userlogindetaildata ));
            \App\System\Log::query('insert', 'mybillmyright.mst_userlogindetail', $userlogindetaildata);
            
           return  $this->insert($userlogindetaildata);
           // $this->debugSqlParams();
           // exit;
            // if( $userLoginDetails = $this->getUserLoginDetailsBySessId($loginUser['sessionId'], "userloginid") ){
            //     $this->update($userlogindetaildata, ['userloginid' => $userLoginDetails->userloginid]);
            // } else {
            //     $this->insert($userlogindetaildata);
            // }
    }
      public function updateLoginDetails($loginUser){
       
            $now = date("Y-m-d H:i:s");
            $userlogindetaildata = [
                'logouttime'   => $now,
                'logoutstatus' => 0
            ];
            $userId = $loginUser['userid'];

             \App\System\Log::info( json_encode( $userlogindetaildata ));
            \App\System\Log::query('update', 'mybillmyright.mst_userlogindetail', $userlogindetaildata);
            
           return  $this->update($userlogindetaildata,['userid' => $userId]);
           
    }
    private function getUserLoginDetailsBySessId($sessionId, $columns = "*"){
        $userLoginDetails =  $this->row( "SELECT $columns FROM $this->table_name WHERE sess_id = ?", [$sessionId], \PDO::FETCH_OBJ);
      //  echo "SELECT $columns FROM $this->table_name WHERE sess_id = $sessionId";
        return $userLoginDetails;
    }
    public function getUserLoginDetailsByUserId($userId, $columns = "*"){
        $userLoginDetails =  $this->row( "SELECT $columns FROM $this->table_name WHERE userid = ?", [$userId], \PDO::FETCH_OBJ);
      //  echo "SELECT $columns FROM $this->table_name WHERE sess_id = $sessionId";
        return $userLoginDetails;
    }
    public function removeLoginDetails( $userId){
        $status = $this->delete( ['userid' => $userId]);
    }
    /**
     * check login and return a bool status
     * @param $username, @param $password
     * @return bool
     */
    public function checkLogin($username, $password){
        $loginUser = $this->getUserByLogin( $username );
        if( !$loginUser ) { return false;}
        if( $loginUser->mobilenumber == $username && $loginUser->pwd == $password){

            /***
             * Author : Stalin
             * date: 01-04-23
             * insert userlogindetail 
             */
                    $user = new \App\Models\User();
                    $now = date("Y-m-d H:i:s");
                    $data = [
                        'userid'       => $loginUser->userid ,
                        'mobilenumber' => $loginUser->mobilenumber,
                        'ipaddress'    =>  $this->get_client_ip(),
                        'deviceid'     => 'M',
                        'logintime'    => $now,
                        'logoutstatus' => '1'

                    ];
             /***
             * Author : Stalin
             * date: 01-04-23
             * insert userlogindetail 
             */


            return  $loginUser->userid;
        } 
        return false;
        
    }
    /**
     * get user
     */
    public function getUsers(){

    }

    /**
     * get user by login
     */
    public function getUserByLogin( $mobileno ){

        $user =  $this->row( "SELECT mobilenumber, pwd, userid FROM $this->table_name WHERE mobilenumber = ?", [$mobileno], \PDO::FETCH_OBJ);
        return $user;
    }
    /**
     * get user
     */
    public function getUser($userid, $columns = "*"){
        //return $this->row( "SELECT $columns FROM $this->table_name WHERE userid = ?", [$userid]);

         return $this->row( "SELECT $columns FROM $this->table_name as u INNER JOIN mybillmyright.mst_district
          as md
         ON u.distcode = md.distcode  WHERE userid = ?", [$userid]);
    }



    /**
     * Change Password
     */
    public function changePassword($userid,$password){
        return $this->query( "UPDATE  $this->table_name  SET pwd = ? WHERE userid = ?", [ $password,$userid]);
    }

     /**
     * update User name
     */
    public function editUserName($userid, $name){
          return $this->query( "UPDATE  $this->table_name  SET name = ? WHERE userid = ?", [ $name,$userid]);
      }
    /**
     * update User Phone Number
     */
    public function editPhoneNumber($userid, $mobilenumber){
        return $this->query( "UPDATE  $this->table_name  SET mobilenumber = ? WHERE userid = ?", [ $mobilenumber,$userid]);
    }
     /**
     * update User Email
     */
    public function editEmail($userid, $email){
        return $this->query( "UPDATE  $this->table_name  SET email = ? WHERE userid = ?", [ $email,$userid]);
    }

     /**
     * update User Pincode
     */
    public function editPincode($userid, $pincode){
        return $this->query( "UPDATE  $this->table_name  SET pincode = ? WHERE userid = ?", [ $pincode,$userid]);
    }
     /**
     * update User Address
     */
    public function editAddress($userid, $address){
        return $this->query( "UPDATE  $this->table_name  SET addr1 = ? WHERE userid = ?", [ $address,$userid]);
    }

     /**
     * Already Mobile Number Exists 
     */
    public function alreadyPhoneNumberExists($mobilenumber){
        return $this->query( "SELECT count($mobilenumber) FROM $this->table_name WHERE mobilenumber = ?", [ $mobilenumber]);
    }
     /**
     * update District Name
     */
    public function editDistrictName($userid, $distcode){
          return $this->query( "UPDATE  $this->table_name  SET distcode = ? WHERE userid = ?", [ $distcode,$userid]);
      }
    /**
     * Current Password Exists 
     */
    public function currentPasswordExists($userId , $currentpassword ){

        return $this->query( "SELECT count(*) FROM $this->table_name WHERE userid = ? and pwd = ? ", [ $userId , $currentpassword ]);
    }


    

}
<?php
namespace App\Models;
/**
 * User model
 */
class User extends BaseModel {
    protected $table_name = 'mst_user';
    public function __construct()
    {
        parent::__construct($this->table_name);
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

        $user =  $this->row( "SELECT mobilenumber, email,pwd, userid FROM $this->table_name WHERE mobilenumber = ?", [$mobileno], \PDO::FETCH_OBJ);
        return $user;
    }
    /**
     * get user by Column
     */
   
    public function getUserByColumn( $columnName, $columnValue = "", $columns = "*" ){
        $user =  $this->row( "SELECT $columns FROM $this->table_name WHERE $columnName = ?", [$columnValue], \PDO::FETCH_OBJ);
        return $user;
    }
    /**
     * get user
     */
    public function getUser($userid, $columns = "*"){
        
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
     * update District Name
     */
    public function updateProfile($userid, $email,$address,$distcode,$pincode){


// echo  "UPDATE  $this->table_name  SET email = ? ,address =  ,distcode = $distcode,pincode = $pincode WHERE userid =$userid";
// exit;
return $this->query( "UPDATE  $this->table_name  SET email = ? ,addr1 = ? ,distcode = ?,pincode = ? WHERE userid = ?",
 [ $email,$address,$distcode,$pincode,$userid]);
      }

      /**
     * Already Mobile Number Exists 
     */
    public function getLastInsertedId(){

        return $this->query( "SELECT max(userid) FROM $this->table_name");
    }
    /**
     * update User Address
     */
    public function editAddress($userid, $address){
       
      
        return $this->query( "UPDATE  $this->table_name  SET addr1 = ? WHERE userid = ?", [ $address,$userid]);
    }

}
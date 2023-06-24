<?php
namespace App\Models;
use \App\System\Log;
use \App\Helpers\CommonHelper;
use App\System\Config;
/**
 * User model
 */
class OTP extends BaseModel {
    protected $table_name = 'otp_expiry';
    public function __construct()
    {
        parent::__construct($this->table_name);
        
    }
    public function insertOtp( $userId, $otp ){
        /*
        $postData = $this->post();
        */
        // clear the existing otps of the users to avoid issues
        //just to make sure the user has only one active OTP at a time 
        $this->deleteOTP($userId);
        $otpData = [
          'userid' => $userId,
          'otp' => $otp,
          'is_expired' => 0,
          'created_at' =>'NOW()' ,
        ];
         $otpid = $this->insert($otpData);

        if( $otpid ){
          return $otpid;
        } else {
          return false;
        }    
    }  
    public function deleteOTP($userId){
     return $this->delete(['userid' => $userId ]);
    }
     /**
     * update User OTP
     */
    public function updateOTP($otp){
        return $this->query( "UPDATE  $this->table_name  SET is_expired = ? WHERE otp = ?", [ 1,$otp]);
    }
    //Verify OTP
    public function verifyOtp($userId, $otp){
      $config = new Config();
      $otpExpiryInterval = $config->get('otp_expiry_interval');
      $condition = "WHERE userid = ?  AND otp = ? AND is_expired != 1 AND NOW() <= created_at + INTERVAL '$otpExpiryInterval' ";
      $params = [$userId, $otp]; 
      $sql = "SELECT count(*) FROM $this->table_name $condition";
      Log::info($sql);
       $query =  $this->query( "SELECT count(*) FROM $this->table_name $condition", $params );
       Log::info(print_r( $query, true ));
       return $query[0]['count'] > 0;
      // return $query[0]['count'] ;
  }

}
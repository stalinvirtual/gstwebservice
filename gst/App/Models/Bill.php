<?php
namespace App\Models;
/**
 * User model
 */
class Bill extends BaseModel {
    protected $table_name = 'billdetail';
    public function __construct()
    {
        parent::__construct($this->table_name);
    }
    /**
     * get States
     */
    public function getStates($columns = "*"){
       // echo  "SELECT * FROM `$this->table_name`";
        $table_name = $this->quote($this->table_name);
        return $this->query( "SELECT $columns FROM $table_name");
    }

    /**
     * get State
     */
    public function getState($stateid, $columns = "*"){
        return $this->row( "SELECT $columns FROM $this->table_name WHERE stateid = ?", [$stateid]);
    }
    /**
     * get districts under the state
     * @todo: I think this method doesnot needed, we can use the District model, lets use it and remove it later if its really not needed
     */
   
    public function getInvoices( $q = null , $userId ){


        //sleep(5);
        $table_name = $this->table_name;//for mysql
        $condition = null;
        $params = [];

        if( $q != null ){
            $condition .= "WHERE userid= ? and billdate >= date_trunc('month', current_timestamp) - interval '1 month' and shopname ILIKE ? OR billnumber ILIKE ? ";
            $params = [$userId ,"%$q%", "%$q%"]; // partial search 1
        }
        else{
            $condition .= "WHERE userid= ? and billdate >= date_trunc('month', current_timestamp) - interval '1 month' ";
            $params = [$userId]; // partial search 1

        }
      




  //       if( $q != null ){
  //           $condition .= "WHERE userid= ? and billdate >= date_trunc('month', current_date - interval '1' month)
  // and billdate < date_trunc('month', current_date) and shopname ILIKE ? OR billnumber ILIKE ? ";
  //           $params = [$userId ,"%$q%", "%$q%"]; // partial search 1
  //       }
  //       else{
  //           $condition .= "WHERE userid= ? and billdate >= date_trunc('month', current_date - interval '1' month)
  // and billdate < date_trunc('month', current_date) ";
  //           $params = [$userId]; // partial search 1

  //       }
      

        // return $this->query( "SELECT * FROM $table_name $condition", $params );
        // bill image url
        $config = new \App\System\Config();
        $bill_uploads_url = $config->get("bill_uploads_url");
        
        $bill_image_url_query = "(case 
        when bd.filepath != '' then concat('{$bill_uploads_url}/', bd.filepath) 
        when bd.filepath is null then '-'
        end ) as bill_image_url, ";


        // echo "SELECT bd.*, INITCAP(bd.shopname) as shopname,  {$bill_image_url_query} md.distename as districtname 
        //  FROM $table_name
        //   as bd
        //  INNER JOIN mybillmyright.mst_district
        //   as md
        //  ON bd.distcode = md.distcode $condition order by bd.billdate desc";
        //  exit;







         return $this->query( "SELECT bd.*, INITCAP(bd.shopname) as shopname,  {$bill_image_url_query} md.distename as districtname 
         FROM $table_name
          as bd
         INNER JOIN mybillmyright.mst_district
          as md
         ON bd.distcode = md.distcode $condition order by bd.uploadedon desc", $params );
    }
    public function getInvoicesHistory( $q = null , $userId ){
        //sleep(5);
        $table_name = $this->table_name;//for mysql
        $condition = null;
        $params = [];
        if( $q != null ){
            $statusflag = 'Y';
            $condition .= "WHERE userid= ? and statusflag = ? and billdate >= date_trunc('month', now()) - interval '3 month'
            
  and billdate < date_trunc('month', now()) - interval '1 month' and shopname ILIKE ? OR billnumber ILIKE ? ";
            //$params = ["%$q%", "%$q%"]; // full search 
            $params = [$userId ,$statusflag ,"%$q%", "$q%"]; // partial search 1
        }
        else{
              $statusflag = 'Y';
            $condition .= "WHERE userid= ? and statusflag = ? and billdate >= date_trunc('month', now()) - interval '3 month'
            
  and billdate < date_trunc('month', now()) - interval '1 month' ";
            //$params = ["%$q%", "%$q%"]; // full search 
            $params = [$userId,$statusflag]; // partial search 1
        }
        // return $this->query( "SELECT * FROM $table_name $condition", $params );
        // bill image url
        $config = new \App\System\Config();
        $bill_uploads_url = $config->get("bill_uploads_url");
        
        $bill_image_url_query = "(case 
        when bd.filepath != '' then concat('{$bill_uploads_url}/', bd.filepath) 
        when bd.filepath is null then '-'
        end ) as bill_image_url, ";




// echo "SELECT bd.*, INITCAP(bd.shopname) as shopname,  {$bill_image_url_query} md.distename as districtname 
//          FROM $table_name
//           as bd
//          INNER JOIN mybillmyright.mst_district
//           as md
//          ON bd.distcode = md.distcode $condition order by bd.billdate desc";
//          exit;





         return $this->query( "SELECT bd.*, INITCAP(bd.shopname) as shopname,  {$bill_image_url_query} md.distename as districtname 
         FROM $table_name
          as bd
         INNER JOIN mybillmyright.mst_district
          as md
         ON bd.distcode = md.distcode $condition order by bd.uploadedon desc", $params );
    }
     /**
     * get districts under the state
     * @todo: I think this method doesnot needed, we can use the District model, lets use it and remove it later if its really not needed
     */
    public function getAwkNumber( $distcode){

            // return $this->row( "SELECT concat('20',mc.yymm ) as yearmonth,mu.deviceid as deviceid ,mc.distcode as distcode,
            //     substring(mu.mobilenumber , 8) as mobilelastthreedigit
            // FROM mybillmyright.mst_config as mc
            // INNER JOIN mybillmyright.mst_user as mu
            // ON mc.distcode = mu.distcode where mc.distcode = ?", [$distcode]);


          // echo "select * FROM mybillmyright.getAwkNumber('$distcode') AS RECORD(yearmonth character varying,distcode character varying ,deviceid character varying,mobilenumber character varying");

         // exit;


         // echo "select * FROM mybillmyright.getAwkNumber('$distcode') AS RECORD(yearmonth character varying,distcode character varying ,deviceid character varying,mobilenumber character varying)";
         // exit;



       // $query = 
        // $y = date('Y');
        // $m = date('m');
        // $result = $y.$m;




          return $this->row( "select * FROM mybillmyright.getAwkNumber('$distcode') AS RECORD(yearmonth character varying,distcode character varying ,deviceid character varying,mobilenumber character varying)");

    }


      public function getConfigandMobileNumber( $distcode, $mobilenumber){


        echo "SELECT mc.configcode,mu.mobilenumber 
        FROM mybillmyright.mst_config as mc
        INNER JOIN mybillmyright.mst_user as mu
        ON mc.distcode = mu.distcode where  mc.distcode = '$distcode' and mu.mobilenumbe ='$mobilenumber'";

        exit;




        return $this->row( "SELECT mc.configcode,mu.mobilenumber 
        FROM mybillmyright.mst_config as mc
        INNER JOIN mybillmyright.mst_user as mu
        ON mc.distcode = mu.distcode where  mc.distcode = ? and mu.mobilenumbe =?", [$distcode,$mobilenumber]);
    }

    /**
     * get Invoices based Date Range 
     */
    public function getInvoicesbasedDateRange( $startdate , $enddate, $userId,$message = "" ){
       $table_name = $this->table_name;//for mysql
       $condition = "";
       $params = [];
       if(trim( $message) != ""){
            $statusflag = 1;
            $condition .= "WHERE userid= ? and statusflag = ? and billdate >= ? and  billdate <= ?";
            $params = [$userId ,  $statusflag , $startdate,$enddate]; // partial search 1
       }
       else{
          $condition .= "WHERE userid= ? and billdate >= ? and  billdate <= ?";
          $params = [$userId , $startdate,$enddate]; // partial search 1
       }

       // return $this->query( "SELECT * FROM $table_name $condition", $params );
       // bill image url
       $config = new \App\System\Config();
       $bill_uploads_url = $config->get("bill_uploads_url");
       $bill_image_url_query = "(case
       when bd.filepath != '' then concat('{$bill_uploads_url}/', bd.filepath)
       when bd.filepath is null then '-'
       end ) as bill_image_url, ";
        return $this->query( "SELECT bd.*, INITCAP(bd.shopname) as shopname,  {$bill_image_url_query} md.distename as districtname
        FROM $table_name
         as bd
        INNER JOIN mybillmyright.mst_district
         as md
        ON bd.distcode = md.distcode $condition order by bd.billdate desc", $params );
   }
     /**
     * update Bill Table's Status Flag 
     */
    public function updateBillNumberStatusFlag($billId,$acknumber){
          return $this->query( "UPDATE  $this->table_name  SET statusflag = ?,acknumber = ? WHERE billdetailid = ?", [ 'Y',$acknumber,$billId]);
      }

        /**
        * Get Based Config Start Date,End Date Picker
        */
    public function configBasedStartDateEndDate($distcode){

          return $this->row( "SELECT * from mybillmyright.mst_config where distcode = ?", [$distcode]);
      }
      public function getInvoiceCountBasedBillStartandEndDate(){
        return $this->row( "select * from Invoice_count_values()");
    }
    /**
     * Already Mobile Number Exists 
     * 
     * select count(billnumber)  from mybillmyright.billdetail where mobilenumber ='9159698082' and billnumber ='0000005689' and distcode='578'
and billdate = '2023-04-01'
$checkArray = array(
      'billnumber'    => $billnumber,
      'billdate'      => $billdate,
      'distcode'      => $distcode,
      'mobilenumber'  => $mobilenumber,

    )
     */
    public function alreadyBillNumberExists($checkArray){
          $mobilenumber = $checkArray['mobilenumber'];
          $billnumber = $checkArray['billnumber'];
          $distcode = $checkArray['distcode'];
          $billdate = $checkArray['billdate'];

         
      
        return $this->query( "SELECT count($billnumber) FROM $this->table_name WHERE  billnumber = ? and distcode=?
and billdate = ? and mobilenumber = ? ", [ $billnumber, $distcode ,$billdate,$mobilenumber]);
    }

     public function distCodeText($distcode){

          return $this->row( "SELECT distsname from mybillmyright.mst_district where distcode = ?", [$distcode]);
      }
}
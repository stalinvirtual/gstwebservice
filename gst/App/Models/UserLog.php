<?php
namespace App\Models;

use App\Helpers\CommonHelper;

/**
 * User Log model
 */
class UserLog extends BaseModel {
    protected $table_name = 'mst_userlog';
    public function __construct()
    {
        parent::__construct($this->table_name);
    }
}
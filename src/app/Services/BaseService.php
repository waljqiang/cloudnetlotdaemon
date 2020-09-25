<?php
namespace app\Services;

use Server\CoreBase\Model;
use Carbon\Carbon;

class BaseService extends Model{
    public function initialization(&$context){
        parent::initialization($context);
    }
}
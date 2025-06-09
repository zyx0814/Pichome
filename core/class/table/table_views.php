<?php


if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_views extends dzz_table
{
    public function __construct() {

        $this->_table = 'views';
        $this->_pk    = 'id';

        parent::__construct();
    }

    public function insert_data($setarr){
        if($data = DB::fetch_first("select * from %t where idtype = %d  and idval = %s",
            array($this->_table,$setarr['idtype'],$setarr['idval']))){
            $num = intval($data['nums']) +1;
            return parent::update($data['id'],['nums'=>$num]);
        }else{
            $insertdata = [
                'idtype'=>$setarr['idtype'],
                'idval'=>$setarr['idval'],
                'nums'=>1
            ];
            return  parent::insert($insertdata,1);
        }
    }

}

<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_ai_xhchat extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'ai_xhchat';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function insertData($setarr)
    {
        global $_G;
        $uid = $_G['uid'];
        $setarr['dateline'] = time();
        $setarr['uid'] = $uid;
        if($setarr['totaltoken']){
            $tokendatas = [
                'totaltoken'=>$setarr['totaltoken'],
                'uid'=>getglobal('uid'),
                'app'=>'aiXhimageChat',
                'gettype'=>0,
                'dateline'=>TIMESTAMP
            ];
            \Hook::listen('statsTokenuse',$tokendatas);
        }
        return parent::insert($setarr);

    }
    public function fetchContentByIdvalue($idvalue,$idtype)
    {
        global $_G;
        $uid = $_G['uid'];
        $returndata = [];
        foreach(DB::fetch_all("select * from %t where idval = %s and idtype = %d and uid = %d order by id asc",
            [$this->_table,$idvalue,$idtype,$uid]) as $v){
            $returndata[] = $v;
        }
        return $returndata;
    }

    public function deleteContentByIdvalue($idvalue,$idtype){
        global $_G;
        $uid = $_G['uid'];
        return DB::delete($this->_table,"idval='$idvalue' and idtype=$idtype and uid=$uid");
    }
    public function delContentByIdvalueAndNotuid($idvalue,$idtype){
        return DB::delete($this->_table,"idval='$idvalue' and idtype=$idtype");
    }

}
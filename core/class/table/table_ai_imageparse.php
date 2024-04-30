<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_ai_imageparse extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'ai_imageparse';
        $this->_pk = 'id';
        parent::__construct();
    }

    public function insertData($setarr)
    {
        $setarr['dateline'] = TIMESTAMP;
        //如果查询到已经有数据，则不插入
        if ($data = DB::fetch_first("select * from %t where aid = %d and rid=%s and gettype = %d and tplid = %d and aikey = %s",
            [$this->_table, $setarr['aid'], $setarr['rid'], $setarr['gettype'],$setarr['tplid'],$setarr['aikey']])) {
            if($data['isget'] && $data['data']) return ['id'=>$data['id'],'isget'=>$data['isget'],'data'=>$data['data']];
            else return ['id'=>$data['id']];
        } else {
             if($id = parent::insert($setarr,1)){
                 return ['id'=>$id];
             }
        }
    }

    public function deleteByRid($rid){
        return DB::delete($this->_table,"rid='$rid'");
    }
    public function deleteByAid($aid){
        return DB::delete($this->_table,'aid='.$aid);
    }

}
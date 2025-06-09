<?php


if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_keyword_hots extends dzz_table
{
    public function __construct() {

        $this->_table = 'keyword_hots';
        $this->_pk    = 'id';

        parent::__construct();
    }

    public function insert_data($setarr){
        if($data = DB::fetch_first("select * from %t where keyword = %s and idtype = %d and idval = %s",
            array($this->_table,$setarr['keyword'],$setarr['idtype'],$setarr['idval']))){
            $num = intval($data['nums']) +1;
            return parent::update($data['id'],['nums'=>$num]);
        }else{
            $insertdata = [
                'idtype'=>$setarr['idtype'],
                'idval'=>$setarr['idval'],
                'keyword'=>$setarr['keyword'],
                'nums'=>1
            ];
            return  parent::insert($insertdata,1);
        }
    }

    public function fetch_by_appid($appid,$page=1,$perpage=10){
        $hotsdata = [];
        if(!$appid) return $hotsdata;
        $limitsql = 'limit '.($page - 1)*$perpage.','.$perpage;
        foreach(DB::fetch_all("select keyword,nums from %t where idtype = 0 and idval = %s order by nums desc $limitsql ",
            array($this->_table,$appid)) as $v){
            $hotsdata[] = ['keyword'=>$v['keyword'],'num'=>$v['nums']];
        }
        return $hotsdata;
    }
    public function fetch_by_gid($gid,$page=1,$perpage=10){
        $hotsdata = [];
        if(!$gid) return $hotsdata;
        $limitsql = 'limit '.($page - 1)*$perpage.','.$perpage;
        foreach(DB::fetch_all("select keyword,nums from %t where idtype = 1 and idval = %s order by nums desc $limitsql ",
            array($this->_table,$gid)) as $v){
            $hotsdata[] = ['keyword'=>$v['keyword'],'num'=>$v['nums']];
        }
        return $hotsdata;
    }
    public function fetch_file_hots($page=1,$perpage=10){
        $hotsdata = [];
        $limitsql = 'limit '.($page - 1)*$perpage.','.$perpage;
        foreach(DB::fetch_all("select keyword,nums from %t where idtype = 0 or idtype = 3 order by nums desc $limitsql ",
            array($this->_table)) as $v){
            $hotsdata[] = ['keyword'=>$v['keyword'],'num'=>$v['nums']];
        }
        return $hotsdata;
    }
}

<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_pichome_vapp extends dzz_table
    {
        public function __construct()
        {
            
            $this->_table = 'pichome_vapp';
            $this->_pk = 'appid';
            $this->_pre_cache_key = 'pichome_vapp';
            $this->_cache_ttl = 3600;
            parent::__construct();
        }
        private function code62($x) {
            $show = '';
            while($x > 0) {
                $s = $x % 62;
                if ($s > 35) {
                    $s = chr($s+61);
                } elseif ($s > 9 && $s <=35) {
                    $s = chr($s + 55);
                }
                $show .= $s;
                $x = floor($x/62);
            }
            return $show;
        }
        public function getSid($url) {
            $microtime = microtime();
            list($msec, $sec) = explode(' ', $microtime);
            $msec = $msec * 1000000;
            $url = crc32($url.$sec.random(6).$msec);
            $result = sprintf("%u", $url);
            $sid =  self::code62($result);
            $len = strlen($sid);
            if($len < 6){
                $sid .= random(1);
            }
            if(strlen($sid) > 6){
                $sid = substr($sid,0,6);
            }
            if(DB::result_first("select appid from %t where appid = %s",array($this->_table,$sid))){
                $sid = $this->getSid($url);
            }
            return $sid;
        }
        public function insert($setarr){
            //如果为oaooa库时
            $path = $setarr['path'];
            if($appid = DB::result_first("select appid from %t where path = %s and isdelete = 0",array($this->_table,$setarr['path']))){
                parent::update($appid,$setarr);
                return $appid;
            }
            //生成appid
            $setarr['appid'] = $this->getSid($path);

            if(parent::insert($setarr)){
                return $setarr['appid'];
            }
        }
        
        public function fetch_by_path($path){
            return  DB::fetch_first("select * from %t where path = %s",array($this->_table,$path));
        }
        //获取不重复的应用名称
        public function getNoRepeatName($name)
        {
            static $i = 0;
            if (DB::result_first("select COUNT(appid) from %t where appname=%s ", array($this->_table, $name))) {
                $name = preg_replace("/\(\d+\)/i", '', $name) . '(' . ($i + 1) . ')';
                $i += 1;
                return $this->getNoRepeatName($name);
            } else {
                return $name;
            }
        }
     
        //删除虚拟应用
        public function delete_vapp_by_appid($appid){
            $appdata = parent::fetch($appid);
            //删除文件表数据
            C::t('pichome_resources')->delete_by_appid($appid);
            //删除目录表数据
            C::t('pichome_folder')->delete_by_appid($appid);
            //删除目录文件关系表数据
            C::t('pichome_folderresources')->delete_by_appid($appid);
            //删除标签分类表数据
            C::t('pichome_taggroup')->delete_by_appid($appid);
            //删除标签关系表数据
            C::t('pichome_tagrelation')->delete_by_appid($appid);
            //删除最近搜索表数据
            C::t('pichome_searchrecent')->delete_by_appid($appid);
            //resources表数据未完成删除前不允许删除vapp表
            if(DB::result_first("select count(rid) from %t where appid = %s",array('pichome_resources',$appid))){
                return ;
            }else{
                if(is_dir(getglobal('setting/attachdir').'pichomethumb/'.$appid)){
                    removedirectory(getglobal('setting/attachdir').'pichomethumb/'.$appid);
                }
                if($appdata['type'] !== 1){
                    $hookdata = ['appid'=>$appid,'apptype'=>$appdata['type']];
                    Hook::listen('pichomevappdelete',$hookdata);
                }
                return parent::delete($appid);
            }

        }
        
        public function fetch_all_sharedownlod(){
            $downshare = array();
            foreach(DB::fetch_all("select * from %t where 1",array($this->_table)) as $v){
                $downshare[$v['appid']]=$v;
            }
            return $downshare;
        }

        public function add_getinfonum_by_appid($appid,$ceof = 1){
            $appdata = C::t('pichome_vapp')->fetch($appid);
            if($ceof < 0){
                if($appdata['getinfonum'] == 0) return true;
                elseif($appdata['getinfonum'] < abs($ceof))$ceof = -$appdata['getinfonum'];

            }

            if ($ceof > 0) {
                DB::query("update %t set getinfonum=getinfonum+%d where appid = %s", array($this->_table, $ceof, $appid));
            } else {
                DB::query("update %t set getinfonum=getinfonum-%d where appid = %s", array($this->_table, abs($ceof), $appid));
            }
            $this->clear_cache($appid);
        }
    }
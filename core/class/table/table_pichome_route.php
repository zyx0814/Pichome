<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_pichome_route extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_route';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_route';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }
    private function code62($x)
    {
        $show = '';
        while ($x > 0) {
            $s = $x % 62;
            if ($s > 35) {
                $s = chr($s + 61);
            } elseif ($s > 9 && $s <= 35) {
                $s = chr($s + 55);
            }
            $show .= $s;
            $x = floor($x / 62);
        }
        return $show;
    }
    public function feth_url_by_path($path){
       // $path = $this->path_transferred_meaning($path);
        return DB::result_first("select url from %t where path = %s",array($this->_table,$path));
    }
    //转义查询语句当中的path
    public function path_transferred_meaning($path){
        return str_replace(array('\'','(',')','+','^','$','{','}','[',']','#'),array("\'",'\(','\)','\+','\^','\$','\{','\}','\[','\]','\#'),$path);
    }
    //生成自动短链接地址
    public function create_shortpath($url){
        $microtime = microtime();
        list($msec, $sec) = explode(' ', $microtime);
        $msec = $msec * 1000000;
        $url = crc32($url . $sec . random(6) . $msec);
        $result = sprintf("%u", $url);
        $sid = self::code62($result);
        $len = strlen($sid);
        if ($len < 5) {
            $sid .= random(1);
        }
        if (strlen($sid) > 5) {
            $sid = substr($sid, 0, 6);
        }
        if (DB::result_first("select id from %t where path = %s", array($this->_table, $sid))) {
            $sid = $this->create_shortpath($url);
        }
        return $sid;
    }
    public function update_path_by_url($url,$path=''){
        global $_G;
        if(!$path) $path = $this->create_shortpath($url);
        elseif(!preg_match('/^\w{1,30}$/',$path)) return false;
        if(!DB::result_first("select id from %t where path = %s ",array($this->_table,$path))){
            if($id = DB::result_first("select id from %t where url = %s",array($this->_table,$url))){
                parent::update($id,['path'=>$path]);
            }else{
                $setarr = [
                    'path'=>$path,
                    'url'=>$url,
                ];
                parent::insert($setarr);
            }
            $this->update_route();
            return ($_G['setting']['pathinfo']) ? $path:$url;
        }
        return false;

    }
    //删除栏目单页的route规则
    public function delete_by_abid($id,$isbanner = 1,$btype=2){

        $params = [$this->_table];
        if(!$isbanner){
            $wheresql = " url like %s or url like %s ";
            $params[] = $this->path_transferred_meaning('%mod=alonepage&op=view#id='.$id.'%');
            $params[] = $this->path_transferred_meaning('%mod=alonepage&op=view&id='.$id.'#id='.$id.'%');
            $sid = 'a_'.$id;
        }else{
            $wheresql = "  url like %s or url like %s ";
            $params[] =  $this->path_transferred_meaning('%mod=banner&op=index#id='.$id.'%');
            $params[] =  $this->path_transferred_meaning('%mod=banner&op=index&id='.$id.'#id='.$id.'%');
            $sid = ($btype == 4) ? 'tb_'.$id:'b_'.$id;
        }
		$i=0;

        foreach(DB::fetch_all("select id from %t where $wheresql ",$params) as $v){
            if(parent::delete($v['id'])){
                $this->delQRcodeBySid($sid);
				$i++;
			}
        }
        $this->update_route();
		return $i;
    }

    ////删除库的route规则
    public function delete_by_appid($appid){
        $params = [$this->_table,'%'.$appid.'%'];
        $wheresql = " url like %s ";
        foreach(DB::fetch_all("select id from %t where $wheresql ",$params) as $v){
            parent::delete($v['id']);
            $sid = 'vapp_'.$appid;
            $this->delQRcodeBySid($sid);

        }
        $this->update_route();
    }

    public function update_route(){
        $pathinoStatus = isset($_G['setting']['pathinfo']) ? $_G['setting']['pathinfo']:0;
        if(!$pathinoStatus) $pathinoStatus = C::t('setting')->fetch('pathinfo');
        $data = ['pathinfo'=>$pathinoStatus];
        $routefile = CACHE_DIR . BS . 'route' . EXT;
        foreach(DB::fetch_all("SELECT * FROM %t where 1",array($this->_table)) as $value) {
            $data[$value['url']]=$value['path'];
        }
        //写入缓存文件
        @file_put_contents($routefile,"<?php \t\n return ".var_export($data,true).";");
    }
    public function feth_path_by_url($url){
        $path = '';
        $path = DB::result_first("select path from %t where url = %s",array($this->_table,$url));
        if(!$path){
            $url = preg_replace('/&id=(.*)#/', '#', $url);
            $path = DB::result_first("select path from %t where url = %s",array($this->_table,$url));
        }
        return $path;
    }
    public function delQRcodeBySid($sid){
        $sidarr = explode('_',$sid);
        $target='./qrcode/'.$sidarr[0].'/'.$sidarr[1].'.png';
        @unlink(getglobal('setting/attachdir').$target);
    }
    public function getQRcodeBySid($url,$sid){
        $pathinoStatus = isset($_G['setting']['pathinfo']) ? $_G['setting']['pathinfo']:0;
        if(!$pathinoStatus) $pathinoStatus = C::t('setting')->fetch('pathinfo');

        if($pathinoStatus && $path = C::t('pichome_route')->feth_path_by_url($url)){
            $url = $path;
        }
        $url = getglobal('siteurl').$url;
        $sidarr = explode('_',$sid);
        //如果开启了短链接模式
        $target='./qrcode/'.$sidarr[0].'/'.$sidarr[1].'.png';
        $targetpath = dirname(getglobal('setting/attachdir').$target);
        dmkdir($targetpath);
        if(@getimagesize(getglobal('setting/attachdir').$target)){
            return getglobal('setting/attachurl').$target;
        }else{//生成二维码
            QRcode::png($url,getglobal('setting/attachdir').$target,'M',25,2);
            return getglobal('setting/attachurl').$target;
        }
    }
}
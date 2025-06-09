<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_banner extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_banner';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_banner';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }

    public function insert_data($setarr)
    {
        if ($setarr['id']) {
            Hook::listen('lang_parse', $setarr, ['setBannerLangData']);
            if (parent::update($setarr['id'], $setarr)) {
                $this->clearBannerData();
                return $setarr['id'];
            }
        } else {
            if ($id = parent::insert($setarr, 1)) {
                if ($setarr['pid']) $fpathkey = DB::result_first("select pathkey from %t where id = %d", array($this->_table, $setarr['pid']));
                else $fpathkey = '';
                $pathkey = ($fpathkey) ? $fpathkey . '-_' . $id . '_' : '_' . $id . '_';
                parent::update($id, ['pathkey' => $pathkey]);
                $setarr['id'] = $id;
                Hook::listen('lang_parse', $setarr, ['setBannerLangData']);
                $this->clearBannerData();
                return $id;
            }
        }
        return $setarr['id'];
    }

    public function fetch_by_pid($pid = 0)
    {
        $bannerdata = [];
        foreach (DB::fetch_all("select * from %t where pid = %d order by disp", array($this->_table, $pid)) as $v) {
            if ($v['icon']) {
                $v['iconpath'] = getglobal('siteurl') . 'index.php?mod=io&op=getfileStream&path=' . dzzencode('attach::' . $v['icon']);
                $v['filters'] = unserialize($v['filters']);
            }
            $bannerdata[] = $v;
        }
        return $bannerdata;
    }

    //删除栏目
    public function delete_by_id($id)
    {
        if (!$bdata = parent::fetch($id)) return 0;
        $i = 0;
        //删除上级时同时删除所有下级
        $i = 0;
        foreach (DB::fetch_all("select id,bdata,btype from %t where pathkey like %s", [$this->_table, str_replace('_', '\_', $bdata['pathkey']) . '%']) as $v) {
            if (parent::delete($v['id'])) {
                Hook::listen('lang_parse', $v['id'], ['delBannerLangData']);
                $i++;
                if (!DB::result_first("select COUNT(*) from %t where bdata=%s and btype = %d", array($this->_table, $bdata['bdata'], $bdata['btype']))) {
                    C::t('pichome_route')->delete_by_abid($bdata['bdata'], 1, $bdata['btype']);
                }
            }
        }
        $this->clearBannerData();
        return $i;
    }

    public function fetch_bannerbasic_by_bid($bid)
    {
        if (!$bannerdata = parent::fetch($bid)) return false;
        if ($bannerdata['icon']) $bannerdata['iconpath'] = IO::getFileuri('attach::' . $bannerdata['icon']);
        Hook::listen('lang_parse', $bannerdata, ['getBannerLangData']);
        return $bannerdata;
    }

    public function update_disp($id,$oid,$pid,$disptype){
        $orderarr = [];
        foreach(DB::fetch_all("select id from %t where pid = %d  and id != %d order by disp,dateline",array($this->_table,$pid,$id)) as $value){
            $orderarr[] = $value['id'];
        }
        $orderarr = array_flip($orderarr);

        $position = ($oid) ? $orderarr[$oid]:0;

        $position = ($disptype == 'after') ? $position+1:$position;
        foreach($orderarr as $k=>$v) {
            if ($v >= $position) {
                $disp = $v + 1;
            } else {
                $disp = $v;
            }
            parent::update($k, array('disp' => $disp));
        }
        return parent::update($id,array('disp'=>$position));
    }
//移动目录到指定位置
    public function move_to_idandoid($id,$oid,$disptype){
        $fdata = parent::fetch($id);
        if(!$fdata) return false;
        $ofdata = parent::fetch($oid);
        if($disptype == 'inner'){
            $fpathkey = $fdata['pid'] ? DB::result_first("select pathkey from %t where id = %d",[$this->_table,$fdata['pid']]):'' ;
            foreach(DB::fetch_all("select id, pathkey from %t where pathkey like %s",array($this->_table,$fdata['pathkey'].'%')) as $v) {
                if ($v['id'] == $id) {
                    $npathkey = ($ofdata['pathkey']) ? $ofdata['pathkey'] . '-_'.$v['id'].'_':'_'.$v['id'].'_';
                    if (!parent::update($id, ['pid' => $oid])) {
                        break;
                    }
                } else {
                    $npathkey = (!$fpathkey) ? $ofdata['pathkey'].'-'.$v['pathkey']:str_replace($fpathkey,$ofdata['pathkey'],$v['pathkey']);
                }
                parent::update($v['id'], ['pathkey' => $npathkey]);

            }
            self::update_disp($id,'',$ofdata['pid'],$disptype);
        }else{
            if($fdata['pid'] == $ofdata['pid']){
                self::update_disp($id,$oid,$ofdata['pid'],$disptype);
            }else{

                $opathkey = ($ofdata['pid']) ? DB::result_first("select pathkey from %t where id = %d",[$this->_table,$ofdata['pid']]):'' ;
                $fpathkey = $fdata['pid'] ? DB::result_first("select pathkey from %t where id = %d",[$this->_table,$fdata['pid']]):'' ;
                foreach(DB::fetch_all("select id, pathkey from %t where pathkey like %s",array($this->_table,$fdata['pathkey'].'%')) as $v){
                    if($v['id']==$id){
                        $npathkey =($opathkey) ? $opathkey.'-_'.$v['id'].'_':'_'.$v['id'].'_';
                        if(!parent::update($id,['pid'=>$ofdata['pid']])){
                            break;
                        }
                    }else{
                        $npathkey = (!$fpathkey) ? $opathkey.'-'.$v['pathkey']:str_replace($fpathkey,$opathkey,$v['pathkey']);
                    }
                    parent::update($v['id'],['pathkey'=>$npathkey]);
                }
                self::update_disp($id,$oid,$ofdata['pid'],$disptype);

            }
        }
        $this->clearBannerData();
        return true;

    }

    public function getbannerlist($pid = 0, $isshow = 0)
    {
        global $_G;
        if (!isset($_G['pathinfo'])) $pathinfo = C::t('setting')->fetch('pathinfo');
        else $pathinfo = $_G['pathinfo'];
        $params = [$this->_table, $pid];
        $wheresql = ' pid = %d ';
        if ($isshow) {
            $wheresql .= ' and isshow = %d ';
            $params[] = 1;
        }
        $bannerlist = [];
        foreach (DB::fetch_all("select * from %t where $wheresql order by disp asc", $params) as $v) {
            if ($v['icon']) {
                $v['icon'] = getglobal('siteurl') . 'index.php?mod=io&op=getfileStream&path=' . dzzencode('attach::' . $v['icon']);
            } else {
                $v['icon'] = 0;
            }
            $v['soucresname'] = '';
            if ($v['btype'] == 0) {
                $vappdata = C::t('pichome_vapp')->fetch($v['bdata']);
                Hook::listen('lang_parse', $vappdata, ['getVappLangData']);
                $v['soucresname'] = $vappdata['appname'];
            } elseif ($v['btype'] == 1) {
                $v['soucresname'] = DB::result_first("select name from %t where id = %d", ['pichome_smartdata', $v['bdata']]);
            } elseif ($v['btype'] == 2) {
                $pagedata = C::t('pichome_templatepage')->fetch($v['bdata']);
                Hook::listen('lang_parse', $pagedata, ['getTabgrouplangData']);
                $v['soucresname'] = $pagedata['pagename'];
            } elseif ($v['btype'] == 3) {
                $v['soucresname'] = $v['bdata'];
            }
            if ($v['btype'] == 3) {
                $url = $v['bdata'];
            } elseif ($v['btype'] == 4) {
                $tabdata = C::t('#tab#tab_group')->fetch($v['bdata']);
                Hook::listen('lang_parse', $tabdata, ['getTabgrouplangData']);
                $v['soucresname'] = $tabdata['name'];
                $url = 'index.php?mod=banner&op=index&id=tb_' . $v['bdata'] . '#id=tb_' . $v['bdata'];
            } else {
                $url = 'index.php?mod=banner&op=index&id=' . $v['bdata'] . '#id=' . $v['bdata'];
            }
            $v['realurl'] = $url;
            if ($pathinfo) $path = C::t('pichome_route')->feth_path_by_url($url);
            else $path = '';
            if ($path) {
                $v['url'] = $path;
            } else {
                $v['url'] = '';
            }
            if($v['btype'] == 5) $v['url'] =$v['realurl']= '';
            Hook::listen('lang_parse', $v, ['getBannerLangData']);
            $v['children'] = $this->getbannerlist($v['id'], $isshow);
            if (!$v['pid']) {
                if ($v['isbottom']) {
                    $bannerlist['bottom'][] = $v;
                } else {
                    $bannerlist['top'][] = $v;
                }
            } else {
                $bannerlist[] = $v;
            }

        }
        return $bannerlist;
    }
    public function clearBannerData(){
        $cachename = 'BANNERTREELIST';
        C::t('cache')->delete_cachedata_by_cachename($cachename);
    }
    public function getBannerTreeData()
    {
        global $_G;
        if (!isset($_G['pathinfo'])) $pathinfo = C::t('setting')->fetch('pathinfo');
        else $pathinfo = $_G['pathinfo'];
        $cachename = 'BANNERTREELIST';
        $bannerlist = [];
        if ( $cachedata = C::t('cache')->fetch_cachedata_by_cachename($cachename)) {
            $bannerlist = $cachedata;
        } else {
            foreach (DB::fetch_all("select * from %t where isshow = %d order by disp asc", [$this->_table, 1]) as $v) {
                if ($v['icon']) {
                    $v['icon'] = getglobal('siteurl') . 'index.php?mod=io&op=getfileStream&path=' . dzzencode('attach::' . $v['icon']);
                } else {
                    $v['icon'] = 0;
                }
                $v['soucresname'] = '';
                if ($v['btype'] == 0) {
                    $vappdata = C::t('pichome_vapp')->fetch($v['bdata']);
                    Hook::listen('lang_parse', $vappdata, ['getVappLangData']);
                    $v['soucresname'] = $vappdata['appname'];
                } elseif ($v['btype'] == 1) {
                    $v['soucresname'] = DB::result_first("select name from %t where id = %d", ['pichome_smartdata', $v['bdata']]);
                } elseif ($v['btype'] == 2) {
                    $pagedata = C::t('pichome_templatepage')->fetch($v['bdata']);
                    Hook::listen('lang_parse', $pagedata, ['getAlonepageLangData']);
                    $v['soucresname'] = $pagedata['pagename'];
                } elseif ($v['btype'] == 3) {
                    $v['soucresname'] = $v['bdata'];
                }
                if ($v['btype'] == 3) {
                    $url = $v['bdata'];
                } elseif ($v['btype'] == 4) {
                    $url = 'index.php?mod=banner&op=index&id=tb_' . $v['bdata'] . '#id=tb_' . $v['bdata'];
                } else {
                    $url = 'index.php?mod=banner&op=index&id=' . $v['bdata'] . '#id=' . $v['bdata'];
                }
                $v['realurl'] = $url;

                if ($pathinfo) $path = C::t('pichome_route')->feth_path_by_url($url);
                else $path = '';
                if ($path) {
                    $v['url'] = $path;
                } else {
                    $v['url'] = '';
                }
                if($v['btype'] == 5) $v['url'] =$v['realurl']= '';
                Hook::listen('lang_parse', $v, ['getBannerLangData']);
                $bannerlist[$v['id']] = $v;

            }
            $setArr = [
                'cachekey' => $cachename,
                'cachevalue' => serialize($bannerlist),
                'dateline' => time()
            ];
            C::t('cache')->insert_cachedata_by_cachename($setArr);

        }

        return $bannerlist;
    }

    public function buildTree(&$items, $parentId = 0, &$tree = [])
    {
        foreach ($items as $item) {
            if ($item['pid'] == $parentId) {
                $children = [];
                $this->buildTree($items, $item['id'], $children);
                $item['children'] = $children;
                $tree[] = $item;
            }
        }
        return $tree;
    }

    public function getBannerTree($id=''){
        $result = [];
        $bannerlist = $this->getBannerTreeData();

        $bannerdata = $this->buildTree($bannerlist);
        foreach ($bannerdata as $key=>$value){
            if($value['pid']){
                unset($bannerdata[$key]);
                continue;
            }
            if($value['isbottom']){
                $result['bottom'][] = $value;
            }else{
                $result['top'][] = $value;
            }
        }
        $topbanners = $showchildrenbanners= [];
        foreach ($bannerlist as $key=>$item) {
            if($item['isbottom']) continue;
            if($item['btype'] != 3 && !$item['pid']){
                // if($item['showchildren'])
                $showchildrenbanners[$key]  = $item;
                $topbanners[$key] = $item;
            }
        }
        $topbannerids = array_keys($topbanners);
        $showchildrenbannerids = array_keys($showchildrenbanners);

        $bannerl = [];

        if($id){
            $btype = '';

            if(strpos($id,'tb_') === 0){
                $id = intval(str_replace('tb_','',$id));
                $btype = 4;
            }
            $pathkey = '';
            $bid = 0;
            $pid = 0;

            foreach($bannerlist as $k=>$v){
                if($v['isbottom']) continue;
                if($btype){
                    if($v['btype'] == $btype && $v['bdata'] == $id){
                        $pathkey = $v['pathkey'];
                        $bid = $v['id'];
                        $pid = $v['pid'];
                        break;
                    }else{
                        continue;
                    }

                }elseif($v['bdata'] == $id){
                    if($v['btype'] == 0){
                        $appdata = C::t('pichome_vapp')->fetch($id);
                        $pagesetting = unserialize($appdata['pagesetting']);
                        if($pagesetting['aside'] || !$pagesetting['filterstyle'] ) continue;
                    }
                    $pathkey = $v['pathkey'];
                    $bid = $v['id'];
                    $pid = $v['pid'];
                    break;
                }
            }


            if($pathkey){

                $pathkey = str_replace('_','',$pathkey);
                $pathkeyarr = explode('-',$pathkey);
                $topid = $pathkeyarr[0];

                if(in_array($topid,$showchildrenbannerids)){
                    if(!$pid){
                        foreach($bannerlist as $k=>$v){
                            if($v['pid'] == $bid){
                                if($v['id'] == $bid) $v['selected'] = true;
                                else $v['selected'] = false;
                                $bannerl[$bid][] = $v;
                            }
                        }
                    }else{
                        foreach($pathkeyarr as $cbid){
                            foreach($bannerlist as $k=>$v){
                                if($v['pid'] == $cbid){
                                    if($v['id'] == $bid) $v['selected'] = true;
                                    else $v['selected'] = false;
                                    $bannerl[$cbid][] = $v;
                                }
                            }
                        }
                    }

                }
            }

        }
        else{
            $pid = $topbannerids[0];
            if(in_array($pid,$showchildrenbannerids)){
                if($bannerlist[$pid]['btype'] == 0){
                    $appdata = C::t('pichome_vapp')->fetch($bannerlist[$pid]['bdata']);
                    $pagesetting = unserialize($appdata['pagesetting']);
                    if( $pagesetting['aside'] || !$pagesetting['filterstyle']){
                        $bannerl = [];
                    }else{
                        foreach($bannerlist as $k=>$v){
                            if($v['pid'] == $pid){
                                $bannerl[$pid][] = $v;
                            }
                        }
                    }
                }else{
                    foreach($bannerlist as $k=>$v){
                        if($v['pid'] == $pid){
                            $bannerl[$pid][] = $v;
                        }
                    }
                }

            }

        }
        return ['bannerlist'=>$result,'tilebanner'=>$bannerl];
    }
}
<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_pichome_resources extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_resources';
        $this->_pk = 'rid';
        $this->_pre_cache_key = 'pichome_resources';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

    public function insert($setarr)
    {
        if (DB::result_first("select count(rid) from %t where rid = %s", array($this->_table, $setarr['rid']))) {
            $rid = $setarr['rid'];
            unset($setarr['rid']);
            parent::update($rid, $setarr);
        } else {
            parent::insert($setarr);
        }
        return true;
    }

    public function delete_by_appid($appid)
    {
        $data = C::t('pichome_vapp')->fetch($appid);
        //$i = 0;
        $rids = [];
        foreach (DB::fetch_all("select rid from %t where appid = %s limit 0,100", array($this->_table, $appid)) as $v) {
            $rids[] = $v['rid'];
        }
        if ($rids) $this->delete_by_rid($rids, $data['deluid'], $data['delusername']);
        //return $i;
    }

    public function fetch_by_path($path)
    {
        $path = trim($path);
        return DB::result_first("select * from %t  where  path = %s", array($this->_table, $path));
    }

    public function delete_by_rid($rids, $uid = 0, $username = '')
    {
        if (!is_array($rids)) $rids = (array)$rids;
        C::t('pichome_resources_attr')->delete_by_rid($rids);
        C::t('pichome_folderresources')->delete_by_rid($rids);
        C::t('pichome_palette')->delete_by_rid($rids);
        C::t('pichome_comments')->delete_by_rid($rids);
        C::t('pichome_resourcestag')->delete_by_rid($rids);
        C::t('pichome_share')->delete_by_rid($rids);
        C::t('pichome_ffmpeg_record')->delete($rids);
        C::t('pichome_imagickrecord')->delete($rids);
        $deldata = ['rids' => $rids, 'deluid' => $uid, 'delusername' => $username];
        Hook::listen('pichomedatadeleteafter', $deldata);

        return $this->delete($rids);
    }


    public function fetch_by_rids($rids)
    {
        global $Opentype;
        if (!is_array($rids)) $rids = (array)$rids;

        $datas = $tmpdatas = [];
        foreach (parent::fetch_all($rids) as $v) {
            $v['fsize'] = formatsize($v['size']);
            $v['mtime'] = dgmdate(round($v['mtime'] / 1000), 'Y/m/d H:i');
            $v['dateline'] = dgmdate(round($v['dateline'] / 1000), 'Y/m/d H:i');
            $v['name'] = str_replace(strrchr($v['name'], "."), "", $v['name']);
            $v['btime'] = dgmdate(round($v['btime'] / 1000), 'Y/m/d H:i');
            $v['dpath'] = dzzencode($v['rid'], '', 0, 0);
            if (in_array($v['ext'], $Opentype['video'])) {
                $v['opentype'] = 'video';
            } elseif (in_array($v['ext'], $Opentype['text'])) {
                $v['opentype'] = 'text';
            } elseif (in_array($v['ext'], $Opentype['pdf'])) {
                $v['opentype'] = 'pdf';
            } elseif (in_array($v['ext'], $Opentype['image'])) {
                $v['opentype'] = 'image';
            } else {
                $v['opentype'] = 'other';
            }
            $tmpdatas[$v['rid']] = $v;
        }
        foreach ($rids as $rid) {
            $datas[$rid] = $tmpdatas[$rid];
        }
        foreach (C::t('pichome_resources_attr')->fetch_all($rids) as $v) {
            $datas[$v['rid']]['path'] = $v['path'];
            //$datas[$v['rid']]['hasthumb'] = $v['hasthumb'];
            $colorsarr = [];
            //获取颜色数据
            $colordata = C::t('pichome_palette')->fetch_colordata_by_rid($v['rid']);
            foreach ($colordata as $cv) {
                $colorsarr[] = $cv;
            }
            $datas[$v['rid']]['color'] = $colorsarr[0];
            $datas[$v['rid']]['link'] = $v['link'];
        }
        //array_multisort($datas, 'rid', SORT_ASC, $rids);
        foreach (C::t('pichome_resourcestag')->fetch_all_tag_by_rids($rids) as $k => $v) {
            $datas[$k]['tags'] = $v;
            // $datas[$k]['tags'] = '•'.implode('•',$v);
        }

        return $datas;
    }

    //获取数据后端使用
    public function fetch_data_by_rid($rid)
    {

        if (!$resourcesdata = parent::fetch($rid)) return array();
        if ($resourcesdata['isdelete'] > 0) return array();
        $downshare = C::t('pichome_vapp')->fetch_all_sharedownlod();
        if ($downshare[$resourcesdata['appid']]['isdelete']) return array();
        $attrdata = C::t('pichome_resources_attr')->fetch($rid);
        $resourcesdata = array_merge($resourcesdata, $attrdata);
        $resourcesdata['realpath'] = $downshare[$resourcesdata['appid']]['path'] . BS . $resourcesdata['path'];
        return $resourcesdata;
    }

    public function fetch_by_rid($rid)
    {
        global $Opentype;
        if (!$resourcesdata = parent::fetch($rid)) return array();
        if ($resourcesdata['isdelete'] > 0) return array();
        //获取所有库分享和下载权限
        $downshare = C::t('pichome_vapp')->fetch_all_sharedownlod();
        $attrdata = C::t('pichome_resources_attr')->fetch($rid);
        if ($attrdata['desc']) $attrdata['desc'] = strip_tags($attrdata['desc']);
        $resourcesdata = array_merge($resourcesdata, $attrdata);
        $resourcesdata['colors'] = C::t('pichome_palette')->fetch_colordata_by_rid($rid);
        $resourcesdata['ext'] = strtolower($resourcesdata['ext']);
        if (in_array($resourcesdata['ext'], $Opentype['video'])) {
            $resourcesdata['opentype'] = 'video';
        } elseif (in_array($resourcesdata['ext'], $Opentype['text'])) {
            $resourcesdata['opentype'] = 'text';
        } elseif (in_array($resourcesdata['ext'], $Opentype['pdf'])) {
            $resourcesdata['opentype'] = 'pdf';
        } elseif (in_array($resourcesdata['ext'], $Opentype['image'])) {
            $resourcesdata['opentype'] = 'image';
        } else {
            $resourcesdata['opentype'] = 'other';
        }
        $resourcesdata['icondata'] = getglobal('siteurl') . 'index.php?mod=io&op=getImg&hash='.VERHASH.'&path=' . dzzencode($rid, '', 7200, 0);

        $thumbwidth = getglobal('config/pichomethumbwidth') ? getglobal('config/pichomethumbwidth') : 900;
        $thumbheight = getglobal('config/pichomethumbheight') ? getglobal('config/pichomethumbheight') : 900;
        $thumsizearr = $this->getImageThumbsize($resourcesdata['width'], $resourcesdata['height'], $thumbwidth, $thumbheight);
        $resourcesdata['iconwidth'] = $thumsizearr[0];
        $resourcesdata['iconheight'] = $thumsizearr[1];
        //$resourcesdata['icondata'] = str_replace('+', '%20', $resourcesdata['icondata']);
        if(getglobal('adminid') == 1) $resourcesdata['realfianllypath'] = getglobal('siteurl') . 'index.php?mod=io&op=getImg'.'&path=' . dzzencode($rid.'_3', '', 0, 0);

        $resourcesdata['share'] = $downshare[$resourcesdata['appid']]['share'];
        $resourcesdata['download'] = $downshare[$resourcesdata['appid']]['download'];

        $originalimg = $downshare[$resourcesdata['appid']]['path'] . BS . $resourcesdata['path'];
        $resourcesdata['originalimg'] = ($resourcesdata['download']) ? getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . dzzencode($resourcesdata['rid'].'_1','', 0, 0):'';
        /*if ($resourcesdata['opentype'] == 'video') {
            $resourcesdata['realpath'] = str_replace('+', '', urlencode(getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . dzzencode($resourcesdata['rid'].'_3','', 7200, 0)));
        }*//* else {
            $resourcesdata['realpath'] = getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . dzzencode($resourcesdata['rid'].'_realpath', '', 0, 0);
        }*/


        //}

        $resourcesdata['name'] = str_replace(strrchr($resourcesdata['name'], "."), "", $resourcesdata['name']);
        $resourcesdata['fsize'] = formatsize($resourcesdata['size']);
        $resourcesdata['mtime'] = dgmdate(round($resourcesdata['mtime'] / 1000), 'Y/m/d H:i');
        $resourcesdata['dateline'] = dgmdate(round($resourcesdata['dateline'] / 1000), 'Y/m/d H:i');
        $resourcesdata['btime'] = dgmdate(round($resourcesdata['btime'] / 1000), 'Y/m/d H:i');
        unset($resourcesdata['path']);
        $resourcesdata['foldernames'] = C::t('pichome_folderresources')->get_foldername_by_rid($rid);
        $resourcesdata['tag'] = C::t('pichome_resourcestag')->fetch_tag_by_rid($rid);
        $resourcesdata['dpath'] = dzzencode($rid, '', 0, 0);


        return $resourcesdata;
    }

    public function getdatasbyrids($rids)
    {
        $returndata = [];
        //文件数据
        $resourcesdata = $this->fetch_by_rids($rids);
        //获取所有库分享和下载权限,以及编码数据
        $downshare = C::t('pichome_vapp')->fetch_all_sharedownlod();
        //文件附属表数据
        //  $attrdata = C::t('pichome_resources_attr')->fetch_by_rids($rids);
        //文件标注数
        $annonationnumdata = C::t('pichome_comments')->fetch_annonationnum_by_rids($rids);
        foreach ($resourcesdata as $v) {
            //echo $v['hasthumb'];die;
            $v['annonationnum'] = $annonationnumdata[$v['rid']]['num'];
            $v['share'] = $downshare[$v['appid']]['share'];
            $v['download'] = $downshare[$v['appid']]['download'];

            $v['icondata'] = getglobal('siteurl') . 'index.php?mod=io&op=getImg&hash='.VERHASH.'&path=' . dzzencode($v['rid'],'', 7200, 0);
            $thumbwidth = getglobal('config/pichomethumbwidth') ? getglobal('config/pichomethumbwidth') : 900;
            $thumbheight = getglobal('config/pichomethumbheight') ? getglobal('config/pichomethumbheight') : 900;
            $thumsizearr = $this->getImageThumbsize($v['width'], $v['height'], $thumbwidth, $thumbheight);
            $v['thumbwidth'] = $thumsizearr[0];
            $v['thumbheight'] = $thumsizearr[1];
            if ($v['opentype'] == 'video') {
                $v['realpath'] = getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . dzzencode($v['rid'].'_3','', 7200, 0);
            }



            unset($v['path']);
            $returndata[] = $v;
        }
        return $returndata;
    }

    public function getImageThumbsize($owidth, $oheight, $width, $height)
    {
        if ($owidth > $width || $oheight > $height) {
            $or = $owidth / $oheight;
            $r = $width / $height;
            if ($r > $or) {
                if ($oheight < $height) {
                    $height = $oheight;
                    $width = $owidth;
                } else {

                    $width = ceil($height * $or);
                    if ($width < 242) {
                        $width = 242;
                        $height = ceil($width / $or);
                    }
                }

            } else {
                if ($owidth < $width) {
                    $height = $oheight;
                    $width = $owidth;
                } else {
                    $height = ceil($width / $or);
                    $width = ceil($height * $or);
                    if ($width < 242) {
                        $width = 242;
                        $height = ceil($width / $or);
                    }
                }
            }

        } else {
            $width = $owidth;
            $height = $oheight;
        }
        //Return the results
        return array($width, $height);

    }

    public function geticondata_by_rid($rid,$time=7200)
    {
        $resourcesdata = DB::fetch_first("select r.rid,r.appid,r.hasthumb,r.ext,r.type,ra.path as fpath,
            v.path,r.apptype,v.iswebsitefile,v.version from %t r 
        left join %t ra on r.rid=ra.rid left join %t v on r.appid = v.appid where r.rid = %s and r.isdelete = 0",
            array($this->_table, 'pichome_resources_attr', 'pichome_vapp', $rid));
        $resourcesdata['icondata'] = getglobal('siteurl') . 'index.php?mod=io&op=getImg&hash='.VERHASH.'&path=' . dzzencode($rid, '', $time, 0);
        return $resourcesdata;
    }

    public function fetch_like_words($keyword, $limit = 10)
    {
        $likewords = [];
        $presql = " case when name like %s then 3 when name like %s then 2 when name like %s then 1 end as rn";
        $wheresql = " name like %s";
        $params = [$keyword . '%', '%' . $keyword, '%' . $keyword . '%', $this->_table, '%' . $keyword . '%'];
        foreach (DB::fetch_all("select name,$presql from %t where $wheresql order by rn desc  limit 0,$limit", $params) as $v) {
            $likewords[] = $v['name'];
        }
        return $likewords;
    }

}
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
            // parent::update($rid, $setarr);
            $this->update_by_rids($setarr['appid'], $rid, $setarr);
            return $rid;
        } else {
            if (parent::insert($setarr)) {
                // $hookindex = ['rids'=>$setarr['rid'],'appid'=>$setarr['appid']];
                // Hook::listen('updatedataafter',$hookindex);
            }
            return $setarr['rid'];
        }
    }

    public function insert_data($setarr)
    {
        if (!isset($setarr['rid'])) $setarr['rid'] = $this->createRid($setarr['appid']);
        return $this->insert($setarr);
    }

    public function createRid($appid)
    {

        $ridmd = strtoupper(dechex(date('m'))) . date('d');

        $ridms = substr(time(), -5) . substr(microtime(), 2, 5);
        $rid = md5($ridmd . $ridms . sprintf('%02d', rand(0, 99)) . $appid);
        if (DB::result_first("select count(rid) from %t where rid = %s", array($this->_table, $rid))) {
            $rid = $this->createRid($appid);
        }
        return $rid;
    }

    //更改文件权限
    public function update_perm_by_appid_fid($appid, $perm, $fid = '', $isall = 0)
    {
        if (!$fid) {
            if ($isall) {
                DB::update($this->_table, ['level' => $perm], ['appid' => $appid]);
            } else {
                DB::query("update %t r left join %t fr on r.rid = fr.rid  set level = %d where r.appid = %s isnull(fr.fid) ",
                    array($this->_table, 'pichome_folderresources', $perm, $appid));
                //DB::update($this->_table,['level'=>$perm]," appid = '$appid'  and isnull(fids)");
            }
        } else {
            DB::query("update %t r left join %t fr on r.rid = fr.rid  set level = %d where r.appid = %s and fr.fid=%s ",
                array($this->_table, 'pichome_folderresources', $perm, $appid, $fid));
            /*DB::update($this->_table,['level'=>$perm]," appid = '$appid'  and find_in_set('$fid',fids)");*/

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

    public function update_by_rids($appid, $rids, $attr)
    {
        if (!is_array($rids)) $rids = (array)$rids;
        if (parent::update($rids, $attr)) {
            $hookindex = ['rids' => $rids, 'appid' => $appid];
            Hook::listen('updatedataafter', $hookindex);
        }
    }

    //清空删除文件
    public function emptydel_data($appid)
    {
        // $rids = [];
        foreach (DB::fetch_all("select rid from %t where isdelete = 2 and appid = %s", array($this->_table, $appid)) as $v) {
            $this->delete_by_rid($v['rid']);
        }
        return true;
    }

    //清空回收站
    public function empty_recycle_data($appid)
    {
        if (!$appid) return true;
        DB::update($this->_table, ['isdelete' => 2], "isdelete = 1  and appid = '$appid' ");
        return true;
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
        C::t('thumb_record')->delete_by_rid($rids);
        //C::t('video_record')->delete_by_rid($rids);
        //C::t('pichome_view')->delete_by_rid($rids);
        C::t('pichome_resourcestab')->delete_by_rid($rids);
        C::t('ffmpegimage_cache')->delete_by_path($rids);
        //移除文件夹封面数据
        C::t('pichome_folder')->remove_foldercver_by_rids($rids);

        $deldata = ['rids' => $rids, 'deluid' => $uid, 'delusername' => $username];
        Hook::listen('pichomedatadeleteafter', $deldata);

        return $this->delete($rids);
    }

    //删除文件到回收站
    public function recycle_data_by_rids($rids)
    {
        foreach (DB::fetch_all("select fids,rid from %t where rid in(%n) ", array($this->_table, $rids)) as $v) {
            if (parent::update($v['rid'], array('isdelete' => 1))) {
                $fids = $v['fids'];
                $fidarr = explode(',', $fids);
                C::t('pichome_folder')->add_filenum_by_fid($fidarr, -1);
            }
        }
        return true;

    }

    //从回收站恢复文件
    public function recover_file_by_rids($rids)
    {
        foreach (DB::fetch_all("select fids,rid from %t where rid in(%n) ", array($this->_table, $rids)) as $v) {
            if (parent::update($v['rid'], array('isdelete' => 0))) {
                $fids = $v['fids'];
                $fidarr = explode(',', $fids);
                C::t('pichome_folder')->add_filenum_by_fid($fidarr, 1);
            }
        }
        return true;
    }

    public function fetch_by_rids($rids)
    {

        if (!is_array($rids)) $rids = (array)$rids;

        $datas = $tmpdatas = [];
        foreach (parent::fetch_all($rids) as $v) {
            $v['fsize'] = formatsize($v['size']);
            $v['mtime'] = dgmdate(round($v['mtime'] / 1000), 'Y/m/d H:i');
            $v['dateline'] = dgmdate(round($v['dateline'] / 1000), 'Y/m/d H:i');
            $v['name'] = str_replace(strrchr($v['name'], "."), "", $v['name']);
            $v['btime'] = dgmdate(round($v['btime'] / 1000), 'Y/m/d H:i');
            $v['dpath'] = dzzencode($v['rid'], '', 0, 0);
            $v['opentype'] = getTypeByExt($v['ext']);
            if ($v['opentype'] == 'audio' || $v['opentype'] == 'video') {
                if(in_array($v['ext'],explode(',',getglobal('config/pichomeplayermediaext')))){
                    $v['mediaplayerpath'] = getglobal('siteurl') . 'index.php?mod=io&op=getStream&hash=' . VERHASH . '&path=' . dzzencode($v['rid'] . '_3', '', 14400, 0);
                }else{
                    if($ppath = DB::result_first("select path from %t where rid = %s and status = %d",array('video_record',$v['rid'],2))){
                        $v['mediaplayerpath'] = IO::getFileUri($ppath);
                    }else{
                        $v['mediaplayerpath'] = false;
                    }
                }

            }
            $tmpdatas[$v['rid']] = $v;
        }
        foreach ($rids as $rid) {
            $datas[$rid] = $tmpdatas[$rid];
        }
        // $time = microtime(true);
        $tagids = [];
        foreach (C::t('pichome_resources_attr')->fetch_all($rids) as $v) {

            $datas[$v['rid']]['path'] = $v['path'];
            $datas[$v['rid']]['duration'] = $v['duration'];
            //$datas[$v['rid']]['hasthumb'] = $v['hasthumb'];
            $datas[$v['rid']]['link'] = $v['link'];

            if ($v['tag']) {
                $tmptags = explode(',', $v['tag']);
                $datas[$v['rid']]['tag'] = $tmptags;
                $tagids = array_merge($tagids, $tmptags);
            }
        }

        /* $time1 = microtime(true);
         $wtime = $time1 - $time;
         runlog('aaaaaselect','获取属性耗时'.$wtime);*/
        //获取所有标签
        $tagids = array_unique($tagids);
        $tagdata = [];
        foreach (DB::fetch_all("select tagname,tid from %t where tid in(%n)", array('pichome_tag', $tagids)) as $tag) {
            $tagdata[$tag['tid']] = $tag['tagname'];
        }
        foreach ($datas as $k => $v) {

            if ($v['tag']) {
                $tmptagname = [];
                foreach ($v['tag'] as $tid) {
                    $tmptagname[] = $tagdata[$tid];
                }
                $datas[$k]['tags'] = $tmptagname;
            }
        }
        return $datas;
    }

    //获取数据后端使用
    public function fetch_data_by_rid($rid)
    {

        if (!$resourcesdata = parent::fetch($rid)) return array();
        //if ($resourcesdata['isdelete'] > 0) return array();
        $downshare = C::t('pichome_vapp')->fetch_all_sharedownlod();
        if ($downshare[$resourcesdata['appid']]['isdelete']) return array();
        $attrdata = C::t('pichome_resources_attr')->fetch($rid);
        $resourcesdata = array_merge($resourcesdata, $attrdata);
        if (is_numeric($resourcesdata['path'])) {
            $attachment = C::t('attachment')->fetch(intval($resourcesdata['path']));
            $bz = io_remote::getBzByRemoteid($attachment['remote']);
            $resourcesdata['bz'] = $bz;
            $resourcesdata['remoteid'] = $attachment['remote'];
            $resourcesdata['path'] = $bz . $attachment['attachment'];
            $resourcesdata = array_merge($resourcesdata, $attachment);
        } else {
            if (strpos($downshare[$resourcesdata['appid']]['path'], ':') === false) {
                $resourcesdata['bz'] = 'dzz::';
            } else {
                $patharr = explode(':', $downshare[$resourcesdata['appid']]['path']);
                $resourcesdata['bz'] = ($patharr[1]) ? $patharr[0] . ':' . $patharr[1] . ':' : 'dzz::';
                $resourcesdata['remoteid'] = $patharr[1];
            }
            $resourcesdata['path'] = $downshare[$resourcesdata['appid']]['path'] . BS . $resourcesdata['path'];
        }
        $resourcesdata['vapptype'] = $downshare[$resourcesdata['appid']]['type'];
        return $resourcesdata;
    }

    //获取对比数据
    public function fetch_comparedata_by_rid($rid)
    {
        if (!$resourcesdata = parent::fetch($rid)) return array();
        if ($resourcesdata['isdelete'] > 0) return array();
        $downshare = C::t('pichome_vapp')->fetch_all_sharedownlod();
        if ($downshare[$resourcesdata['appid']]['isdelete']) return array();
        $attrdata = C::t('pichome_resources_attr')->fetch($rid);
        $resourcesdata = array_merge($resourcesdata, $attrdata);
        //获取颜色数据
        $resourcesdata['palettes'] = C::t('pichome_palette')->fetch_colorp_by_rid($rid);
        return $resourcesdata;
    }

    public function getOpensrc($ext, $bz)
    {
        $openexts = C::t('app_open')->fetch_all_ext();
        $bzarr = explode(':', $bz);
        $bzpre = $bzarr[0];
        $openlist = [];
        $bzext = $bzpre . '::' . $ext;
        foreach ($openexts as $v) {
            if ($bzext == $v['ext'] || $ext == $v['ext']) {
                if ($v['isdefault']) {
                    $src = getglobal('siteurl') . $v['url'];
                    break;
                } else {
                    $openlist[] = $v;
                }
            }
        }
        if (!$src) {
            if (isset($openlist[0])) {
                $src = getglobal('siteurl') . $openlist[0]['url'];
            }
        }
        return $src;
    }

    public function getThumbsByrids($rids)
    {
        if (!is_array($rids)) $rids = (array)$rids;
        $return = [];
        foreach (DB::fetch_all("select * from %t where rid in(%n)", array('thumb_record', $rids)) as $v) {
            if ($v['rid']) $return[$v['rid']]['imgstatus'] = 1;
            if ($v['sstatus']) $return[$v['rid']]['icondata'] = getglobal('siteurl') . IO::getFileuri($v['spath']);
            else $return[$v['rid']]['icondata'] = false;
            if ($v['lstatus']) $return[$v['rid']]['originalimg'] = getglobal('siteurl') . IO::getFileuri($v['lpath']);
            else $return[$v['rid']]['originalimg'] = false;
        }
        return $return;
    }

    //获取文件图片地址
    public function getfileimageurl($resourcesdata, $apppath, $apptype, $download,$return = 0)
    {

        $imgdata = [];
        $patharr = explode(':', $apppath);
        $did = is_numeric($patharr[1]) ? $patharr[1] : 1;
        //库路径
        $thumbdir = $apppath;
        if(strpos($resourcesdata['path'],$thumbdir)!==false)$resourcesdata['path'] = str_replace($thumbdir.BS,'',$resourcesdata['path']);
        $ext = $resourcesdata['ext'];
        //获取缩略图地址模式，默认0由服务器自动根据文件位置生成地址
        $thumurlmod = getglobal('config/thumburlmod') ?  getglobal('config/thumburlmod'):0;
        //如果不是云存储文件或者强制服务器中转
        if ($did == 1 || $thumurlmod) {
            //小图参数
            $smallthumbparams = ['rid' => $resourcesdata['rid'], 'hash' => VERHASH, 'download' => $download,
                'thumbsign' => '0', 'ext' => $resourcesdata['ext'], 'appid' => $resourcesdata['appid'],'hasthumb'=>$resourcesdata['hasthumb']];
            //大图参数
            $largethumbparams = ['rid' => $resourcesdata['rid'], 'hash' => VERHASH, 'download' => $download,
                'thumbsign' => '1', 'ext' => $resourcesdata['ext'], 'appid' => $resourcesdata['appid'],'hasthumb'=>$resourcesdata['hasthumb']];
            if ($apptype == 3 || $apptype == 1) {
                $thumbdata = C::t('thumb_record')->fetch($resourcesdata['rid']);
                if ($thumbdata['sstatus']) $imgdata['icondata'] = getglobal('siteurl') . IO::getFileuri($thumbdata['spath']);
                else $imgdata['icondata'] =  false;
                if ($thumbdata['lstatus']) $imgdata['originalimg'] = getglobal('siteurl') . IO::getFileuri($thumbdata['lpath']);
                else {
                    $imgdata['originalimg'] =  (!$return) ? false: getglobal('siteurl') . 'index.php?mod=io&op=createThumb&path='.$resourcesdata['dpath'].'&size=large';
                }
            } else {

                $imgdata['icondata'] = getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . Pencode($smallthumbparams, 0, '') . '&' . VERHASH;
                $imgdata['originalimg'] = getglobal('siteurl') . 'index.php?mod=io&op=getImg&path=' . Pencode($largethumbparams, 0, '') . '&' . VERHASH;
            }
        }
        else {
            switch ($apptype) {
                case 0:

                    //小图地址
                    if ($resourcesdata['hasthumb']) {
                        $tmppath = str_replace(strrchr($resourcesdata['path'], "."), "", $resourcesdata['path']);

                        $imgdata['icondata'] = IO::getFileUri($thumbdir . BS . $tmppath . '_thumbnail.png');
                    } else {
                        //如果没有缩略图并且图片可直接预览使用大图地址作为小图
                        if (in_array($ext, explode(',', getglobal('config/pichomecommimageext')))) {
                            $imgdata['icondata'] = IO::getFileUri($thumbdir . BS . $resourcesdata['path']);
                        } else {
                            $imgdata['icondata'] = 'static/dzzthumb/preview/b.gif';
                        }
                    }

                    //大图地址
                    if (!$resourcesdata['hasthumb'] && in_array($ext, explode(',', getglobal('config/pichomecommimageext')))) {
                        $imgdata['originalimg'] = IO::getFileUri($thumbdir . BS . $resourcesdata['path']);
                    } else {
                        $imgdata['originalimg'] = $imgdata['icondata'];
                    }
                    break;
                case 2:
                    //特殊格式文件后缀
                    $pichomespecialimgext = 'aai,art,arw,avs,bpg,bmp,bmp2,bmp3,brf,cals,cals,cgm,cin,cip,cmyk,cmyka,cr2,crw,cube,cur,cut,dcm,dcr,dcx,dds,dib,djvu,dng,dot,dpx,emf,epdf,epi,eps,eps2,eps3,epsf,epsi,ept,exr,fax,fig,fits,fpx,gplt,gray,graya,hdr,heic,hpgl,hrz,ico,info,isobrl,isobrl6,jbig,jng,jp2,jpt,j2c,j2k,jxr,json,man,mat,miff,mono,mng,m2v,mpc,mpr,mrwmmsl,mtv,mvg,nef,orf,otb,p7,palm,pam,clipboard,pbm,pcd,pcds,pcl,pcx,pdb,pef,pes,pfa,pfb,pfm,pgm,picon,pict,pix,png8,png00,png24,png32,png48,png64,pnm,ppm,ps,ps2,ps3,psb,psd,ptif,pwp,rad,raf,rgb,rgb565,rgba,rgf,rla,rle,sfw,sgi,shtml,sid,mrsid,sum,text,tga,tif,tiff,tim,ttf,ubrl,ubrl6,uil,uyvy,vicar,viff,wbmp,wpg,wmf,wpg,x,xbm,xcf,xpm,xwd,x3f,YCbCr,YCbCrA,yuv,sr2,srf,srw,rw2,nrw,mrw,kdc,erf,canvas,caption,clip,clipboard,fractal,gradient,hald,histogram,inline,map,mask,matte,null,pango,plasma,preview,print,scan,radial_gradient,scanx,screenshot,stegano,tile,unique,vid,win,xc,granite,logo,netscpe,rose,wizard,bricks,checkerboard,circles,crosshatch,crosshatch30,crosshatch45,fishscales,gray0,gray5,gray10,gray15,gray20,gray25,gray30,gray35,gray40,gray45,gray50,gray55,gray60,gray65,gray70,gray75,gray80,gray85,gray90,gray95,gray100,hexagons,horizontal,horizontal2,horizontal3,horizontalsaw,hs_bdiagonal,hs_cross,hs_diagcross,hs_fdiagonal,hs_vertical,left30,left45,leftshingle,octagons,right30,right45,rightshingle,smallfishcales,vertical,vertical2,vertical3,verticalfishingle,vericalrightshingle,verticalleftshingle,verticalsaw,fff,3fr,ai,iiq,cdr';
                    $pichomespecialimgextarr = explode(',', $pichomespecialimgext);
                    //获取记录表缩略图对应信息
                    $thumbdata = DB::fetch_first("select thumb,bid from %t where appid = %s and rid = %s", array('billfish_record', $patharr['appid'], $patharr['rid']));
                    $bid = $thumbdata['bid'];
                    $thumbpath = dechex($bid);
                    $thumbpath = (string)$thumbpath;
                    if (strlen($thumbpath) < 2) {
                        $thumbpath = str_pad($thumbpath, 2, 0, STR_PAD_LEFT);
                    } elseif (strlen($thumbpath) > 2) {
                        $thumbpath = substr($thumbpath, -2);
                    }
                    //小图地址
                    if ($resourcesdata['hasthumb']) {
                        $imgdata['icondata'] = IO::getFileUri($thumbdir . '/.bf/.preview/' . $thumbpath . '/' . $bid . '.small.webp');
                    } else {
                        //如果没有缩略图并且图片可直接预览使用大图地址作为小图
                        if (in_array($ext, explode(',', getglobal('config/pichomecommimageext')))) {
                            $imgdata['icondata'] = IO::getFileUri($thumbdir . BS . $resourcesdata['path']);
                        } else {
                            $imgdata['icondata'] = 'static/dzzthumb/preview/b.gif';
                        }
                    }
                    $originalimg = $thumbdir . '/.bf/.preview/' . $thumbpath . '/' . $bid . '.hd.webp';
                    //大图地址
                    if (!$resourcesdata['hasthumb'] && in_array($ext, $pichomespecialimgextarr) && IO::checkfileexists($originalimg)) {
                        $imgdata['originalimg'] = IO::getFileUri($originalimg);
                    } else {
                        $imgdata['originalimg'] = $imgdata['icondata'];
                    }
                    break;
                case 1:
                case 3:
                    $thumbdata = C::t('thumb_record')->fetch($resourcesdata['rid']);
                    if ($thumbdata['sstatus']) $imgdata['icondata'] = getglobal('siteurl') . IO::getFileuri($thumbdata['spath']);
                    else $imgdata['icondata'] = false;
                    if ($thumbdata['lstatus']) $imgdata['originalimg'] = getglobal('siteurl') . IO::getFileuri($thumbdata['lpath']);
                    else  $imgdata['originalimg'] =  (!$return) ? false: getglobal('siteurl') . 'index.php?mod=io&op=createThumb&path='.$resourcesdata['dpath'].'&size=large';
                    break;
            }
        }


        return $imgdata;
    }

    public function fetch_by_rid($rid, $nolevel = 0, $contaiondel = 0)
    {
        global $_G;
        if (!$resourcesdata = parent::fetch($rid)) return array();
        if ($resourcesdata['isdelete'] > 0 && !$contaiondel) return array();
        //获取所有库分享和下载权限
        $appdata = C::t('pichome_vapp')->fetch_all_sharedownlod($resourcesdata['appid']);
        $attrdata = C::t('pichome_resources_attr')->fetch($rid);
        if ($attrdata['desc']) $attrdata['desc'] = strip_tags($attrdata['desc']);
        $resourcesdata = array_merge($resourcesdata, $attrdata);
        if (is_numeric($resourcesdata['path'])) {
            $attachment = C::t('attachment')->fetch(intval($resourcesdata['path']));
            $bz = io_remote::getBzByRemoteid($attachment['remote']);
            $resourcesdata['bz'] = $bz;
            $resourcesdata['remoteid'] = $attachment['remote'];
            $resourcesdata['path'] = $bz . $attachment['attachment'];
            $resourcesdata = array_merge($resourcesdata, $attachment);
        }
        else {
            if (strpos($appdata['path'], ':') === false ) {
                $resourcesdata['bz'] = 'dzz::';
            } else {
                $patharr = explode(':', $appdata['path']);
                if($patharr[1] && is_numeric($patharr[1])){
                    $resourcesdata['bz'] = $patharr[0] . ':' . $patharr[1] . ':';
                    $resourcesdata['remoteid'] = $patharr[1];
                }else{
                    $resourcesdata['bz'] = 'dzz::';
                    $resourcesdata['remoteid'] = 1;
                }
            }
            $resourcesdata['path'] = $appdata['path'] . BS . $resourcesdata['path'];
        }
        $resourcesdata['colors'] = C::t('pichome_palette')->fetch_colordata_by_rid($rid);
        $resourcesdata['ext'] = strtolower($resourcesdata['ext']);
        $resourcesdata['opentype'] = getOpentype($resourcesdata['ext']);
        if($resourcesdata['isdelete']){
            $resourcesdata['share'] =0;
            $resourcesdata['download'] = 0;
            $resourcesdata['collection'] = 0;
        }else{
            $resourcesdata['share'] = $nolevel ? 0:C::t('pichome_vapp')->getpermbypermdata($appdata['share'], $resourcesdata['appid'], 'share');
            $resourcesdata['download'] = $nolevel ? 1:C::t('pichome_vapp')->getpermbypermdata($appdata['download'], $resourcesdata['appid'], 'download');
            $resourcesdata['collection'] = $nolevel ? 0:(defined('PICHOME_LIENCE') && ($_G['adminid'] == 1 || ($_G['uid'] && !$_G['config']['pichomeclosecollect']))) ? 1 : 0;
        }

        $resourcesdata['isdetail'] = 1;
        $resourcesdata['dpath'] =dzzencode($rid, '', 0, 0);
        $imgdata = $this->getfileimageurl($resourcesdata, $appdata['path'], $appdata['type'], $resourcesdata['download'],1);
        $resourcesdata = array_merge($resourcesdata, $imgdata);

        if ($resourcesdata['width'] == 0) $resourcesdata['width'] = 900;
        if ($resourcesdata['height'] == 0) $resourcesdata['height'] = 900;
        $thumbwidth = getglobal('config/pichomethumlargwidth') ? getglobal('config/pichomethumlargwidth') : 1920;
        $thumbheight = getglobal('config/pichomethumlargheight') ? getglobal('config/pichomethumlargheight') : 1080;
        $thumsizearr = $this->getImageThumbsize($resourcesdata['width'], $resourcesdata['height'], $thumbwidth, $thumbheight);
        $resourcesdata['iconwidth'] = $thumsizearr[0];
        $resourcesdata['iconheight'] = $thumsizearr[1];

        if (getglobal('adminid') == 1) $resourcesdata['realfianllypath'] = getglobal('siteurl') . 'index.php?mod=io&op=getStream' . '&path=' . dzzencode($rid . '_7', '', 0, 0);

        $resourcesdata['name'] = str_replace(strrchr($resourcesdata['name'], "."), "", $resourcesdata['name']);
        $resourcesdata['fsize'] = formatsize($resourcesdata['size']);
        $resourcesdata['mtime'] = dgmdate(round($resourcesdata['mtime'] / 1000), 'Y/m/d H:i');
        $resourcesdata['dateline'] = ($resourcesdata['lastdate']) ? dgmdate(round($resourcesdata['lastdate']), 'Y/m/d H:i') : dgmdate(round($resourcesdata['dateline'] / 1000), 'Y/m/d H:i');
        $resourcesdata['btime'] = dgmdate(round($resourcesdata['btime'] / 1000), 'Y/m/d H:i');
        $resourcesdata['foldernames'] = C::t('pichome_folderresources')->get_foldername_by_rid($rid);
        $resourcesdata['tag'] = C::t('pichome_resourcestag')->fetch_tag_by_rid($rid);

        $src = $this->getOpensrc($resourcesdata['ext'], $resourcesdata['bz']);
        unset($resourcesdata['path']);
        $random = rand();
        $resourcesdata['iniframe'] = ($src) ? $src . '&random=' . $random . '&hash=' . VERHASH . '&path=' . $resourcesdata['dpath'] : '';
        return $resourcesdata;
    }




    public function getdatasbyrids($rids, $nodel = 0)
    {
        global $_G;
        $returndata = [];
        //文件数据
        $resourcesdata = $this->fetch_by_rids($rids);
        //获取所有库分享和下载权限,以及编码数据
        $downshare = C::t('pichome_vapp')->fetch_all_sharedownlod();

        //文件标注数
        $annonationnumdata = C::t('pichome_comments')->fetch_annonationnum_by_rids($rids);

        //获取所有的缩略图表数据
        $imagedatas = $this->getThumbsByrids($rids);
        foreach ($resourcesdata as $v) {
            if ($nodel && $v['isdelete'] > 0) continue;
            if($v['isdelete']){
                $v['share'] = $v['download'] = $v['collection'] = 0;
            }else{

                $v['share'] = C::t('pichome_vapp')->getpermbypermdata($downshare[$v['appid']]['share'], $v['appid'], 'share');
                $v['download'] = C::t('pichome_vapp')->getpermbypermdata($downshare[$v['appid']]['download'], $v['appid'], 'download');
                $v['collection'] = (defined('PICHOME_LIENCE') && ($_G['adminid'] == 1 || ($_G['uid'] && !$_G['config']['pichomeclosecollect']))) ? 1 : 0;
            }

            if ($imagedatas[$v['rid']]['imgstatus']) {
                $imgdata = ['icondata' => $imagedatas[$v['rid']]['icondata'], 'originalimg' => $imagedatas[$v['rid']]['originalimg']];
            } else {
                $imgdata = $this->getfileimageurl($v, $downshare[$v['appid']]['path'], $downshare[$v['appid']]['type'], $v['download']);
            }
            $v = array_merge($v, $imgdata);
            $v['annonationnum'] = $annonationnumdata[$v['rid']]['num'];
            $thumbwidth = getglobal('config/pichomethumsmallwidth') ? getglobal('config/pichomethumsmallwidth') : 360;
            $thumbheight = getglobal('config/pichomethumsmallwidth') ? getglobal('config/pichomethumsmallwidth') : 360;
            if ($v['width'] == 0) $v['width'] = 900;
            if ($v['height'] == 0) $v['height'] = 900;
            $thumsizearr = $this->getImageThumbsize($v['width'], $v['height'], $thumbwidth, $thumbheight);
            $v['thumbwidth'] = $thumsizearr[0];
            $v['thumbheight'] = $thumsizearr[1];
            //获取文件所属目录数
            $v['foldernum'] = DB::result_first("select count(id) from %t where rid = %s", array('pichome_folderresources', $v['rid']));
            $intcolor = DB::result_first("select color from %t where rid = %s order by weight desc", array('pichome_palette', $v['rid']));
            $v['color'] = dechex($intcolor);
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

    public function geticondata_by_rid($rid, $onlyicon = 0)
    {
        $resourcesdata = DB::fetch_first("select r.rid,r.isdelete,r.appid,r.ext,r.type,ra.path as fpath,
            v.path,v.type as apptype,v.download from %t r 
        left join %t ra on r.rid=ra.rid left join %t v on r.appid = v.appid where r.rid = %s ",
            array($this->_table, 'pichome_resources_attr', 'pichome_vapp', $rid));
        if ($resourcesdata['isdelete']) {
            if ($onlyicon) return false;
            else return $resourcesdata;
        } else {
            $download = C::t('pichome_vapp')->getpermbypermdata($resourcesdata['download'], $resourcesdata['appid'], 'download');
            $imgdata = $this->getfileimageurl($resourcesdata, $resourcesdata['path'], $resourcesdata['apptype'], $download);

            if ($onlyicon) {
                return $imgdata['icondata'];
            } else {
                return array_merge($resourcesdata, $imgdata);
            }
        }


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

    //移动文件目录 复制或剪切
    public function move_file_to_folder($rid, $fid, $iscopy = 1)
    {
        //查询文件基本信息
        $data = parent::fetch($rid);
        if (!$data) return false;
        //如果是复制增加目录
        if ($iscopy) {
            //原有fid
            $ofids = $data['fids'] ? explode(',', $data['fids']) : [];
            $rfolder = ['appid' => $data['appid'], 'rid' => $rid, 'fid' => $fid];
            C::t('pichome_folderresources')->insert($rfolder);
            if (!in_array($fid, $ofids)) {
                $ofids[] = $fid;
                $attrs = ['fids' => implode(',', $ofids), 'lastdate' => TIMESTAMP];
                C::t('pichome_resources')->update_by_rids($data['appid'], $rid, $attrs);
            }
            return true;
        } else {//如果是剪切
            //原有fid
            $ofids = $data['fids'] ? explode(',', $data['fids']) : [];
            //移除原目录属性
            C::t('pichome_folderresources')->delete_by_ridfid($rid, $ofids);
            //添加新目录属性
            $rfolder = ['appid' => $data['appid'], 'rid' => $rid, 'fid' => $fid];
            C::t('pichome_folderresources')->insert($rfolder);
            $attrs = ['fids' => $fid, 'lastdate' => TIMESTAMP];
            C::t('pichome_resources')->update_by_rids($data['appid'], $rid, $attrs);
            return true;
        }
        return false;
    }

    public function upload_file($data, $attrover = 1)
    {
        global $_G;

        if (!$data) return false;
        $aid = $data['aid'];
        $appid = $data['appid'];
        //属性信息
        $dataattrs = json_decode($data['attr'], true);
        //处理目录数据
        if ($dataattrs['fid']) {
            $folderdata = C::t('pichome_folder')->fetch($dataattrs['fid']);
        }
        $setarr = [];
        $setarr['lastdate'] = TIMESTAMP;
        //如果当前库有该文件,则使用当前文件
        if ($rid = DB::result_first("select rid from %t where path = %d and appid = %s ", array('pichome_resources_attr', $aid, $appid))) {
            $resourcesdata = C::t('pichome_resources')->fetch($rid);
            $nfids = explode(',', $resourcesdata['fids']);
            $setarr['rid'] = $rid;
            $rsetarr = [
                'fids' => $nfids ? implode(',', $nfids) : ''
            ];
            if (!in_array($folderdata['fid'], $nfids)) {
                $nfids[] = $folderdata['fid'];
            }
            //处理主表数据
            $setarr['uid'] = $data['uid'];
            $setarr['username'] = $data['username'];
            //如果是覆盖模式1,没有的属性增加，有的属性不替换
            //echo intval($dataattrs['level']);
            if ($attrover == 1) {
                //评分
                if ($dataattrs['grade'] && !$resourcesdata['grade']) $setarr['grade'] = intval($dataattrs['grade']);
                //密级
                if ($dataattrs['level'] && !$resourcesdata['level']) $setarr['level'] = intval($dataattrs['level']);
                //目录数据
                if ($nfids) $setarr['fids'] = implode(',', $nfids);
                $setarr['dateline'] = $data['dateline'] * 1000;
            } else {
                if ($dataattrs['grade']) $setarr['grade'] = intval($dataattrs['grade']);
                //密级
                if ($dataattrs['level']) $setarr['level'] = intval($dataattrs['level']);
                //目录数据
                if ($nfids) $setarr['level'] = implode(',', $nfids);
                $setarr['dateline'] = $data['dateline'] * 1000;
            }
            //print_r($dataattrs);
            //print_r($setarr);die;
            //修改主表数据
            if (C::t('pichome_resources')->update($rid, $setarr)) {//插入主表
                //目录数据
                if ($folderdata['fid']) {
                    $frsetarr = ['appid' => $appid, 'rid' => $setarr['rid'], 'fid' => $folderdata['fid']];
                    C::t('pichome_folderresources')->insert($frsetarr);
                    C::t('pichome_folder')->add_filenum_by_fid($folderdata['fid'], 1);
                }
                //属性表数据
                $oldattrs = C::t('pichome_resources_attr')->fetch($setarr['rid']);
                $attrs = ['desc' => $oldattrs['desc'], 'link' => $oldattrs['link']];
                if ($dataattrs['tag']) {
                    $tagnamearr = !is_array($dataattrs['tag']) ? explode(',', $dataattrs['tag']) : $dataattrs['tag'];
                    $tids = [];
                    //增加和插入标签关系表数据
                    foreach ($tagnamearr as $v) {
                        $tid = C::t('pichome_tag')->insert($v, 1);
                        $rtag = ['appid' => $appid, 'rid' => $rid, 'tid' => $tid];
                        $tids[] = $tid;
                        C::t('pichome_resourcestag')->insert($rtag);
                    }
                    //处理属性表标签数据
                    $oldtidarr = explode(',', $oldattrs['tag']);
                    if ($oldtidarr) $attrs['tag'] = implode(',', array_merge($tids, $oldtidarr));
                    else $attrs['tag'] = implode(',', $tids);
                }
                if ($attrover == 1) {
                    if (!$attrs['desc'] && $dataattrs['desc']) $attrs['desc'] = $dataattrs['desc'];
                    if (!$attrs['link'] && $dataattrs['link']) $attrs['link'] = $dataattrs['link'];
                } else {
                    if ($dataattrs['desc']) $attrs['desc'] = $dataattrs['desc'];
                    if ($dataattrs['desc']) $attrs['link'] = $dataattrs['link'];
                }
                $attrs['searchval'] = $resourcesdata['name'] . $attrs['desc'] . htmlspecialchars($attrs['link']);
                C::t('pichome_resources_attr')->update($rid, $attrs);
                //标签卡数据
                foreach ($dataattrs as $k => $val) {
                    if (strpos($k, 'tabgroup_') === 0) {
                        $gid = intval(str_replace('tabgroup_', '', $k));
                        $odatatids = [];
                        foreach (DB::fetch_all("select tid from %t where rid = %s and gid = %d", array('pichome_resourcestab', $rid, $gid)) as $v) {
                            $odatatids[] = $v['tid'];
                        }
                        if (!in_array($val, $odatatids)) {
                            $rtag = ['appid' => $appid, 'rid' => $rid, 'tid' => $val, 'gid' => $gid];
                            C::t('pichome_resourcestab')->insert($rtag);
                        }
                    }
                }
                return $setarr;
            } else {
                return false;
            }
        } elseif ($rid = DB::result_first("select rid from %t where path = %d ", array('pichome_resources_attr', $aid))) {
            //如果当前库无该文件但其它库有
            //获取原文件基本数据
            $resourcesdata = C::t('pichome_resources')->fetch($rid);

            $rsetarr = [
                'lastdate' => TIMESTAMP * 1000,
                'appid' => $appid,
                'apptype' => 1,
                'size' => $resourcesdata['filesize'],
                'type' => $resourcesdata['type'],
                'ext' => $resourcesdata['ext'],
                'mtime' => $data['dateline'] * 1000,
                'dateline' => TIMESTAMP * 1000,
                'btime' => $data['dateline'] * 1000,
                'width' => $resourcesdata['width'],
                'height' => $resourcesdata['height'],
                'lastdate' => TIMESTAMP,
                'level' => $dataattrs['level'] ? $dataattrs['level'] : 0,
                'name' => $data['filename'],
                'fids' => $folderdata['fid'] ? $folderdata['fid'] : ''
            ];
            if ($rsetarr['rid'] = C::t('pichome_resources')->insert_data($rsetarr)) {//插入主表
                //获取附属表数据
                $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                $rid = $attrdata['rid'] = $rsetarr['rid'];
                $attrdata['appid'] = $appid;
                $attrdata['link'] = $dataattrs['link'];
                $attrdata['desc'] = $dataattrs['desc'];
                $attrdata['searchval'] = $rsetarr['name'] . htmlspecialchars($dataattrs['link']) . $dataattrs['desc'];
                C::t('attachment')->addcopy_by_aid($attrdata['path']);//增加图片使用数

                if ($dataattrs['tag']) {
                    $tagnamearr = !is_array($dataattrs['tag']) ? explode(',', $dataattrs['tag']) : $dataattrs['tag'];
                    $tids = [];
                    //增加和插入标签关系表数据
                    foreach ($tagnamearr as $v) {
                        $tid = C::t('pichome_tag')->insert($v, 1);
                        $rtag = ['appid' => $appid, 'rid' => $rid, 'tid' => $tid];
                        $tids[] = $tid;
                        C::t('pichome_resourcestag')->insert($rtag);
                    }
                    //处理属性表标签数据
                    $attrdata['tag'] = implode(',', $tids);
                }

                C::t('pichome_resources_attr')->insert($attrdata);

                //目录数据
                if ($folderdata['fid']) {
                    $frsetarr = ['appid' => $appid, 'rid' => $rid, 'fid' => $folderdata['fid']];
                    C::t('pichome_folderresources')->insert($frsetarr);
                    C::t('pichome_folder')->add_filenum_by_fid($folderdata['fid'], 1);
                }
                //缩略图数据
                $thumbrecorddata = C::t('thumb_record')->fetch($rid);
                $thumbrecorddata['rid'] = $rsetarr['rid'];
                $thumbrecorddata['sstatus'] = $thumbrecorddata['lstatus'] = $thumbrecorddata['stimes'] = $thumbrecorddata['ltimes'] = 0;

                C::t('thumb_record')->insert_data($thumbrecorddata);
                //颜色数据
                foreach (DB::fetch_all("select * from %t where rid = %s", array('pichome_palette', $rid)) as $v) {
                    $v['rid'] = $rid;
                    unset($v['id']);
                    C::t('pichome_palette')->insert($v);
                }
                C::t('pichome_vapp')->addcopy_by_appid($appid);

                //标签卡数据
                foreach ($dataattrs as $k => $val) {
                    if (strpos($k, 'tabgroup_') === 0) {
                        $gid = intval(str_replace('tabgroup_', '', $k));
                        $odatatids = [];
                        foreach (DB::fetch_all("select tid from %t where rid = %s and gid = %d", array('pichome_resourcestab', $rid, $gid)) as $v) {
                            $odatatids[] = $v['tid'];
                        }
                        if (!in_array($val, $odatatids)) {
                            $rtag = ['appid' => $appid, 'rid' => $rid, 'tid' => $val, 'gid' => $gid];
                            C::t('pichome_resourcestab')->insert($rtag);
                        }
                    }
                }
                return $rsetarr;
            } else {
                return false;
            }
        } else {

            $dataattrs = json_decode($data['attr'], true);
            $attach = C::t('attachment')->fetch($data['aid']);
            $imginfo = getimagesize($_G['setting']['attachdir'] . $attach['attachment']);
            $setarr = [
                'lastdate' => TIMESTAMP * 1000,
                'appid' => $appid,
                'apptype' => 1,
                'uid' => $data['uid'],
                'username' => $data['username'],
                'size' => $data['filesize'],
                'type' => getTypeByExt($data['ext']),
                'ext' => $data['ext'],
                'mtime' => $data['dateline'] * 1000,
                'dateline' => TIMESTAMP * 1000,
                'btime' => $data['dateline'] * 1000,
                'width' => isset($imginfo[0]) ? $imginfo[0] : 0,
                'height' => isset($imginfo[1]) ? $imginfo[1] : 0,
                'lastdate' => TIMESTAMP,
                'level' => $dataattrs['level'] ? $dataattrs['level'] : 0,
                'grade' => $dataattrs['grade'] ? $dataattrs['grade'] : 0,
                'name' => $attach['filename'],
                'fids' => $folderdata['fid'] ? $folderdata['fid'] : ''
            ];
            if ($setarr['rid'] = C::t('pichome_resources')->insert_data($setarr)) {//插入主表
                C::t('attachment')->update($attach['aid'], array('copys' => $attach['copys'] + 1));//增加图片使用数
                //属性表数据
                $attrdata = [
                    'rid' => $setarr['rid'],
                    'appid' => $appid,
                    'path' => $attach['aid'],
                    'searchval' => $setarr['name']
                ];
                $attrdata['appid'] = $appid;
                $attrdata['link'] = $dataattrs['link'];
                $attrdata['desc'] = $dataattrs['desc'];
                $attrdata['searchval'] = $setarr['name'] . htmlspecialchars($dataattrs['link']) . $dataattrs['desc'];
                C::t('attachment')->addcopy_by_aid($data['aid']);//增加图片使用数

                if ($dataattrs['tag']) {
                    $tagnamearr = !is_array($dataattrs['tag']) ? explode(',', $dataattrs['tag']) : $dataattrs['tag'];
                    $tids = [];
                    //增加和插入标签关系表数据
                    foreach ($tagnamearr as $v) {
                        $tid = C::t('pichome_tag')->insert($v, 1);
                        $rtag = ['appid' => $appid, 'rid' => $setarr['rid'], 'tid' => $tid];
                        $tids[] = $tid;
                        C::t('pichome_resourcestag')->insert($rtag);
                    }
                    //处理属性表标签数据
                    $attrdata['tag'] = implode(',', $tids);
                }
                C::t('pichome_resources_attr')->insert($attrdata);
                //目录数据
                if ($folderdata['fid']) {
                    $frsetarr = ['appid' => $appid, 'rid' => $setarr['rid'], 'fid' => $folderdata['fid']];
                    C::t('pichome_folderresources')->insert($frsetarr);
                    C::t('pichome_folder')->add_filenum_by_fid($folderdata['fid'], 1);
                }

                $wp = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarkstatus'] : '';
                $wt = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarktype'] : '';
                $wcontent = $_G['setting']['IsWatermarkstatus'] ? ($_G['setting']['watermarktype'] == 'png' ? $_G['setting']['waterimg'] : '') : '';
                //缩略图数据
                $thumbrecorddata = [
                    'rid' => $setarr['rid'],
                    'ext' => $setarr['ext'],
                    'filesize' => $setarr['size'],
                    'width' => $setarr['width'],
                    'height' => $setarr['height'],
                    'swidth' => $_G['seetting']['thumbsize']['small']['width'],
                    'sheight' => $_G['seetting']['thumbsize']['small']['height'],
                    'lwidth' => $_G['setting']['thumbsize']['large']['width'],
                    'lheight' => $_G['setting']['thumbsize']['large']['height'],
                    'lwaterposition' => $wp,
                    'lwatertype' => $wt,
                    'lwatercontent' => $wcontent,
                    'swaterposition' => $wp,
                    'swatertype' => $wt,
                    'swatercontent' => $wcontent
                ];
                C::t('thumb_record')->insert($thumbrecorddata);
                //标签卡数据
                foreach ($dataattrs as $k => $val) {
                    if (strpos($k, 'tabgroup_') === 0) {
                        $gid = intval(str_replace('tabgroup_', '', $k));
                        $odatatids = [];
                        foreach (DB::fetch_all("select tid from %t where rid = %s and gid = %d", array('pichome_resourcestab', $rid, $gid)) as $v) {
                            $odatatids[] = $v['tid'];
                        }
                        if (!in_array($val, $odatatids)) {
                            $rtag = ['appid' => $appid, 'rid' => $setarr['rid'], 'tid' => $val, 'gid' => $gid];
                            C::t('pichome_resourcestab')->insert($rtag);
                        }
                    }
                }
                C::t('pichome_vapp')->addcopy_by_appid($appid);
                return $setarr;
            } else {
                return false;
            }
        }
    }


}
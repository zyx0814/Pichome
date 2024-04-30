<?php
if(!defined('IN_OAOOA')) {
    exit('Access Denied');
}
class table_thumb_preview extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'thumb_preview';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'thumb_preview';
        $this->_cache_ttl = 3600;
        parent::__construct();
    }

    public  function addPreview($rid,$aid){
        global $_G;
       if($id = DB::result_first("select id from %t where rid = %s and opath = %d ",[$this->_table,$rid,$aid])){
            return $id;
       }else{
           //获取图片数据
           $imgData = IO::getMeta('attach::'.$aid,1);
           $wp = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarkstatus']:'';
           $wt = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarktype']:'';
           $wcontent = $_G['setting']['IsWatermarkstatus'] ? ($_G['setting']['watermarktype'] == 'png' ? $_G['setting']['waterimg']:$_G['setting']['position_text']):'';
           $setarr = [
               'rid'=>$rid,
               'opath'=>$imgData['path'],
               'aid'=>$aid,
               'width' => $imgData['width'] ? intval($imgData['width']):0,
               'height' => $imgData['height'] ? intval($imgData['height']):0,
               'lwidth' => $_G['setting']['thumbsize']['large']['width'],
               'lheight' => $_G['setting']['thumbsize']['large']['height'],
               'swidth' => $_G['setting']['thumbsize']['small']['width'],
               'sheight' =>$_G['setting']['thumbsize']['small']['height'],
               'filesize' => $imgData['filesize'],
               'ext' => $imgData['ext'],
               'lwaterposition'=>$wp,
               'lwatertype'=>$wt,
               'lwatercontent'=>$wcontent,
               'swaterposition'=>$wp,
               'swatertype'=>$wt,
               'swatercontent'=>$wcontent,

           ];
           if($id = parent::insert($setarr,1)){
               C::t('attachment')->addcopy_by_aid($aid,1);
               return $id;
           }
       }
       return false;

    }

    public function editCover($rid,$aid){
        global $_G;
        $imgData = IO::getMeta('attach::'.$aid,1);
        $wp = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarkstatus']:'';
        $wt = $_G['setting']['IsWatermarkstatus'] ? $_G['setting']['watermarktype']:'';
        $wcontent = $_G['setting']['IsWatermarkstatus'] ? ($_G['setting']['watermarktype'] == 'png' ? $_G['setting']['waterimg']:$_G['setting']['position_text']):'';
        $setarr = [
            'rid'=>$rid,
            'opath'=>$imgData['path'],
            'aid'=>$aid,
            'width' => $imgData['width'] ? intval($imgData['width']):0,
            'height' => $imgData['height'] ? intval($imgData['height']):0,
            'lwidth' => $_G['setting']['thumbsize']['large']['width'],
            'lheight' => $_G['setting']['thumbsize']['large']['height'],
            'swidth' => $_G['setting']['thumbsize']['small']['width'],
            'sheight' =>$_G['setting']['thumbsize']['small']['height'],
            'filesize' => $imgData['filesize'] ? $imgData['filesize'] : 0,
            'ext' => $imgData['ext'],
            'lwaterposition'=>$wp,
            'lwatertype'=>$wt,
            'lwatercontent'=>$wcontent,
            'swaterposition'=>$wp,
            'swatertype'=>$wt,
            'swatercontent'=>$wcontent,
            'iscover'=>1,
            'sstatus'=>0,
            'lstatus'=>0,
        ];
        if($coverdata = DB::fetch_first("select id from %t where rid = %s  and iscover = %d ",[$this->_table,$rid,1])){
           if($coverdata['opath'] != $aid){
               if(parent::update($coverdata['id'],$setarr)){
                   C::t('attachment')->addcopy_by_aid($aid,1);
                   C::t('attachment')->delete_by_aid($coverdata['aid']);
                   return true;
               }
           }
        }else{
            if(parent::insert($setarr,1)){
                C::t('attachment')->addcopy_by_aid($aid,1);
                return true;
            }
        }
        return false;

    }

    public function delCover($rid){
        if($coverdata = DB::fetch_first("select id,opath from %t where rid = %s  and iscover = %d ",[$this->_table,$rid,1])){
            if(parent::delete($coverdata['id'])){
                C::t('attachment')->delete_by_aid($coverdata['aid']);
                return true;
            }
        }
        return false;
    }

    public function delPreview($id){
        if($previewdata = DB::fetch_first("select id,opath from %t where id = %s ",[$this->_table,$id])){
            if(parent::delete($previewdata['id'])){
                C::t('attachment')->delete_by_aid($previewdata['aid']);
                return true;
            }
        }
        return false;
    }

    public function delete_by_rid($rid){
        if(!is_array($rid)) $rid = (array)$rid;
        if($previewdata = DB::fetch_all("select id,opath from %t where rid in(%n) ",[$this->_table,$rid])){
            foreach($previewdata as $value){
                if(parent::delete($value['id'])){
                    C::t('attachment')->delete_by_aid($value['aid']);
                }
            }
        }
    }

    public function fetchPreviewByRid($rid,$onlysmall=false){
        $returndata = [];
        foreach(DB::fetch_all("select * from %t where rid = %s and iscover !=%d  order by disp asc",[$this->_table,$rid,1]) as $v){
             $tmpdata = [
                'id'=>$v['id'],
                'spath'=>($v['sstatus']) ? IO::getFileUri($v['spath']):IO::getFileUri($v['opath']),
            ];
            if(!$onlysmall) $tmpdata['lpath']=($v['lstatus']) ? IO::getFileUri($v['lpath']):IO::getFileUri($v['opath']);
            $returndata[] = $tmpdata;
        }
        return $returndata;
    }
}
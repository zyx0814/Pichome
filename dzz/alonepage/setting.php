<?php
    /*
     * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
     * @license     https://www.oaooa.com/licenses/
     *
     * @link        https://www.oaooa.com
     * @author      zyx(zyx@oaooa.com)
     */
//此页的调用地址  index.php?mod=test;
//同目录的其他php文件调用  index.php?mod=test&op=test1;
    if (!defined('IN_OAOOA')) {//所有的php文件必须加上此句，防止被外部调用
        exit('Access Denied');
    }
Hook::listen('adminlogin');
$id = isset($_GET['id']) ? intval($_GET['id']):0;
$pageData = C::t('pichome_templatepage')->fetch($id);
Hook::listen('lang_parse',$pageData,['getAlonePageLangData']);
$navtitle = $pageData['pagename'].'-'.lang('page_manage');
include template('page/setting');
    
    
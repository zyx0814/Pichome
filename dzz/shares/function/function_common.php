<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if ( !defined( 'IN_OAOOA' ) ) { //所有的php文件必须加上此句，防止被外部调用
	exit( 'Access Denied' );
}
function checkShare($share){
	global $_G;
	if (!$share) {
		exit(json_encode(array('success'=>false,'msg'=>lang('share_file_iscancled'))));
	}
	if ( $share['deldateline']>0 && $_G['adminid']!=1) {
        exit(json_encode(array('success'=>false,'msg'=>lang('share_file_iscancled'))));
    }
	if ($share['status'] == -4) return array('success'=>false,'msg'=>lang('shared_links_screened_administrator'));
	if ($share['status'] == -5) return array('success'=>false,'msg'=>lang('sharefile_isdeleted_or_positionchange'));
	//判断是否过期
	if ($share['endtime'] && ($share['endtime']+60*60*24) < TIMESTAMP) {
		if($share['status']>-1) C::t('pichome_share')->update($share['id'],array('status'=>-1));
		return array('success'=>false,'msg'=>lang('share_link_expired'));
	}
	if ($share['times'] && $share['times'] <= $share['count']) {
		if($share['status']>-1) C::t('pichome_share')->update($share['id'],array('status'=>-2));
		return array('success'=>false,'msg'=>lang('link_already_reached_max_number'));
	}

	if ($share['status'] == -3) {
		return array('success'=>false,'msg'=>lang('share_file_deleted'));
	}


	return array('success'=>true);
}


<?php
    ignore_user_abort(true);
    @set_time_limit(0);
    global $_G;
    $start = 0;
    $limit = 100;

    foreach(DB::fetch_all("select * from %t where ctype > 0 and `status` < 2 and `status` > 0 limit $start,$limit",array('video_record')) as $v){
        $resourcesdata = C::t('pichome_resources')->fetch_data_by_rid($v['rid']);

      if($v['ctype'] == 2){
            require_once DZZ_ROOT . './dzz/qcos/class/class_video.php';
          $patharr = explode(':', $resourcesdata['path']);
          $did = $patharr[1] ? $patharr[1]:0;
          $qcosconfig = C::t('connect_storage')->fetch($did);
          $hostarr = explode(':',$qcosconfig['hostname']);
          $config = [
              'secretId' => trim($qcosconfig['access_id']),
              'secretKey' => dzzdecode($qcosconfig['access_key'], 'QCOS'),
              'region' => $hostarr[1],
              'schema' => $hostarr[0],
              'bucket'=>trim($qcosconfig['bucket']),
          ];

            $video = new  video($config);

            $jobsstatus= $video->get_jobdata($v['jobid']);
            if($jobsstatus == 2){
                //$outputpath = 'pichomethumb/'. date('Ym') . '/' . date('d') .'/'.md5($resourcesdata['path']).'.'.$v['format'];
                C::t('video_record')->update($v['id'], array('status'=>2,'remoteid'=>$did));
            }elseif($jobsstatus == -1){
                C::t('video_record')->update($v['id'], array('status'=>-1,'dateline'=>TIMESTAMP,'endtime'=>time()));
            }
        }
    }
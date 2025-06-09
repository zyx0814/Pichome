<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
*/

/*php 权限控制类
 *有新的权限在这里添加
 *由于数据库存储是smallint(10),最大支持32位权限；(32位系统最多支持32位，64位系统最多支持64位；)
*/

class perm {



    public static  function getPowerArr()
    {

        return array(
            'flag' => 1,        //标志位为1表示权限设置,否则表示未设置，继承上级；
            'read1' => 2,        //读取自己的文件
            'read2' => 4,        //读取所有文件
            'delete1' => 8,        //删除自己的文件
            'delete2' => 16,        //删除所有文件
            'edit1' => 32,        //编辑自己的文件
            'edit2' => 64,        //编辑所有文件
            'download1' => 128,        //下载自己的文件
            'download2' => 256,        //下载所有文件
            'copy1' => 512,        //拷贝自己的文件
            'copy2' => 1024,    //拷贝所有文件
            'upload' => 2048,    //新建和上传
            //'newtype' => 4096,    //新建其他类型文件（除文件夹以外）
            'folder' => 8192,    //新建文件夹
            'collect' => 16384,    //收藏

            'share' => 262144,    //分享


        );
    }
    public function setPerm($actions,$perm=0){
        if(!is_array($actions)) $actions=array($actions);
        $powerarr=self::getPowerArr();
        foreach($actions as $action){
            if(isset($powerarr[$action])){
                $perm = $perm | $powerarr[$action];
            }
        }
       return $perm;
    }

     public static function addPower($action,$perm=0)
    {
        $powerarr=self::getPowerArr();
        //利用逻辑或添加权限
        if (isset($powerarr[$action])) return $perm | intval($powerarr[$action]);
    }

     public static function mergePower($perm1,$perm2)
    { //合成权限，使用于系统权限和用户权限合成
        return  intval($perm1) & intval($perm2);

    }

     public  function delPower($action,$perm=0)
    {
        $powerarr=self::getPowerArr();
        //删除权限，先将预删除的权限取反，再进行与操作
        if (isset($powerarr[$action])) return  $perm & ~intval($powerarr[$action]);
    }

    public  function isPower($action,$perm=0)
    {
        $powerarr=self::getPowerArr();
        //权限比较时，进行与操作，得到0的话，表示没有权限
        if (!$powerarr[$action]) return 0;
        return $perm & intval($powerarr[$action]);
    }

    public  static  function check($action, $perm=0)
    {
        //权限比较时，进行与操作，得到0的话，表示没有权限
        $perm = intval($perm);
        $powerarr = self::getPowerArr();
        if (!$powerarr[$action]) return 0;
        if (!$perm) return 0;
        return $perm & intval($powerarr[$action]);
    }

}
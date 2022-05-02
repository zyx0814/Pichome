<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if(!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}
//include DZZ_ROOT.'./dzz/news/uninstall_real.php';
//提示用户删除的严重程度
if($_GET['confirm']=='DELETE'){
    include dirname(__FILE__).'/uninstall_real.php';
}else{
    header("Location: $confirm_uninstall_url");
    exit();
}

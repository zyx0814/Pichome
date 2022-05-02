<?php
/* @authorcode  codestrings
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 */

if(!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}

//卸载网盘程序；

$sql = <<<EOF
DROP TABLE IF EXISTS `dzz_eagleweb_vapp`;
DROP TABLE IF EXISTS `dzz_eagleweb_folder`;
DROP TABLE IF EXISTS `dzz_eagleweb_resources`;
DROP TABLE IF EXISTS `dzz_eagleweb_resources_attr`;
DROP TABLE IF EXISTS `dzz_eagleweb_share`;
DROP TABLE IF EXISTS `dzz_eagleweb_tag`;
DROP TABLE IF EXISTS `dzz_eagleweb_taggroup`;
DROP TABLE IF EXISTS `dzz_eagleweb_tagrelation`;
EOF;

runquery($sql);

$finish = true;
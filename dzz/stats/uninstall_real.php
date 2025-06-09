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
DROP TABLE IF EXISTS `pichome_stats_view`;
DROP TABLE IF EXISTS `pichome_stats_userlogin`;
DROP TABLE IF EXISTS `pichome_stats_keyword`;
EOF;

runquery($sql);

$finish = true;
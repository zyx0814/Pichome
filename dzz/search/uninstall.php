<?php
/* @authorcode  codestrings
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 */

if(!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}

//卸载加密程序；

$sql = <<<EOF
DROP TABLE IF EXISTS `pichome_search_template `;

EOF;

runquery($sql);

$finish = true;
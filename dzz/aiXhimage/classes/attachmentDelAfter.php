<?php
namespace dzz\aiXhimage\classes;
use \C as C;
class attachmentDelAfter
{
    public function run($aid){
        if(!$aid) return true;
        C::t('#aiXhimage#ai_xhchat')->delContentByIdvalueAndNotuid($aid,0);
        C::t('ai_imageparse')->deleteByAid($aid);
    }
}
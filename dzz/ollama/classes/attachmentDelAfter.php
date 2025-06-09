<?php
namespace dzz\ollama\classes;
use \C as C;
class attachmentDelAfter
{
    public function run($aid){
        if(!$aid) return true;
        C::t('#ollama#ollama_chat')->delContentByIdvalueAndNotuid($aid,0);
        C::t('ai_imageparse')->deleteByAid($aid);
    }
}
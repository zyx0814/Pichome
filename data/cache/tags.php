<?php 	
 return array (
  'config_read' => 
  array (
    0 => 'core\\dzz\\config',
  ),
  'check_login' => 
  array (
    0 => 'user\\classes\\checklogin',
  ),
  'safe_chk' => 
  array (
    0 => 'user\\classes\\safechk',
  ),
  'dzz_route' => 
  array (
    0 => 'core\\dzz\\route',
  ),
  'dzz_initbefore' => 
  array (
    0 => 'user\\classes\\init|user',
    1 => 'misc\\classes\\init|misc',
  ),
  'dzz_initafter' => 
  array (
    0 => 'user\\classes\\route|user',
  ),
  'app_run' => 
  array (
    0 => 'core\\dzz\\apprun',
  ),
  'mod_run' => 
  array (
    0 => 'core\\dzz\\modrun',
  ),
  'adminlogin' => 
  array (
    0 => 'admin\\login\\classes\\adminlogin',
  ),
  'mod_start' => 
  array (
    0 => 'core\\dzz\\modroute',
  ),
  'login_check' => 
  array (
    0 => 'user\\login\\classes\\logincheck|user',
  ),
  'login_valchk' => 
  array (
    0 => 'user\\login\\classes\\loginvalchk|user/login',
  ),
  'email_chk' => 
  array (
    0 => 'user\\profile\\classes\\emailchk|user',
  ),
  'register_before' => 
  array (
    0 => 'user\\register\\classes\\register|user',
  ),
  'check_val' => 
  array (
    0 => 'user\\register\\classes\\checkvalue|user',
  ),
  'register_common' => 
  array (
    0 => 'user\\register\\classes\\regcommon',
  ),
  'systemlog' => 
  array (
    0 => 'admin\\systemlog\\classes\\systemlog',
  ),
);
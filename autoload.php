<?php

// -------------------------------------------
// 自动载入函数
// -------------------------------------------
spl_autoload_register(function($classname)
{
   $filename = str_replace('\\', '/', $classname);
   $filename = str_replace('Think/', CORE_PATH, $filename);
   $filename = str_replace('App/', LIB_PATH, $filename);

   include_once $filename . EXT;
});
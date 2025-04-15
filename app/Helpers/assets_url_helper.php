<?php 

if(!function_exists('assets_url'))
{
  function assets_url($value)
  {
   return str_replace("/index.php","",$value);
  }
}

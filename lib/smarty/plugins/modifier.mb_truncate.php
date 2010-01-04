<?php

function smarty_modifier_mb_truncate($string, $length = 80, $etc = '...', $break_words = false, $middle = false , $lang='utf-8')
{
  if ($length === 0) {
    return '';
  }

  if (mb_strlen($string) > $length) {
    $length -= mb_strlen($etc);
    if (!$break_words && !$middle) {
      $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1,$lang));
    }
    if(!$middle) {
      return mb_substr($string, 0, $length,$lang).$etc;
    } else {
      return mb_substr($string, 0, $length/2,$lang) . $etc . mb_substr($string, -$length/2,$lang);
    }
  } else {
    return $string;
  }
}

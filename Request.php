<?php

namespace lamanweb\bookcafe;

class Request extends Dropship
{
  const TYPE_NONE   = 0;
  const TYPE_ALPHA  = 1;
  const TYPE_DIGIT  = 2;
  const TYPE_ALNUM  = 3;
  const GET         = 10;
  const POST        = 11;
  const COOKIE      = 12;
  const SESSION     = 13;
  public static function get($name, $method = null, $validation = null)
  {
    if ($method < self::GET && $validation === null) {
      $validation = $method;
      $method = self::GET;
    } else if ($method === null ) {
      $method = self::GET;
    }
    if ($validation === null) {
      $validation = self::TYPE_NONE;
    }
    $holder = null;
    switch ($method) {
      case self::GET:
        $holder = $_GET;
        break;
      case self::POST:
        $holder = $_POST;
        break;
      case self::COOKIE:
        $holder = $_COOKIE;
        break;
      case self::SESSION:
        $holder = $_SESSION;
        break;
    }
    if (!isset($holder[$name])) {
      return false;
    }
    $validator = null;
    switch ($validation) {
      case self::TYPE_ALNUM:
        $validator = 'alnum';
        break;
      case self::TYPE_DIGIT:
        $validator = 'digit';
        break;
      case self::TYPE_ALPHA:
        $validator = 'alpha';
        break;
    }
    $ret_val = $holder[$name];
    $valid_func = 'ctype_' . $validator;
    return (($validator === null) ? $ret_val : (( $valid_func($ret_val) ) ? $ret_val : null ) );
  }
}
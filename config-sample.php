<?php
// Save this file as application/config[/env]mimeil.php and all LaMeil instances
// will be automatically accordingly configured.
//
// Keys are names of MiMeil's instance properties (see LaMeil->init()).

return array(
  // Either 'Person Name<e@ma.il>' or just 'e@ma.il'.
  'from'                  => 'Example.com Robot<robot@example.com>',
  // Return-Path:
  'returnPath'            => null,
  // CC:
  'copyTo'                => array(),
  // BCC:
  'bccTo'                 => array(),
  // X-Priority and Importance: 0 (low), 1 (normal), 2 (high).
  'priority'              => 1,

  // Custom message headers ('Header: Content').
  'headers'               => array(),
  // Content-Transfer-Encoding:
  'bodyEncoding'          => 'base64',
  // Content-Type's charset:
  // (all listed values are tried in turn before falling back to UTF-8)
  'bodyCharsets'          => array('cp1251'),
  // Always use the first charset name in 'bodyCharsets'.
  'forceBodyCharset'      => false,
  'makeTextBodyFromHTML'  => true,
  'textBodyFormat'        => 'flowed',

  // For message headers only.
  'headerEOLN'            => "\n",
  // For message body.
  'eoln'                  => "\r\n",
  // Just for code beauty.
  'sortHeaders'           => true,
  // sendmail's command-line parameters.
  'params'                => '',

  // If true messages won't be actually sent.
  'simulate'              => Request::is_env('local'),
  // If set to a string outgoing .eml's are dumped on this path (useful in
  // combination with 'simulate'.
  'echoPath'              => Request::is_env('local') ? path('storage').'mail' : false,
);
<?php
$config=[
  // needed by JSMF\Application
  'applicationNamespace' => 'Example', // this is the first part of the namespace used by applications classes (e.g. the Controllers)
  'modulePath' => SRC . '/lib/Example/Modules',
  //'baseUri' => '/example/public', // this only needed if your applications base url is not "/"
  'layoutTemplateUrl' => SRC . '/layout/layout', // a layout is optional but a good place for the html skeletton, loading javascript css etc. extension .html.php is assumed automatically

  // needed by JSMF\Database
  'pdo_dsn' => 'mysql:host=127.0.0.1',
  'pdo_user' => 'xxx',
  'pdo_pass' => 'xxx',

  // needed by JSMF\Cache\Redis
  'cache' => [
    'backend' => 'redis',
    'redis'=> [
      'socket' => '/var/run/redis/redis.sock',
      'dbindex' => 30,
    ],
  ],



  'foo' => 'bar',
  'bar' => [
    'baz' => 'buz'
   ],
];

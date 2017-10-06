<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Autoloader for JSMF
 **/

if (!defined('JSMF_AUTOLOADER')) {
  define('JSMF_AUTOLOADER', 1);
  define('BASE_PATH', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..'));
  define('CACHE_FILE', dirname(__FILE__) . DIRECTORY_SEPARATOR . '_autoloader.cache.php');
  $cache = [];

  if (file_exists(CACHE_FILE) && is_readable(CACHE_FILE)) {
    include(CACHE_FILE);
  }

  spl_autoload_register(function ($class) use (&$cache) {

    $path = strtolower(BASE_PATH . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');

    logg('======================');
    logg('Loading class ' . $class . ' searching in ' . $path);
    if (isset($cache[$path])) {
      $path = $cache[$path];
      logg('Found path in cache, using ' . $path);
    }


    if (!file_exists($path)) {
      $pathOriginal = $path;

      logg('not found in path, trying case insensitive searching');

      // try to get it case insensitive
      // upwalk the path until it exists
      $pathExpl = explode(DIRECTORY_SEPARATOR, str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');
      $classExpl = explode('\\', $class);
      $classNotExists = [];
      do {
        array_pop($pathExpl);
        array_unshift($classNotExists, array_pop($classExpl));
      } while (count($pathExpl) && !file_exists(BASE_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR,
          $pathExpl)));

      $lastExistingPath = BASE_PATH . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR,
          $pathExpl) . DIRECTORY_SEPARATOR;
      logg('path exists up to: ' . $lastExistingPath);

      // now we retrieve the lowercased directory structure from the last existing directory and try to match it case insensitive
      do {
        logg('getting directory ' . $lastExistingPath);
        $structure = getLowercasedDirectory($lastExistingPath);
        $checkLcPath = strtolower($lastExistingPath . $classNotExists[0] . (count($classNotExists) == 1 ? '.php' : ''));
        logg('Check lc path: ' . $checkLcPath);
        if (isset($structure[$checkLcPath])) {
          // the dir/file exists with another case, check if we can resolve the path now
          logg('lowcased path ' . $structure[$checkLcPath] . ' does exist!');
          $lastExistingPath = $structure[$checkLcPath] . DIRECTORY_SEPARATOR;


          if (count($classNotExists) == 1) {
            $path = $structure[$checkLcPath];
          } else {
            array_shift($classNotExists);
            $path = $lastExistingPath . implode(DIRECTORY_SEPARATOR, $classNotExists) . '.php';
          }
          logg('Checking ' . $path);
        } else {
          logg('even case-insensitive the path cannot be resolved... ' . $lastExistingPath . strtolower($classNotExists[0]) . ') aborting');
          break;
        }
      } while (!file_exists($path) && count($classNotExists));

      if (file_exists($path)) {
        $cache[$pathOriginal] = $path;

        $cacheContent='<?php' . "\n" . '$cache=[';
        foreach ($cache as $k => $v) {
          $cacheContent.='\'' . $k . '\'=>\'' . str_replace('\'', '\\\'', $v) . '\',';
        }
        $cacheContent.='];';

        file_put_contents(CACHE_FILE, $cacheContent);
      }
    }

    if (file_exists($path)) {
      logg('found in ' . $path);
      include($path);
    } else {
      logg('class not found');
    }
  }, true, true);


  /**
   * get directory content (non-recursive) as array. Keys contain the low-cased path
   * @param String $dir
   * @return Array
   **/
  function getLowercasedDirectory($dir) {
    $structure = Array();
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
      if ($file == '.' || $file == '..' || strpos($file, '.') === 0 || !is_readable($dir . $file)) {
        continue;
      }

      $structure[strtolower($dir . $file)] = $dir . $file;
    }
    closedir($dh);

    return $structure;
  }

  function logg($text) {
//    echo "\n<br />" . $text;
  }
}
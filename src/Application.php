<?php
/**
 * Represents the JSMF Application
 **/

namespace JSMF;

/**
 * Inits and runs the applicaton
 **/
class Application {
  const HOOK_PREOUTPUT=1;
  /**
   * the current requests controllelr
   **/
  private static $_controller;

  /**
   * contains the hooks added to the applications execution process
   **/
  private static $_hooks=[];


  public static function registerAutoloader() {


    spl_autoload_register(function ($class) use (&$cache) {
      $moduleDir = Config::get('modulePath');
      $namespace = Config::get('applicationNamespace');
      if (strpos($class, $namespace) !== 0) {
        return; // only used for our application namespace
      }

      // if module dir ends with class start replace that
      $clsSplit = explode('\\', $class);
      while (count($clsSplit)) {
        $test=implode(DIRECTORY_SEPARATOR, $clsSplit);
        if (strpos($moduleDir, $test) == strlen($moduleDir) - strlen($test)) {
          $moduleDir = substr($moduleDir, 0, strpos($moduleDir, $test) - 1);
        }
        array_pop($clsSplit);
      }


      $path = ($moduleDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php');

      self::logg('======================');
      self::logg('Loading class ' . $class . ' searching in ' . $path);
      if (isset($cache[$path])) {
        $path = $cache[$path];
        self::logg('Found path in cache, using ' . $path);
      }


      if (!file_exists($path)) {
        $pathOriginal = $path;

        self::logg('not found in path, trying case insensitive searching');

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
        self::logg('path exists up to: ' . $lastExistingPath);

        // now we retrieve the lowercased directory structure from the last existing directory and try to match it case insensitive
        do {
          self::logg('getting directory ' . $lastExistingPath);
          $structure = self::getLowercasedDirectory($lastExistingPath);
          $checkLcPath = strtolower($lastExistingPath . $classNotExists[0] . (count($classNotExists) == 1 ? '.php' : ''));
          self::logg('Check lc path: ' . $checkLcPath);
          if (isset($structure[$checkLcPath])) {
            // the dir/file exists with another case, check if we can resolve the path now
            self::logg('lowcased path ' . $structure[$checkLcPath] . ' does exist!');
            $lastExistingPath = $structure[$checkLcPath] . DIRECTORY_SEPARATOR;


            if (count($classNotExists) == 1) {
              $path = $structure[$checkLcPath];
            } else {
              array_shift($classNotExists);
              $path = $lastExistingPath . implode(DIRECTORY_SEPARATOR, $classNotExists) . '.php';
            }
            self::logg('Checking ' . $path);
          } else {
            self::logg('even case-insensitive the path cannot be resolved... ' . $lastExistingPath . strtolower($classNotExists[0]) . ') aborting');
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
        self::logg('found in ' . $path);
        include($path);
      } else {
        self::logg('class not found');
      }
    }, true, true);



  }

  /**
   * get directory content (non-recursive) as array. Keys contain the low-cased path
   * @param String $dir
   * @return Array
   **/
  private static function getLowercasedDirectory($dir) {
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

  private static function logg($text) {
    //echo "\n<br />" . $text;
  }

  /**
   * inits and runs the application
   * @param String|index $defaultModule
   * @param String|index $defaultController
   * @param String|index $defaultAction
   * @return Void
   **/
  public static function run(string $defaultModule='index', string $defaultController='index', string $defaultAction='index') {
    if (!defined('DEV_SERVER')) define('DEV_SERVER', isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'dev.') === 0);


    try {
      $namespace = Config::get('applicationNamespace');
      $moduleDir = Config::get('modulePath');

      // init session
      Session::init(Config::get('session.handler', 'files'));

      // init the request/response
      Response::init();


      // set right template path
      Template::setTemplateDir($moduleDir . DIRECTORY_SEPARATOR . ucfirst(Request::getModule($defaultModule)) . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR . ucfirst(Request::getController($defaultController)) . DIRECTORY_SEPARATOR);

      // instanciate the right controller
      self::$_controller=Controller::factory('\\' . $namespace . '\\Modules', Request::getModule($defaultModule), Request::getController($defaultController));

      // set the action (controller will instanciate the template then)
      self::$_controller->setAction(Request::getAction($defaultAction));

      // setup layout if given in config
      if (Config::has('layoutTemplateUrl')) {
        self::$_controller->view->setLayout(Config::get('layoutTemplateUrl'), (Config::has('layoutTemplateUrl_expire') ? Config::get('layoutTemplateUrl_expire') : 1), $namespace);
      }

      if (isset(self::$_hooks[self::HOOK_PREOUTPUT]) && is_callable(self::$_hooks[self::HOOK_PREOUTPUT])) {
        if (self::$_hooks[self::HOOK_PREOUTPUT]() !== FALSE) {
          // call action and set the output
          Response::setOutput(self::$_controller->callAction());
        }
      } else {
        // call action and set the output
        Response::setOutput(self::$_controller->callAction());
      }



    } catch (Exception $e) {
      // JSMF Exception

      Response::setException($e);
      throw $e;
    }
  }
  
  
  /**
   * add a hook function executed before the output is rendered for injecting template vars etc
   * @param Function callback
   * @return Void
   **/
  public static function addHookPreOutput($callback) {
    self::$_hooks[self::HOOK_PREOUTPUT] = $callback;
  }
  
  
  /**
   * returns current controller instance
   * @return \JSMF\Controller
   **/
  public static function getController() :\JSMF\Controller {
    return self::$_controller;
  }
  
  

}




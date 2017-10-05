<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Inits and runs the application
 **/
namespace JSMF;

class Application {
  const HOOK_PREOUTPUT=1;
  private static $_controller;
  private static $_hooks=[];


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
      self::$_controller=Controller::factory('\\' . $namespace . '\\modules', Request::getModule($defaultModule), Request::getController($defaultController));

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




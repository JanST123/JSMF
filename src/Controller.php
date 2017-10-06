<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Controller class
  * controller class where all controllers inherit from
 **/
namespace JSMF;


class Controller {
  private $_renderTemplate;
  private $_controllerName;
  protected $_action=null;
  private $_template=null;
  public $view=null;


  /**
   * delievers the instance of desired controller
   * @param String $namespace (the namespace that the controllers have. Autoloading must be done outside this class)
   * @param String $moduleName
   * @param String $controllerName
   * @return \JSMF\Controller
   * @throws \JSMF\Exception
   */
  public static function factory(string $namespace, string $moduleName, string $controllerName) :\JSMF\Controller {
    $controllerClass=$namespace . '\\' . $moduleName . '\\Controller\\' . $controllerName;

    if (class_exists($controllerClass)) {
      $controller=new $controllerClass;
      return $controller;
    } else {
      throw new Exception\NotFound('Invalid Controller: '.$controllerClass);
    }
    return false;
  }

  
  public function __construct() {
    $controller=explode('\\', get_class($this));
    $this->_controllerName=strtolower($controller[count($controller)-1]);
  }


  /**
   * normally a template named like the action is rendered,
   * with this method you can override this with another template
   * Set $template to false will disable render
   * @param String $template
   * @return Void
   */
  protected function setRender(string $template) {
    $this->_renderTemplate=$template;

    if ($template!==false) {
      if ($this->view instanceof Template) {
        $this->view->setTemplateFile($template);

      } else {
        $this->view = new Template($this->_renderTemplate);
      }
    }
  }


  /**
   * redirects to another controller/action. If not visible internal calls the action, if visible does a http redirect
   * @param String $action
   * @param String|null $controller (if null, uses current controller)
   * @param Boolean|false $visible
   * @return Void
   */
  protected function _redirect(string $action, string $controller=null, bool $visible=false) {
    $sameController=false;
    if (!$controller) {
      $sameController=true;
      $controller=$this->_controllerName;
    }
      
    if ($visible){
      // do a http redirect to desired controller/action
      header('Location: /?controller='.urlencode($controller).'&action='.urlencode($action), true, 302);
      return;
    }

    $instance=$this;
    if (!$sameController) {
      $instance=self::factory($controller);
    }
    $vars=$this->view->getTemplateVars();
    $instance->setAction($action);


    // copy the variables from our view to the new instances view
    $instance->view->setTemplateVars($vars);

    // call the action!
    $instance->callAction();

    // override our view
    if (!$sameController) {
      $this->view=$instance->view;
    }
  }


  /**
   * sets the action, will load the default template
   * @param String $action
   * @return Void
   * @throws \JSMF\Exception
   */
  public function setAction(string $action) {
    if (empty($this->_controllerName)) {
      throw new Exception('Controllername was empty. Did you forget to call parent::__construct() in your Controllers Constructor? :-)');
    }
    
    $this->_action=$action;

    // load default template

    $this->setRender('scripts/'.$action);
  }

   
  /**
   * calls one action in the controller, loads the template etc
   * @param Array $actionParams
   * @return Mixed (Action return)
   * @throws \JSMF\Exception\NotFound
   */
  public function callAction(array $actionParams=[]) {
    // build action Method Name - replace dashes by camelcase for method name
    $actionMethod=$this->_action . 'Action';


    if ($this->view) {
      $layout = $this->view->getLayout();
      if ($layout) $layout->_action = $this->_action;
    }

    if (method_exists($this, $actionMethod)) {
      // call the action
     
      
      // call init method before each action if exists
      if (method_exists($this, 'init')) {
        $this->init();
      }

      return call_user_func_array([$this, $actionMethod], $actionParams);
    } else {
      throw new Exception\NotFound('Invalid Action: '.$this->_action);
    }
  }
}




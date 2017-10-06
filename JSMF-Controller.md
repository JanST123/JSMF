JSMF\Controller
===============






* Class name: Controller
* Namespace: JSMF





Properties
----------


### $view

    public mixed $view = null





* Visibility: **public**


Methods
-------


### factory

    \JSMF\Controller JSMF\Controller::factory(String $namespace, String $moduleName, String $controllerName)

delievers the instance of desired controller



* Visibility: **public**
* This method is **static**.


#### Arguments
* $namespace **String** - &lt;p&gt;(the namespace that the controllers have. Autoloading must be done outside this class)&lt;/p&gt;
* $moduleName **String**
* $controllerName **String**



### __construct

    mixed JSMF\Controller::__construct()





* Visibility: **public**




### setAction

    Void JSMF\Controller::setAction(String $action)

sets the action, will load the default template



* Visibility: **public**


#### Arguments
* $action **String**



### callAction

    Mixed JSMF\Controller::callAction(Array $actionParams)

calls one action in the controller, loads the template etc



* Visibility: **public**


#### Arguments
* $actionParams **Array**



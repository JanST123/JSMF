JSMF\Application
===============

Inits and runs the applicaton




* Class name: Application
* Namespace: JSMF



Constants
----------


### HOOK_PREOUTPUT

    const HOOK_PREOUTPUT = 1







Methods
-------


### run

    Void JSMF\Application::run(String|\JSMF\index $defaultModule, String|\JSMF\index $defaultController, String|\JSMF\index $defaultAction)

inits and runs the application



* Visibility: **public**
* This method is **static**.


#### Arguments
* $defaultModule **String|JSMF\index**
* $defaultController **String|JSMF\index**
* $defaultAction **String|JSMF\index**



### addHookPreOutput

    Void JSMF\Application::addHookPreOutput(\JSMF\Function $callback)

add a hook function executed before the output is rendered for injecting template vars etc



* Visibility: **public**
* This method is **static**.


#### Arguments
* $callback **JSMF\Function** - &lt;p&gt;callback&lt;/p&gt;



### getController

    \JSMF\Controller JSMF\Application::getController()

returns current controller instance



* Visibility: **public**
* This method is **static**.




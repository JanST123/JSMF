JSMF\Request
===============

Handle and analyse the current HTTP Request




* Class name: Request
* Namespace: JSMF







Methods
-------


### getInstance

    \JSMF\Request JSMF\Request::getInstance()

Returns instance of this class



* Visibility: **public**
* This method is **static**.




### isAjax

    Boolean JSMF\Request::isAjax()

returns if current request is ajax request



* Visibility: **public**
* This method is **static**.




### expectsJson

    boolean JSMF\Request::expectsJson()

returns if current request expects JSON as answert



* Visibility: **public**
* This method is **static**.




### __get

    Mixed JSMF\Request::__get(String $key)

alias got get method



* Visibility: **public**


#### Arguments
* $key **String**



### __set

    Void JSMF\Request::__set(String $key, Mixed $value)

sets a request var (you normally should not use this.

..)

* Visibility: **public**


#### Arguments
* $key **String**
* $value **Mixed**



### serialize

    string JSMF\Request::serialize()

serialize the current request for saving



* Visibility: **public**
* This method is **static**.




### unserialize

    mixed JSMF\Request::unserialize(string $dataStr)

Unserialize request data



* Visibility: **public**
* This method is **static**.


#### Arguments
* $dataStr **string**



### get

    Mixed JSMF\Request::get(String $key, String|null $default, String|\JSMF\REQUEST $from, Boolean|true $sanitized)

gets request param, with default value, optionally sanitize



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $default **String|null**
* $from **String|JSMF\REQUEST** - &lt;p&gt;(GET, POST, PAYLOAD or REQUEST)&lt;/p&gt;
* $sanitized **Boolean|true**



### has

    boolean JSMF\Request::has(String $key, String|\JSMF\REQUEST $from)

returns if param exists in request



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $from **String|JSMF\REQUEST**



### getGet

    Mixed JSMF\Request::getGet(String $key, String|null $default, Boolean|true $sanitized)

alias for get(.

.., ..., 'GET', ...)

* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $default **String|null**
* $sanitized **Boolean|true**



### getPost

    Mixed JSMF\Request::getPost(String $key, String|null $default, Boolean|true $sanitized)

alias for get(.

.., ..., 'POST', ...)

* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $default **String|null**
* $sanitized **Boolean|true**



### getCookie

    Mixed JSMF\Request::getCookie(String $key, String|null $default, Boolean|true $sanitized)

alias for get(.

.., ..., 'COOKIE', ...)

* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $default **String|null**
* $sanitized **Boolean|true**



### isPost

    Boolean JSMF\Request::isPost()

returns if request_method is POST



* Visibility: **public**
* This method is **static**.




### getPayload

    Mixed JSMF\Request::getPayload(Boolean|true $raw, Boolean|false $forceAssociativeArray)

returns payload



* Visibility: **public**
* This method is **static**.


#### Arguments
* $raw **Boolean|true**
* $forceAssociativeArray **Boolean|false**



### getOrigin

    String JSMF\Request::getOrigin()

returns the origin sent by parameter or header or null if no origin given



* Visibility: **public**
* This method is **static**.




### redirect

    mixed JSMF\Request::redirect(String $location, Boolean|false $permanent)

redirect to the location, this will send the header directly and php script is aborted



* Visibility: **public**
* This method is **static**.


#### Arguments
* $location **String**
* $permanent **Boolean|false** - &lt;p&gt;return Void&lt;/p&gt;



### addRoute

    mixed JSMF\Request::addRoute(String $regex, String $targetModule, String|\JSMF\index $targetController, String|\JSMF\index $targetAction, Array|array<mixed,> $targetParameters)

adds a new route which (url pattern which will redirect to targetcontroller, targetaction with target parameters)



* Visibility: **public**
* This method is **static**.


#### Arguments
* $regex **String**
* $targetModule **String**
* $targetController **String|JSMF\index**
* $targetAction **String|JSMF\index**
* $targetParameters **Array|array&lt;mixed,&gt;**



### getController

    String JSMF\Request::getController(String $default)

gets the controller or returns default if not given
url format /module/controller/action



* Visibility: **public**
* This method is **static**.


#### Arguments
* $default **String**



### getAction

    String JSMF\Request::getAction(String $default)

gets the action or returns default if not given
url format /module/controller/action



* Visibility: **public**
* This method is **static**.


#### Arguments
* $default **String**



### getModule

    String JSMF\Request::getModule(String $default)

gets the module or returns default if not given
url format /module/controller/action



* Visibility: **public**
* This method is **static**.


#### Arguments
* $default **String**



### isCrossOrigin

    boolean JSMF\Request::isCrossOrigin()

returns if current request is cross origin request



* Visibility: **public**
* This method is **static**.




### getUriParts

    Array JSMF\Request::getUriParts()

returns the uri parts without module/controller/action as an array



* Visibility: **public**
* This method is **static**.




### getRequestUri

    String JSMF\Request::getRequestUri()

returns the request array, attends to proxy forwareded uri



* Visibility: **public**
* This method is **static**.




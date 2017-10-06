JSMF\Session
===============






* Class name: Session
* Namespace: JSMF



Constants
----------


### SESSION_HANDLER_FILES

    const SESSION_HANDLER_FILES = 'files'





### SESSION_HANDLER_DATABASE

    const SESSION_HANDLER_DATABASE = 'db'





### SESSION_HANDLER_MEMCACHE

    const SESSION_HANDLER_MEMCACHE = 'memcache'







Methods
-------


### init

    mixed JSMF\Session::init(String|\JSMF\files $sessionHandler)

starts the session



* Visibility: **public**
* This method is **static**.


#### Arguments
* $sessionHandler **String|JSMF\files**



### injectSessionId

    mixed JSMF\Session::injectSessionId(string $sessionId)

inject session id



* Visibility: **public**
* This method is **static**.


#### Arguments
* $sessionId **string**



### close

    Void JSMF\Session::close()

writes and closes the session



* Visibility: **public**
* This method is **static**.




### getSessionAge

    integer JSMF\Session::getSessionAge()

returns session age in seconds. Useful for exceptio



* Visibility: **public**
* This method is **static**.




### isStarted

    boolean JSMF\Session::isStarted()

returns if a JSMF Session was started



* Visibility: **public**
* This method is **static**.




### get

    Mixed JSMF\Session::get(String $key, String|null $default)

gets session value or default if not exists



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $default **String|null**



### getAll

    array JSMF\Session::getAll(null $prefix)

Returns all session values



* Visibility: **public**
* This method is **static**.


#### Arguments
* $prefix **null**



### set

    Void JSMF\Session::set(String $key, String $val)

sets a session value



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $val **String**



### has

    Boolean JSMF\Session::has(String $key, \JSMF\bool $emptyCheck)

returns if a session key exists



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $emptyCheck **JSMF\bool**



### delete

    Void JSMF\Session::delete(String $key)

deletes a sessio key



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**



### regenerateId

    Void JSMF\Session::regenerateId()

regenerates session-id and marks the old session as destroyed
so it will be deleted after short time



* Visibility: **public**
* This method is **static**.




### sh_open

    void JSMF\Session::sh_open()

session handler for session open
is called from php internal session handler



* Visibility: **public**
* This method is **static**.




### sh_close

    void JSMF\Session::sh_close()

session handler for session close
is called from php internal session handler



* Visibility: **public**
* This method is **static**.




### sh_read

    void JSMF\Session::sh_read(String $sessionId)

session handler for session read
is called from php internal session handler



* Visibility: **public**
* This method is **static**.


#### Arguments
* $sessionId **String**



### sh_write

    void JSMF\Session::sh_write(String $sessionId, String $data)

session handler for session write
is called from php internal session handler



* Visibility: **public**
* This method is **static**.


#### Arguments
* $sessionId **String**
* $data **String**



### sh_destroy

    void JSMF\Session::sh_destroy(String $sessionId)

session handler for session destroy
is called from php internal session handler



* Visibility: **public**
* This method is **static**.


#### Arguments
* $sessionId **String**



### sh_gc

    void JSMF\Session::sh_gc(integer $lifetime)

session handler for session garbage collection
is called from php internal session handler



* Visibility: **public**
* This method is **static**.


#### Arguments
* $lifetime **integer**



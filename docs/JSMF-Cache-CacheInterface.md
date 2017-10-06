JSMF\Cache\CacheInterface
===============






* Interface name: CacheInterface
* Namespace: JSMF\Cache
* This is an **interface**






Methods
-------


### connect

    Boolean JSMF\Cache\CacheInterface::connect()

connects to server if not already connected, returns status



* Visibility: **public**
* This method is **static**.




### get

    Mixed JSMF\Cache\CacheInterface::get(String $key, Mixed $default)

return item from cache



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $default **Mixed**



### set

    Boolean JSMF\Cache\CacheInterface::set(String $key, Mixed $value, Int|null $expire)

stores value in cache



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $value **Mixed**
* $expire **Int|null**



### has

    Boolean JSMF\Cache\CacheInterface::has(String $key)

checks if cache key exists



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**



### delete

    Boolean JSMF\Cache\CacheInterface::delete(String $key)

deletes item from cache



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**



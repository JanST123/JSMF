JSMF\Cache\Memcache
===============






* Class name: Memcache
* Namespace: JSMF\Cache
* This class implements: [JSMF\Cache\CacheInterface](JSMF-Cache-CacheInterface.md)






Methods
-------


### connect

    Boolean JSMF\Cache\CacheInterface::connect()

connects to server if not already connected, returns status



* Visibility: **public**
* This method is **static**.
* This method is defined by [JSMF\Cache\CacheInterface](JSMF-Cache-CacheInterface.md)




### get

    Mixed JSMF\Cache\CacheInterface::get(String $key, Mixed $default)

return item from cache



* Visibility: **public**
* This method is **static**.
* This method is defined by [JSMF\Cache\CacheInterface](JSMF-Cache-CacheInterface.md)


#### Arguments
* $key **String**
* $default **Mixed**



### set

    Boolean JSMF\Cache\CacheInterface::set(String $key, Mixed $value, Int|null $expire)

stores value in cache



* Visibility: **public**
* This method is **static**.
* This method is defined by [JSMF\Cache\CacheInterface](JSMF-Cache-CacheInterface.md)


#### Arguments
* $key **String**
* $value **Mixed**
* $expire **Int|null**



### has

    Boolean JSMF\Cache\CacheInterface::has(String $key)

checks if cache key exists



* Visibility: **public**
* This method is **static**.
* This method is defined by [JSMF\Cache\CacheInterface](JSMF-Cache-CacheInterface.md)


#### Arguments
* $key **String**



### delete

    Boolean JSMF\Cache\CacheInterface::delete(String $key)

deletes item from cache



* Visibility: **public**
* This method is **static**.
* This method is defined by [JSMF\Cache\CacheInterface](JSMF-Cache-CacheInterface.md)


#### Arguments
* $key **String**



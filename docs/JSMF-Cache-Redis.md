JSMF\Cache\Redis
===============






* Class name: Redis
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




### selectDBIndex

    mixed JSMF\Cache\Redis::selectDBIndex(integer $index)

switches the active dbindex



* Visibility: **public**
* This method is **static**.


#### Arguments
* $index **integer**



### getKeys

    array JSMF\Cache\Redis::getKeys(String $pattern)

returns all redis keys matching the pattern



* Visibility: **public**
* This method is **static**.


#### Arguments
* $pattern **String**



### listPop

    mixed JSMF\Cache\Redis::listPop(String $key)

pops one element of the end of the list



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String** - &lt;p&gt;key&lt;/p&gt;



### listShift

    mixed JSMF\Cache\Redis::listShift(String $key)

shifts an element from the beginning of a list



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String** - &lt;p&gt;key&lt;/p&gt;



### listPush

    mixed JSMF\Cache\Redis::listPush(String $key, mixed $value)

pushes an element to the end of a list



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $value **mixed**



### listUnshift

    mixed JSMF\Cache\Redis::listUnshift(String $key, mixed $value)

inserts an element to the beginning of the list, moving all existing elements on index further



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $value **mixed**



### listCount

    integer JSMF\Cache\Redis::listCount(String $key)

returns the number of elements in a list



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**



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



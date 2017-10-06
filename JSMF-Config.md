JSMF\Config
===============






* Class name: Config
* Namespace: JSMF







Methods
-------


### load

    Void JSMF\Config::load(String $configFile)

load config file. Multiple config files were merged to one config registry



* Visibility: **public**
* This method is **static**.


#### Arguments
* $configFile **String** - &lt;p&gt;(absolute path)&lt;/p&gt;



### get

    Mixed JSMF\Config::get(String $configKey, $default)

get config value, returns null if not exists



* Visibility: **public**
* This method is **static**.


#### Arguments
* $configKey **String**
* $default **mixed**



### has

    Boolean JSMF\Config::has(String $configKey)

returns if configkey is present



* Visibility: **public**
* This method is **static**.


#### Arguments
* $configKey **String**



### set

    Void JSMF\Config::set(String $configKey, Mixed $value, Boolean|true $overwrite)

set a config key



* Visibility: **public**
* This method is **static**.


#### Arguments
* $configKey **String**
* $value **Mixed**
* $overwrite **Boolean|true**



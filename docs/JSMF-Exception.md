JSMF\Exception
===============






* Class name: Exception
* Namespace: JSMF
* Parent class: Exception



Constants
----------


### USER_EXCEPTION

    const USER_EXCEPTION = 1337







Methods
-------


### __toString

    mixed JSMF\Exception::__toString()





* Visibility: **public**




### getAdditionalData

    Array JSMF\Exception::getAdditionalData()

returns additional exception data if set



* Visibility: **public**




### __construct

    \Exception JSMF\Exception::__construct(String|null $message, Int $code, \JSMF\Exception|null $previous, Array $additionalData)

constructs exception



* Visibility: **public**


#### Arguments
* $message **String|null**
* $code **Int**
* $previous **[JSMF\Exception](JSMF-Exception.md)|null**
* $additionalData **Array** - &lt;p&gt;(append additional data to the exceptoni (makes sense on json response))&lt;/p&gt;



### isBad

    Boolean JSMF\Exception::isBad()

returns if this is a "bad" exception where the developers should be informed about



* Visibility: **public**




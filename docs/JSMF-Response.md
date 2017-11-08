JSMF\Response
===============






* Class name: Response
* Namespace: JSMF







Methods
-------


### init

    mixed JSMF\Response::init()





* Visibility: **public**
* This method is **static**.




### addHeader

    Void JSMF\Response::addHeader(String $header, \JSMF\int $httcode, Boolean|true $replace)

adds a header for sending later



* Visibility: **public**
* This method is **static**.


#### Arguments
* $header **String**
* $httcode **JSMF\int**
* $replace **Boolean|true**
* $onlyIfNotExists **Boolean|false**



### setOutputFormat

    Void JSMF\Response::setOutputFormat(String $format)

set an output format (default is json)



* Visibility: **public**
* This method is **static**.


#### Arguments
* $format **String**



### addHeaderNotFound

    Void JSMF\Response::addHeaderNotFound()

adds a 404 not found header



* Visibility: **public**
* This method is **static**.




### addHeaderServerError

    Void JSMF\Response::addHeaderServerError()

adds a 500 internal server error header



* Visibility: **public**
* This method is **static**.




### setOutput

    Void JSMF\Response::setOutput(Mixed $data)

sets the output for sending later. JSON Encodes non-scalar values



* Visibility: **public**
* This method is **static**.


#### Arguments
* $data **Mixed**



### setException

    Void JSMF\Response::setException(\JSMF\Exception $e)

sets exception for outputting



* Visibility: **public**
* This method is **static**.


#### Arguments
* $e **[JSMF\Exception](JSMF-Exception.md)**



### output

    Void JSMF\Response::output()

sends the headers and output to the browser



* Visibility: **public**
* This method is **static**.




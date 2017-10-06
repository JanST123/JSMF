JSMF\Template
===============






* Class name: Template
* Namespace: JSMF







Methods
-------


### __isset

    Mixed JSMF\Template::__isset(String $sName)

checks for a template variable



* Visibility: **public**


#### Arguments
* $sName **String**



### __get

    Mixed JSMF\Template::__get(String $sName)

get a template variable



* Visibility: **public**


#### Arguments
* $sName **String**



### __call

    Mixed JSMF\Template::__call(String $sName, $params)

call a function assigned as template var



* Visibility: **public**


#### Arguments
* $sName **String**
* $params **mixed**



### __set

    Void JSMF\Template::__set(String $sName, Mixed $sVal)

set a template variable



* Visibility: **public**


#### Arguments
* $sName **String**
* $sVal **Mixed**



### __toString

    String JSMF\Template::__toString()

prints out the template



* Visibility: **public**




### __construct

    Void JSMF\Template::__construct(String $sTemplateFile, Array|\JSMF\Array() $aTemplateVariables)

constructs a new template



* Visibility: **public**


#### Arguments
* $sTemplateFile **String** - &lt;p&gt;path to template file&lt;/p&gt;
* $aTemplateVariables **Array|JSMF\Array()** - &lt;p&gt;Variables to assign to the template&lt;/p&gt;



### setTemplateFile

    mixed JSMF\Template::setTemplateFile(string $sTemplateFile)

set another template file to override the one given in constructor



* Visibility: **public**


#### Arguments
* $sTemplateFile **string**



### getTemplateVars

    Array JSMF\Template::getTemplateVars()

returns all assigned template vars



* Visibility: **public**




### setTemplateVars

    Void JSMF\Template::setTemplateVars(Array $templateVars)

sets template vars



* Visibility: **public**


#### Arguments
* $templateVars **Array**



### partial

    String JSMF\Template::partial(String $sTemplateFile, Array|\JSMF\Array() $aTemplateVariables)

renders a partial within current template



* Visibility: **public**


#### Arguments
* $sTemplateFile **String**
* $aTemplateVariables **Array|JSMF\Array()**



### setLayout

    Void JSMF\Template::setLayout(String $layoutFile, Int $cacheExpiration, String $cachePrefix)

set the layout. If it is an URL, it is fetched by cURL



* Visibility: **public**


#### Arguments
* $layoutFile **String** - &lt;p&gt;(the full path to the layout file, but WITHOUT the file ending .html.php)&lt;/p&gt;
* $cacheExpiration **Int** - &lt;p&gt;(when $path is url, the number of seconds the layout is cached in local filesystem)&lt;/p&gt;
* $cachePrefix **String**



### getLayout

    \JSMF\Template JSMF\Template::getLayout()

return the current layout template instance



* Visibility: **public**




### hasLayout

    boolean JSMF\Template::hasLayout()

returns if template has layout



* Visibility: **public**




### disableLayout

    Void JSMF\Template::disableLayout(Boolean|true $bDisable)

disables/enables layout and then renders only the content



* Visibility: **public**


#### Arguments
* $bDisable **Boolean|true**



### setTemplateDir

    Void JSMF\Template::setTemplateDir(String $dir)

sets the directory in which templates are searched



* Visibility: **public**
* This method is **static**.


#### Arguments
* $dir **String**



### setMinimizeOutput

    mixed JSMF\Template::setMinimizeOutput(boolean $minimize)

enables or disables output minimizing



* Visibility: **public**
* This method is **static**.


#### Arguments
* $minimize **boolean**



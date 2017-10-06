JSMF\Language
===============






* Class name: Language
* Namespace: JSMF
* Parent class: Exception







Methods
-------


### getCurrentLanguage

    String JSMF\Language::getCurrentLanguage()

returns the current active language



* Visibility: **public**
* This method is **static**.




### setLanguage

    mixed JSMF\Language::setLanguage(\JSMF\string $language)

sets the current language



* Visibility: **public**
* This method is **static**.


#### Arguments
* $language **JSMF\string**



### loadTranslations

    Void JSMF\Language::loadTranslations(String $file, String $language)

load the given language files for the given languages



* Visibility: **public**
* This method is **static**.


#### Arguments
* $file **String** - &lt;p&gt;(path and filename without extension)&lt;/p&gt;
* $language **String**



### getAll

    Array JSMF\Language::getAll()

returns all translations



* Visibility: **public**
* This method is **static**.




### get

    String JSMF\Language::get(String $key, Array $replaces)

returns translation



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**
* $replaces **Array**



### isTranslated

    Boolean JSMF\Language::isTranslated(String $key)

returns if a key is translated



* Visibility: **public**
* This method is **static**.


#### Arguments
* $key **String**



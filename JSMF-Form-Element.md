JSMF\Form\Element
===============






* Class name: Element
* Namespace: JSMF\Form
* Parent class: DOMElement







Methods
-------


### bindToDoc

    \JSMF\Form\Element JSMF\Form\Element::bindToDoc(\JSMF\Form\DOMDocument $doc)

binds the element to DOMDocument, after that changes to the element were allowed



* Visibility: **public**


#### Arguments
* $doc **JSMF\Form\DOMDocument**



### appendChild

    \JSMF\Form\Element JSMF\Form\Element::appendChild(\DOMNode $newChild)

appends child to element



* Visibility: **public**


#### Arguments
* $newChild **DOMNode**



### addAttributes

    \JSMF\Form\Element JSMF\Form\Element::addAttributes(Array $attributes)

adds more attributes to the element



* Visibility: **public**


#### Arguments
* $attributes **Array**



### addAttribute

    mixed JSMF\Form\Element::addAttribute(String $name, Mixed $value)

Helper function to create attr with value



* Visibility: **public**


#### Arguments
* $name **String**
* $value **Mixed** - &lt;p&gt;return \JSMF\Form\Element&lt;/p&gt;



### hasAttribute

    Boolean JSMF\Form\Element::hasAttribute(String $name)

returns true if an element attribute is set



* Visibility: **public**


#### Arguments
* $name **String**



### addClass

    \JSMF\Form\Element JSMF\Form\Element::addClass(String $name)

add a class to the class attribute (if not exists)



* Visibility: **public**


#### Arguments
* $name **String**



### hasClass

    Boolean JSMF\Form\Element::hasClass(String $name)

determines if element has a specific css class



* Visibility: **public**


#### Arguments
* $name **String**



### getParent

    \JSMF\Form\DOMElement JSMF\Form\Element::getParent(String|null $tagname)

returns the elements direct parent



* Visibility: **public**


#### Arguments
* $tagname **String|null**



### getParentByClass

    \JSMF\Form\DOMElement JSMF\Form\Element::getParentByClass(String $class)

returns the elements direct parent by class



* Visibility: **public**


#### Arguments
* $class **String**



### setValue

    \JSMF\Form\Element JSMF\Form\Element::setValue(String $sValue)

Sets the elemnts value



* Visibility: **public**


#### Arguments
* $sValue **String**



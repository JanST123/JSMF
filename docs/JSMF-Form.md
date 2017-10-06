JSMF\Form
===============






* Class name: Form
* Namespace: JSMF







Methods
-------


### __toString

    String JSMF\Form::__toString()

outputs all the form fields (but not the <form> tags!!)



* Visibility: **public**




### __get

    String JSMF\Form::__get(String $fieldName)

returns the HTML for a form field



* Visibility: **public**


#### Arguments
* $fieldName **String**



### __call

    String JSMF\Form::__call($fieldName, array $attributes)

returns the HTML for a form field and sets the given field attributes



* Visibility: **public**


#### Arguments
* $fieldName **mixed**
* $attributes **array**



### __construct

    mixed JSMF\Form::__construct(String|null $formName, Boolean|false $enableCsrfCheck, String|null $csrfFailMessage)

creates new form



* Visibility: **public**


#### Arguments
* $formName **String|null** - &lt;p&gt;(unique name of this form, needed for csrf check. Must be fixed name, no random uniqueid)&lt;/p&gt;
* $enableCsrfCheck **Boolean|false**
* $csrfFailMessage **String|null**



### printLabelAfterElement

    \JSMF\Form JSMF\Form::printLabelAfterElement(Boolean|true $val)

sets the option to print the label AFTER the element (useful for css only matierial design)



* Visibility: **public**


#### Arguments
* $val **Boolean|true**



### getData

    Array JSMF\Form::getData(Boolean|false $ignoreValidation)

returns the form data



* Visibility: **public**


#### Arguments
* $ignoreValidation **Boolean|false** - &lt;p&gt;(set to true to get data without calling validate() method before, otherwise exception is thrown)&lt;/p&gt;



### validate

    Boolean JSMF\Form::validate(Array $messages, \JSMF\bool $csrfFailed)

validates the form



* Visibility: **public**


#### Arguments
* $messages **Array** - &lt;p&gt;(if form is invalid, the failing messages where added to this array)&lt;/p&gt;
* $csrfFailed **JSMF\bool**



### addValidator

    \JSMF\Form JSMF\Form::addValidator(String $validatorClass, String $invalidMessageTranslationKey, \JSMF\Arary $arguments)

add a validator to the last added field



* Visibility: **public**


#### Arguments
* $validatorClass **String**
* $invalidMessageTranslationKey **String**
* $arguments **JSMF\Arary**



### setFieldWrapper

    \JSMF\Form JSMF\Form::setFieldWrapper(String $tag, Array $classes)

set an element to wrap each label/field occurence



* Visibility: **public**


#### Arguments
* $tag **String** - &lt;p&gt;(the HTML Tag without &lt;&gt;)&lt;/p&gt;
* $classes **Array** - &lt;p&gt;(optional classes to add to wrapper)&lt;/p&gt;



### addField

    \JSMF\Form JSMF\Form::addField(String $name, String|null $label, String|null $id, String|\JSMF\text $type, Array|null $values, Mixed|null $defaultValue)

adds a new field to the form setup



* Visibility: **public**


#### Arguments
* $name **String**
* $label **String|null**
* $id **String|null** - &lt;p&gt;(if not given, is set to $name)&lt;/p&gt;
* $type **String|JSMF\text** - &lt;p&gt;(for the different field types (e.g. select, checkbox...) - you can also use the shortcut functions e.g. addSelect, addCheckbox...)&lt;/p&gt;
* $values **Array|null** - &lt;p&gt;(if fieldType needs values / options (e.g. selectboxes etc.))&lt;/p&gt;
* $defaultValue **Mixed|null**



### addSelect

    \JSMF\Form JSMF\Form::addSelect(String $name, Array $options, String|null $label, String|null $id, Mixed|null $defaultValue)

adds a selectbox field



* Visibility: **public**


#### Arguments
* $name **String**
* $options **Array** - &lt;p&gt;(key=radiobutton value, value=radiobutton label)&lt;/p&gt;
* $label **String|null**
* $id **String|null** - &lt;p&gt;(if not given, is set to $name)&lt;/p&gt;
* $defaultValue **Mixed|null**



### addCheckbox

    \JSMF\Form JSMF\Form::addCheckbox(String $name, String $onValue, String|null $label, String|null $id, Mixed|null $defaultValue)

adds a checbox



* Visibility: **public**


#### Arguments
* $name **String**
* $onValue **String**
* $label **String|null**
* $id **String|null** - &lt;p&gt;(if not given, is set to $name)&lt;/p&gt;
* $defaultValue **Mixed|null**



### addRadiobutton

    \JSMF\Form JSMF\Form::addRadiobutton(String $name, Array $options, String|null $label, String|null $id, Mixed|null $defaultValue)

adds a radiobutton group



* Visibility: **public**


#### Arguments
* $name **String**
* $options **Array** - &lt;p&gt;(key=radiobutton value, value=radiobutton label)&lt;/p&gt;
* $label **String|null**
* $id **String|null** - &lt;p&gt;(if not given, is set to $name)&lt;/p&gt;
* $defaultValue **Mixed|null**



### addTextarea

    \JSMF\Form JSMF\Form::addTextarea(String $name, String|null $label, String|null $id, Mixed|null $defaultValue)

adds a textarea field



* Visibility: **public**


#### Arguments
* $name **String**
* $label **String|null**
* $id **String|null** - &lt;p&gt;(if not given, is set to $name)&lt;/p&gt;
* $defaultValue **Mixed|null**



### setOutputLabel

    \JSMF\Form JSMF\Form::setOutputLabel(Boolean $option)

sets label outputting on or off (default is on)



* Visibility: **public**


#### Arguments
* $option **Boolean** - &lt;p&gt;(true to output label, false to do not)&lt;/p&gt;



### isSubmitted

    Boolean JSMF\Form::isSubmitted()

return if this form was submitted



* Visibility: **public**




### reset

    Void JSMF\Form::reset()

resets the form



* Visibility: **public**




### setValue

    Void JSMF\Form::setValue(String $field, Mixed $value)

sets a fields value



* Visibility: **public**


#### Arguments
* $field **String**
* $value **Mixed**



### regenerateCsrfToken

    string JSMF\Form::regenerateCsrfToken()

Regenerates CSRF Token and returns the new token



* Visibility: **public**




### getFormName

    string JSMF\Form::getFormName()

Returns current form name



* Visibility: **public**




### overrideCSRFToken

    mixed JSMF\Form::overrideCSRFToken(string $token)

Overrides crsf token. should be used with caution!



* Visibility: **public**


#### Arguments
* $token **string**



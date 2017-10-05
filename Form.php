<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Form class controller
 **/
namespace JSMF;
use JSMF\Form\Element;
use JSMF\Language;

class Form {
  private $_outputLabel=true;
  private $_formFields=[];
  private $_lastAddedField;
  private $_formData=[];
  private $_formName=null;
  private $_enableCsrfCheck=false;
  private $_csrfFailMessage=null;
  private $_validationDone=false;
  private $_csrfSessionKey=null;
  private $_csrfFieldName='jsmf_form_csrf';
  private $_csrfFieldPrinted=false;
  private $_formIdFieldPrinted=false;
  private $_wrapperElement=null;
  private $_printLabelAfterElement=false;



  /**
   * outputs all the form fields (but not the <form> tags!!)
   * @return String $html
   **/
  public function __toString() {
    $ret='';
    try {
      $doc = new \DOMDocument('1.0', 'utf-8');
      foreach ($this->_formFields as $name => $field) {
        $this->_printElement($name, [], $doc);
      }
      $ret = $doc->saveHTML();
    } catch(\Exception $e) {
      $ret = 'Exception: ' . $e->getMessage();
    }

    
    return $ret;
  }


  /**
   * returns the HTML for a form field
   * @param String $fieldName
   * @return String $html
   **/
  public function __get($fieldName) {
    return $this->_printElement($fieldName);
  }


  /**
   * returns the HTML for a form field and sets the given field attributes
   * @pararm String $fieldName
   * @param array $attributes
   * @return String $html
   **/
  public function __call($fieldName, $attributes) {
    return $this->_printElement($fieldName, isset($attributes[0]) && is_array($attributes[0]) ? $attributes[0] : [], null, isset($attributes[1]) && is_array($attributes[1]) ? $attributes[1] : []);
  }


  /**
   * creates new form
   * @param String|null $formName (unique name of this form, needed for csrf check. Must be fixed name, no random uniqueid)
   * @param Boolean|false $enableCsrfCheck
   * @param String|null $csrfFailMessage
   **/
  public function __construct(string $formName=null, bool $enableCsrfCheck=false, string $csrfFailMessage=null) {
    $this->_formName = $formName;
    $this->_enableCsrfCheck = $enableCsrfCheck;
    $this->_csrfFailMessage = $csrfFailMessage;

    if ($this->_enableCsrfCheck) {
      if (empty($this->_formName)) throw new Exception('When CSRF Check should be enabled a formName must be set');
      if (empty($this->_csrfFailMessage))  throw new Exception('When CSRF Check should be enabled a csrfFailMessage must be set');

      $this->_csrfSessionKey = 'jsmf_form_csrf_' . $this->_formName;
    }
  }


  /**
   * sets the option to print the label AFTER the element (useful for css only matierial design)
   * @param Boolean|true $val
   * @return \JSMF\Form
   **/
  public function printLabelAfterElement(bool $val=true) {
    $this->_printLabelAfterElement=$val;
    return $this;
  }

  /**
   * returns the form data
   * @param Boolean|false $ignoreValidation (set to true to get data without calling validate() method before, otherwise exception is thrown)
   * @return Array
   **/
  public function getData(bool $ignoreValidation=false) :array {
    if (!$this->_validationDone && $this->_mustValidate() && !$ignoreValidation) throw new Exception('Validation has to be done before getting data. Call validate() method before getData()');

    $this->_fillFromPost();
    return $this->_formData;
  }



  /**
   * validates the form
   * @param Array $messages (if form is invalid, the failing messages where added to this array)
   * @return Boolean $allValid
   **/
  public function validate(array &$messages, bool &$csrfFailed=false) :bool {
    $this->_validationDone=true;
    
    $this->_fillFromPost();

    $allValid = true;
    if (!is_array($messages)) $messages = [];

    foreach ($this->_formFields as $name => $field) {
      if (isset($field['validators']) && is_array($field['validators'])) {
        foreach ($field['validators'] as $validator) {
          // call validators init method if exists
          if (method_exists($validator['class'], 'init')) {
            call_user_func([$validator['class'], 'init'], $this->_formFields[$name]['element']);
          }

          // validate

          $validatorResult=call_user_func([$validator['class'], 'validate'],
                             $this->_formFields[$name]['element'],
                             isset($this->_formData[$name]) ? $this->_formData[$name] : null,
                             isset($validator['arguments']) ? $validator['arguments'] : [],
                             $this->_formData
                            );
          if (is_array($validatorResult)) {
            // invalid with custom messages
            $allValid = false;
            $messages[$name] = '<ul><li>' . implode('</li><li>', array_merge([ $validator['invalidMessage'] ], $validatorResult)) . '</li></ul>';
            $field['element']->addClass('invalid');


          } elseif ($validatorResult == true) {
            // valid

          } else {
            // invalid
            $allValid = false;
            $messages[$name] = $validator['invalidMessage'];
            $field['element']->addClass('invalid');
          }
        }
      }
    }

    if ($allValid && $this->_enableCsrfCheck && !$this->_csrfCheck()) {
      $messages[] = $this->_csrfFailMessage;
      $csrfFailed=true;
      return false;
    }

    return $allValid;
  }
  
  

  /**
   * add a validator to the last added field
   * @param String $validatorClass
   * @param String $invalidMessageTranslationKey
   * @param Arary $arguments
   * @return \JSMF\Form
   * @throws \JSMF\Exception
   **/
  public function addValidator(string $validatorClass, string $invalidMessageTranslationKey, array $arguments=[]) :\JSMF\Form {
    if (!$this->_lastAddedField) {
      throw new Exception('Use addField() Method first before adding validator');
    }
    $className = '\\JSMF\\Form\\Validator\\' . ucfirst($validatorClass);
    if (!class_exists($className)) {
      throw new Exception('Validator ' . $className . ' does not exist');
    }

    if (!in_array('JSMF\\Form\\Validator\\ValidatorInterface', class_implements($className))) {
      throw new Exception('Validator ' . $className . ' does not implement ValidatorInterface!');
    }

    $this->_formFields[$this->_lastAddedField]['validators'][] = [
      'class' => $className,
      'arguments' => $arguments,
      'invalidMessage' => Language::isTranslated($invalidMessageTranslationKey) ? Language::get($invalidMessageTranslationKey, [  $this->_formFields[$this->_lastAddedField]['label'] ]) : $invalidMessageTranslationKey,
    ];

    return $this;
  }
  
  
  /**
   * set an element to wrap each label/field occurence
   * @param String $tag (the HTML Tag without <>)
   * @param Array $classes (optional classes to add to wrapper)
   * @return \JSMF\Form
   **/
  public function setFieldWrapper(string $tag, array $classes=[]) :\JSMF\Form {
    $this->_wrapperElement = new Element($tag);
    foreach ($classes as $c) {
      $this->_wrapperElement->addClass($c);
    }
    return $this;
  } 


  /**
   * adds a new field to the form setup
   * @param String $name
   * @param String|null $label
   * @param String|null $id (if not given, is set to $name)
   * @param String|text $type (for the different field types (e.g. select, checkbox...) - you can also use the shortcut functions e.g. addSelect, addCheckbox...)
   * @param Array|null $values (if fieldType needs values / options (e.g. selectboxes etc.))
   * @param Mixed|null $defaultValue
   * @return \JSMF\Form
   **/
  public function addField(string $name, string $label=null, string $id=null, string $type='text', array $values=[], $defaultValue=null) :\JSMF\Form {
    $id = $id ? $id : $name;


    $tag='input';
    $attributes=[
      'name' => $name,
      'id' => $id,
    ];

    switch($type) {
      case 'textarea':
        $tag = 'textarea';
        break;

      case 'select':
        $tag = 'select';
        break;

      case 'checkbox':
        $attributes['type'] = $type;
        $attributes['value'] = isset($values[0]) ? $values[0] : 1;
        break;

      case 'radiobutton':
        $tag=null; // we create a custom element
        $element=$this->_createRadioButtonGroup($name, $id, $values);
        break;

      case 'text':
      case 'hidden':
      case 'password':
      case 'number':
      case 'date':
      case 'range':
      case 'month':
      case 'week':
      case 'time':
      case 'datetime':
      case 'datetime-local':
      case 'email':
      case 'search':
      case 'tel':
      case 'url':
        $attributes['type'] = $type;
        break;
    }


    if ($tag) {
      $element = new Element($tag);
    }
    
    if ($element && count($attributes)) {
      $element->addAttributes($attributes);
    }

    if ($type == 'select') {
      // add the options
      $optionEls=[];
      $optionGroupEls=[];
      foreach ($values as $optionValue => $optionLabel) {
        $options=[];
        $optgroup=null;

        if (is_array($optionLabel)) {
          // optiongroup where $optionValue is the name
          $optionGroupEls[$optionValue] = new Element('optgroup');
          $optionGroupEls[$optionValue]->addAttribute('label', $optionValue);
          $element->appendChild($optionGroupEls[$optionValue]);
          $optgroup = $optionGroupEls[$optionValue];
          $options = $optionLabel;
        } else {
          $options = [ $optionValue => $optionLabel ];
        }

        foreach ($options as $optionValue => $optionLabel) {
          $optionEls[$optionValue] = new Element('option');
          $optionEls[$optionValue]->addAttribute('value', $optionValue);
          $optionEls[$optionValue]->appendChild(new \DOMText($optionLabel));
          if ($optgroup) {
            $optgroup->appendChild($optionEls[$optionValue]);
          } else {
            $element->appendChild($optionEls[$optionValue]);
          }
        }
      }
    }
    
    $this->_formFields[$name] = [
                                 'element' => $element,
                                 'label' => $label,
                                 'type' => $type,
                                 'id' => $id,
                                 'validators' => [],
                                ];
    
    if ($defaultValue !== null) $this->_formData[$name] = $defaultValue;

    
    $this->_lastAddedField = $name;
    
    return $this;
  }


  /**
   * adds a selectbox field
   * @param String $name
   * @param Array $options (key=radiobutton value, value=radiobutton label)
   * @param String|null $label
   * @param String|null $id (if not given, is set to $name)
   * @param Mixed|null $defaultValue
   * @return \JSMF\Form
   **/
  public function addSelect(string $name, array $options, string $label=null, string $id=null, $defaultValue=null) :\JSMF\Form {
    return $this->addField($name, $label, $id, 'select', $options, $defaultValue);
  }


  /**
   * adds a checbox
   * @param String $name
   * @param String $onValue
   * @param String|null $label
   * @param String|null $id (if not given, is set to $name)
   * @param Mixed|null $defaultValue
   * @return \JSMF\Form
   **/
  public function addCheckbox(string $name, string $onValue="1", string $label=null, string $id=null, $defaultValue=null) :\JSMF\Form {
    return $this->addField($name, $label, $id, 'checkbox', [ $onValue ], $defaultValue);
  }

  
  /**
   * adds a radiobutton group
   * @param String $name
   * @param Array $options (key=radiobutton value, value=radiobutton label)
   * @param String|null $label
   * @param String|null $id (if not given, is set to $name)
   * @param Mixed|null $defaultValue
   * @return \JSMF\Form
   **/
  public function addRadiobutton(string $name, array $options, string $label=null, string $id=null, $defaultValue=null) :\JSMF\Form {
    return $this->addField($name, $label, $id, 'radiobutton', $options, $defaultValue);
  }

  
  /**
   * adds a textarea field
   * @param String $name
   * @param String|null $label
   * @param String|null $id (if not given, is set to $name)
   * @param Mixed|null $defaultValue
   * @return \JSMF\Form
   **/
  public function addTextarea(string $name, string $label=null, string $id=null, $defaultValue=null) :\JSMF\Form {
    return $this->addField($name, $label, $id, 'textarea', [], $defaultValue);
  }


  /**
   * sets label outputting on or off (default is on)
   * @param Boolean $option (true to output label, false to do not)
   * @return \JSMF\Form
   **/
  public function setOutputLabel(bool $option) {
    $this->_outputLabel=$option;
    return $this;
  }
  
  
  /**
   * return if this form was submitted
   * @return Boolean
   **/
  public function isSubmitted() :bool {
    if (Request::isPost() && \JSMF\Request::has('jsmf_form_name') && \JSMF\Request::getPost('jsmf_form_name') == $this->_formName) {
      return true;
    }
    return false;
  }

  /**
   * prints the html for a form element
   * @param String $name
   * @param array $attributes
   * @param DOMDocument|null $doc (if not given, a new document is created)
   * @param array $groupElementAttributes (for groups, such as radiobutton groups, selectbox options - 2-dimensional array, first dimension is the index of group element, sencond dimension the attributes)
   * @return String $html
   **/
  private function _printElement(string $name, array $attributes=[], \DOMDocument $doc=null, array $groupElementAttributes=[]) :string {
    if (isset($this->_formFields[$name])) {
      if ($doc === null) $doc = new \DOMDocument('1.0', 'utf-8');
      
      if (!empty($this->_formName) && !$this->_formIdFieldPrinted) {
        $formIdEl = new Element('input');
        $formIdEl->addAttributes([
          'type' => 'hidden',
          'name' => 'jsmf_form_name',
          'value' => $this->_formName,
        ]);
        $formIdEl->bindToDoc($doc);
        $this->_formIdFieldPrinted = true;
      }
      
      if ($this->_enableCsrfCheck && !$this->_csrfFieldPrinted) {
        $csrfEl = new Element('input');
        $csrfEl->addAttributes([
          'type' => 'hidden',
          'name' => $this->_csrfFieldName,
          'value' => $this->_csrfGenerate(),
        ]);
        $csrfEl->bindToDoc($doc);
        $this->_csrfFieldPrinted = true;
      }
      


      if ($this->_wrapperElement && empty($attributes['no-wrapper']) && $this->_formFields[$name]['type'] != 'hidden') {
        $wrapper = clone $this->_wrapperElement;

        // prepend label for all elements but checkbox
        if ($this->_outputLabel && $this->_formFields[$name]['type'] != 'checkbox' && !$this->_printLabelAfterElement) $this->_createLabel($name, $doc, ($this->_formFields[$name]['type']=='radiobutton'), $wrapper);
        $wrapper->appendChild($this->_formFields[$name]['element']);
        // append label for checkboxes
        if ($this->_outputLabel && ($this->_formFields[$name]['type'] == 'checkbox' || $this->_printLabelAfterElement)) $this->_createLabel($name, $doc, false, $wrapper);

        $wrapper->addClass('field-' . $name);

        $wrapper->bindToDoc($doc);
      } else {
        // prepend label for all elements but checkbox
        if ($this->_outputLabel && $this->_formFields[$name]['type'] != 'checkbox' && !$this->_printLabelAfterElement) $this->_createLabel($name, $doc, ($this->_formFields[$name]['type']=='radiobutton'));
        $this->_formFields[$name]['element']->bindToDoc($doc);
        // append label for checkboxes
        if ($this->_outputLabel && ($this->_formFields[$name]['type'] == 'checkbox' || $this->_printLabelAfterElement)) $this->_createLabel($name, $doc);
      }

      
      if (count($attributes)) $this->_formFields[$name]['element']->addAttributes($attributes);

      // call validator init methods (if exists, which will maybe modify the elements)
      if (isset($this->_formFields[$name]['validators'])) {
        foreach ($this->_formFields[$name]['validators'] as $validator) {
          if (method_exists($validator['class'], 'init')) {
            if ($this->_formFields[$name]['type'] == 'radiobutton') {
              // for radiobutton the element is only the container, we have to pass each child to the init
              $els=$this->_formFields[$name]['element']->getElementsByTagName('input');
              foreach ($els as $el) {
                call_user_func([$validator['class'], 'init'], $el);
              }
              
            } else {
              call_user_func([$validator['class'], 'init'], $this->_formFields[$name]['element']);
            }
          }
        }
      }

      // set groupElementAttributes
      switch ($this->_formFields[$name]['type']) {
        case 'radiobutton':
          $radios =  $this->_formFields[$name]['element']->getElementsByTagName('input');
          foreach ($radios as $index => $radio) {
            if (isset($groupElementAttributes[$index])) $radio->addAttributes($groupElementAttributes[$index]);
          }
          break;

        case 'select':
          foreach ($this->_formFields[$name]['element']->childNodes as $index => $option) {
            if (isset($groupElementAttributes[$index])) $option->addAttributes($groupElementAttributes[$index]);
          }
          break;
      }

      // prefill values
      if (!empty($this->_formData[$name])) {
        switch ($this->_formFields[$name]['type']) {
          case 'radiobutton':
            $radios =  $this->_formFields[$name]['element']->getElementsByTagName('input');
            foreach ($radios as $radio) {
              if ($radio->getAttribute('value') == $this->_formData[$name]) {
                $radio->setAttribute('checked', true);
              }
            }
            break;

          case 'checkbox':
            if ($this->_formData[$name] == $this->_formFields[$name]['element']->getAttribute('value')) {
              $this->_formFields[$name]['element']->addAttribute('checked', true);
            }
            break;

          case 'select':
            foreach ($this->_formFields[$name]['element']->childNodes as $option) {
              if ($option->getAttribute('value') == $this->_formData[$name]) {
                $attr=new \DOMAttr('selected');
                $attr->value=true;
                $option->appendChild($attr);
              }
            }
            break;

          case 'textarea':
            $this->_formFields[$name]['element']->addClass('has-value');
            $this->_formFields[$name]['element']->setValue($this->_formData[$name]);
            break;

          default:
            $this->_formFields[$name]['element']->addClass('has-value');
            $this->_formFields[$name]['element']->setAttribute('value', $this->_formData[$name]);
            break;
        }
      }

      return $doc->saveHTML();
    }

    return '';
  }


  /**
   * creates label element and appends to document
   * @param String $name
   * @param DOMDocument $doc
   * @param Boolean|false $asSpan
   * @param DOMNode|null $wrapper
   * @return Void
   */
  private function _createLabel(string $name, \DOMDocument $doc, bool $asSpan=false, \DOMNode $wrapper=null) {
    $label = new Element($asSpan ? 'span' : 'label');
    if ($asSpan) $label->addClass('label');
    else $label->addAttribute('for', $this->_formFields[$name]['id']);
    $label->appendChild(new \DOMText($this->_formFields[$name]['label']));
    
    if ($wrapper) {
      $wrapper->appendChild($label);
    } else {
      $label->bindToDoc($doc);
    }
  }


  /**
   * create div with the radiobuttons in it
   * @param String $name
   * @param String $id
   * @param Array $values
   * @return \JSMF\Form\Element
   **/
  private function _createRadioButtonGroup(string $name, string $id, array $values) :\JSMF\Form\Element {
    $group = new Element('div');
    $group->addClass('radiobutton-group');
    
    $containers=[];
    $els=[];
    $labels=[];
    foreach ($values as $key => $label) {
      $containers[$key] = new Element('div');
      $containers[$key]->addClass('radiobutton');

      $els[$key] = new Element('input');

      $els[$key]->addAttributes([
        'type' => 'radio',
        'value' => $key,
        'name' => $name,
        'id' => $id . '_' . $key,
      ]);
      
      $containers[$key]->appendChild($els[$key]);

      $labels[$key] = new Element('label');
      $labels[$key]->addAttribute('for', $id . '_' . $key);
      $labels[$key]->appendChild(new \DOMText($label));

      $containers[$key]->appendChild($labels[$key]);

      $group->appendChild($containers[$key]);
    }

    return $group;
  }
  
  
  /**
   * resets the form
   * @return Void
   **/
  public function reset() {
    $this->_formData=[];
  }


  /**
   * sets a fields value
   * @param String $field
   * @param Mixed $value
   * @return Void
   **/
  public function setValue(string $field, $value) {
    $this->_formData[$field] = $value;
  }

  /**
   * fill form data from post
   * @return Void
   **/
  private function _fillFromPost() {
    if (Request::isPost()) {
      // fill data from post
      foreach ($this->_formFields as $name => $field) {
        if (Request::has($name, 'POST')) {
          $this->_formData[$name] = Request::getPost($name); // is already sanitized by Request Class
        } else {
          $this->_formData[$name] = null;
        }
      }
    }
  }
  
  
  /**
   * returns if the form must validate (e.g. validators added or csrf check enabled)
   * @return Boolean $mustValidate
   **/
  private function _mustValidate() :bool {
    if ($this->_enableCsrfCheck) return true;
    
    $mustValidate = false;
    foreach ($this->_formFields as $field) {
      if (isset($field['validators']) && count($field['validators'])) {
        $mustValidate = true;
        break;
      }
    }
    return $mustValidate;
  }
  
 
  /**
   * generates csrf id and stores it into session
   * @return String $csrfId
   **/
  private function _csrfGenerate() :string {
    $token = uniqid('jsmf', true);
    Session::set($this->_csrfSessionKey, $token);
    return $token;
  }


  /**
   * Regenerates CSRF Token and returns the new token
   * @return string
   */
  public function regenerateCsrfToken() :string {
    $this->_csrfGenerate();

    return Session::get($this->_csrfSessionKey);
  }


  /**
   * Returns current form name
   * @return string
   */
  public function getFormName() :string {
    return $this->_formName;
  }
  
  /**
   * checks if csrf token found in session
   * @return Boolean $passed
   **/
  private function _csrfCheck() :bool {
    if (Request::has($this->_csrfFieldName, 'POST')) {
      if (Session::has($this->_csrfSessionKey . '_override')) {
        $token = Session::get($this->_csrfSessionKey . '_override');
        Session::delete($this->_csrfSessionKey . '_override');

      } else {
        $token = Session::get($this->_csrfSessionKey);
      }

      if ($token) {
        Session::delete($this->_csrfSessionKey);
        return Request::getPost($this->_csrfFieldName) == $token;    
      }
    }
    
    return false;
  }


  /**
   * Overrides crsf token. should be used with caution!
   * @param string $token
   */
  public function overrideCSRFToken(string $token) {
    Session::set($this->_csrfSessionKey . '_override', $token);
  }

}
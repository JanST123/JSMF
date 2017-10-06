<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Extends DOMElement
 **/

namespace JSMF\Form;
use JSMF\Exception;

class Element extends \DOMElement {
  private $_attributes=[];
  private $_addedAttributes=[];
  private $_classes=[];
  private $_childs=[];
  private $_value=null;
  private $_doc=null;


  /**
   * binds the element to DOMDocument, after that changes to the element were allowed
   * @param DOMDocument $doc
   * @return \JSMF\Form\Element
   **/
  public function bindToDoc(\DOMDocument &$doc) :\JSMF\Form\Element {
    $this->_doc=&$doc;
    $this->_doc->registerNodeClass('DOMElement', 'JSMF\\Form\\Element');
    $this->_doc->importNode($this, true);
    $this->_doc->appendChild($this);

    // apply all the changes
    foreach ($this->_childs as $child) {
      if ($child instanceof \JSMF\Form\Element) $child->bindToDoc($doc);
      $this->appendChild($child);
    }

    $this->_addAttributesDo();
    $this->_addClassesDo();
    $this->_setValueDo();


    return $this;

  }


  /**
   * appends child to element
   * @param DOMElement $node
   * @return \JSMF\Form\Element
   **/
  public function appendChild(\DOMNode $newChild) :\JSMF\Form\Element {
    if ($this->_doc) {
      parent::appendChild($newChild);

    } else {
      $this->_childs[] = $newChild;
    }
    return $this;
  }


  /**
   * adds more attributes to the element
   * @param Array $attributes
   * @return \JSMF\Form\Element
   **/
  public function addAttributes(array $attributes) :\JSMF\Form\Element {
    $this->_attributes = array_merge($this->_attributes, $attributes);

    if ($this->_doc) $this->_addAttributesDo();

    return $this;
  }


  /**
   * Helper function to create attr with value
   * @param String $name
   * @param Mixed $value
   * return \JSMF\Form\Element
   */
  public function addAttribute(string $name, $value) :\JSMF\Form\Element {
    $this->_attributes[$name] = $value;
    if ($this->_doc) $this->_addAttributesDo();
    return $this;
  }
  

  /**
   * returns true if an element attribute is set
   * @param String $name
   * @return Boolean $isset
   **/
  public function hasAttribute($name) {
    $attributes = $this->_doc ? $this->_addedAttributes : $this->_attributes;
    
    return isset($attributes[$name]);
  }


  /**
   * really add the attributes to the element (when DOMDocument is bound)
   **/
  private function _addAttributesDo() {
    foreach ($this->_attributes as $name => $value) {

      if ($name === 'class' && $this->hasAttribute('class')) {
        // special handling for class
        $cls=explode(' ', $this->getAttribute('class'));
        $cls = array_merge($cls, explode(' ', $value));
        $value = implode(' ', array_unique($cls));
      }

      $attr=new \DOMAttr($name);
      $attr->value=htmlentities($value, ENT_COMPAT, 'utf-8');
      $this->appendChild($attr);
      $this->_addedAttributes[$name] = $value;
      unset($this->_attributes[$name]);
    }
  }


  /**
   * add a class to the class attribute (if not exists)
   * @param String $name
   * @return \JSMF\Form\Element
   **/
  public function addClass(string $name) :\JSMF\Form\Element {
    $this->_classes[] = $name;
    if ($this->_doc) $this->_addClassesDo();

    return $this;
  }
  

  /**
   * adds a css class to the element
   * @return \JSMF\Form\Element
   */
  private function _addClassesDo() :\JSMF\Form\Element {
    foreach ($this->_classes as $name) {
      $classes=explode(' ', trim($this->getAttribute('class')));
    
      if (!in_array($name, $classes)) {
        $classes[]=$name;
      }
    
      $this->removeAttribute('class');
      $this->addAttribute('class', trim(implode(' ', $classes)), true);
      
      unset($this->_classes[$name]);
    }
    
    return $this;
  }


  /**
   * determines if element has a specific css class
   * @param String $name
   * @return Boolean
   */
  public function hasClass(string $name) :bool {
    if ($this->_doc) {
      $classes=explode(' ', trim($this->getAttribute('class')));
      return in_array($name, $classes);
    }
    return in_array($name, $this->_classes);
  }
  
  

  /**
   * returns the elements direct parent
   * @param String|null $tagname
   * @return DOMElement
   * @throws \JSMF\Exception
   */
  public function getParent(string $tagname=null) :\DOMElement {
    if (!$this->doc) throw new Exception('DOM Queries only available when element was bound to DOMDOcument');
    
    if (!$this->parentNode) return false;
    if ($tagname===null) return $this->parentNode;

    $el=false;
    $parent=$this;
    do {
      $parent=$parent->parentNode;
      if (property_exists($parent, 'tagName') && strtolower($parent->tagName) == strtolower($tagname)) {
        $el=$parent;
      }
    } while(!$el && $parent->parentNode);

    return $el;
  }


  /**
   * returns the elements direct parent by class
   * @param String $class
   * @return DOMElement
   * @throws \JSMF\Exception
   */
  public function getParentByClass(string $class) :\DOMElement {
    if (!$this->doc) throw new Exception('DOM Queries only available when element was bound to DOMDOcument');
    if (!$this->parentNode) return false;

    $parent=$this->parentNode;

    while($parent instanceof \JSMF\Form\Element) {
      if ($parent->hasClass($class)) {
        return $parent;
        break;
      }
      $parent=$parent->parentNode;
    }


    return false;
  }



  
  /**
   * Sets the elemnts value
   * @param String $sValue
   * @return \JSMF\Form\Element
   */
  public function setValue(string $sValue) :\JSMF\Form\Element{
    $this->_value = $sValue;
    if ($this->_doc) $this->_setValueDo();
    return $this;
  }
  
  
  private function _setValueDo() {
    if ($this->_value !== null) {
      $this->nodeValue='';
      $this->appendChild(new \DOMText($this->_value));
      $this->_value=null;
    }
  }
}
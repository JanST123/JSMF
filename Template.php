<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Template class
 **/

namespace JSMF;

class Template {
  private $_templateVars=Array();
  private $_templateFile=null;
  private $_layout=null;
  private $_disableLayout=false;
  private static $_templateDir='';
  private static $_minimize=false;


  /**
   * checks for a template variable
   * @param String $sName
   * @return Mixed variable content
   */
  public function __isset($sName) {
    return isset($this->_templateVars[$sName]);
  }
  /**
   * get a template variable
   * @param String $sName
   * @return Mixed variable content
   */
  public function __get($sName) {
    return (isset($this->_templateVars[$sName])?$this->_templateVars[$sName]:null);
  }
  /**
   * call a function assigned as template var
   * @param String $sName
   * @return Mixed variable content
   */
  public function __call($sName, $params) {
    if (isset($this->_templateVars[$sName]) && is_callable($this->_templateVars[$sName])) {
      return call_user_func_array($this->_templateVars[$sName], $params);
    }
    return null;
  }

  /**
   * set a template variable
   * @param String $sName
   * @param Mixed $sVal
   * @return Void
   */
  public function __set($sName, $sVal) {
    $this->_templateVars[$sName]=$sVal;
  }

  /**
   * prints out the template
   * @return String
   */
  public function __toString() {
    try {
      return $this->render();
    } catch (Exception $e) {
      // __toString method must never throw an exception, so do basic output here to always return a string
      Response::setException($e);
      return Response::output();
    } catch (\Exception $e) {
      return (String)$e;
    }
    return '';
  }

  /**
   * constructs a new template
   * @param String $sTemplateFile path to template file
   * @param Array|Array() $aTemplateVariables Variables to assign to the template
   * @return Void
   * @throws JSMF\Exception
   */
  public function __construct(string $sTemplateFile, array $aTemplateVariables=[]) {
    $this->_templateVars=$aTemplateVariables;
    $this->_templateFile=$sTemplateFile;
  }


  /**
   * set another template file to override the one given in constructor
   * @param string $sTemplateFile
   */
  public function setTemplateFile(string $sTemplateFile) {
    $this->_templateFile=$sTemplateFile;
  }


  /**
   * find the template file in template dir or fallbackdir, returns the found path if one found, otherwise throws exception
   * @param String $sTemplateFile (filename without extension)
   * @return String $foundTemplateFile
   * @throws \JSMF\Exception
   */
  private function _findTemplateFile(string $sTemplateFile) :string {
    if (strpos($sTemplateFile, '..') !== FALSE) {
      throw new Exception('Path upwalking not allowed: '.$sTemplateFile);
    }


    if (file_exists((strpos($sTemplateFile, '/') !== 0 ? self::$_templateDir : '') . $sTemplateFile.'.html.php')) {
      return (strpos($sTemplateFile, '/') !== 0 ? self::$_templateDir : '') . $sTemplateFile.'.html.php';
    } else {
      throw new Exception('Template '.(strpos($sTemplateFile, '/') !== 0 ? self::$_templateDir : '') . $sTemplateFile.'.html.php not found');
    }
  }


  /**
   * Translator
   * @param String $key
   * @param Array|Array() $vars
   * @return String Translation
   */
  protected function _(string $key, array $vars=[]) :string {
    if (Language::isTranslated($key)) {
      return Language::get($key,$vars);
    } else {
      return '++Not Translated: '.$key.'++';
    }
  }


  /**
   * escapes data for displaying in html
   * @param String $data
   * @return String
   **/
  protected function _escape($data) :string {
    return htmlspecialchars((String)$data);
  }


  /**
   * makes a link clickable (returns html)
   * @param String $text
   * @return String
   **/
  protected function _makeLinkClickable(string $text) :string {
    return preg_replace('/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&\/=]*)/', '<a href="$0" target="_blank">$0</a>', $text);
  }


  /**
   * returns all assigned template vars
   * @return Array
   */
  public function getTemplateVars() :array {
    return $this->_templateVars;
  }
  /**
   * sets template vars
   * @param Array $templateVars
   * @return Void
   */
  public function setTemplateVars(array $templateVars) {
    $this->_templateVars=$templateVars;
  }


  /**
   * renders a partial within current template
   * @param String $sTemplateFile
   * @param Array|Array() $aTemplateVariables
   * @return String template content
   */
  public function partial(string $sTemplateFile, array $aTemplateVariables=[]) :string {
    if (!isset($aTemplateVariables['parent'])) $aTemplateVariables['parent']=$this;
    $partial=new Template($sTemplateFile, $aTemplateVariables);
    $partial->disableLayout();
    return $partial;
  }


  /**
   * set the layout. If it is an URL, it is fetched by cURL
   * @param String $layoutFile (the full path to the layout file, but WITHOUT the file ending .html.php)
   * @param Int $cacheExpiration (when $path is url, the number of seconds the layout is cached in local filesystem)
   * @param String $cachePrefix
   * @return Void
   **/
  public function setLayout(string $layoutFile, int $cacheExpiration=null, string $cachePrefix='') {
    if ($layoutFile && file_exists($layoutFile . '.html.php')) {
      $this->_layout = new Template($layoutFile);
    }
  }


  /**
   * return the current layout template instance
   * @return \JSMF\Template
   **/
  public function getLayout(){
    return $this->_layout;
  }


  /**
   * returns if template has layout
   * @return bool
   **/
  public function hasLayout() :bool {
    return !empty($this->_layout) && $this->_layout instanceOf \JSMF\Template;
  }



  /**
   * renders given templatefile
   * @return String template output
   */
  protected function render() :string {
    // load template
    if (!preg_match('/\.html\.php$/', $this->_templateFile)) $this->_templateFile=$this->_findTemplateFile($this->_templateFile);

    // render
    ob_start();
    include $this->_templateFile;
    $output=ob_get_clean();


    if (self::$_minimize) {
      // minify html
      $output = preg_replace([
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/(\<\!\-\-.*\-\-\>)/sU' // Remove HTML comments
      ], [
        '>',
        '<',
        '\\1',
        ''
      ], $output);
    }



    if ($this->_layout && !$this->_disableLayout) {
      // create layout and attach template to it
      $this->_layout->contentVars = $this->_templateVars;
      $this->_layout->content=$output;
      $this->_layout->disableLayout();
      return $this->_layout->__toString();
    }
    return $output;
  }


  /**
   * disables/enables layout and then renders only the content
   * @param Boolean|true $bDisable
   * @return Void
   */
  public function disableLayout(bool $bDisable=true) {
    $this->_disableLayout=$bDisable;
  }


  /**
   * sets the directory in which templates are searched
   * @param String $dir
   * @return Void
   **/
  public static function setTemplateDir(string $dir) {
    self::$_templateDir=$dir;
  }

  /**
   * enables or disables output minimizing
   * @param bool $minimize
   */
  public static function setMinimizeOutput(bool $minimize=true) {
    self::$_minimize=$minimize;
  }
}

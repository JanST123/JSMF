<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Language class
 **/


namespace JSMF;

class Language extends \Exception {
  private static $_translations=[];
  private static $_currentLanguage='de';

  /**
   * returns the current active language
   * @return String
   **/
  public static function getCurrentLanguage() :string  {
    return self::$_currentLanguage;
  }

  /**
   * sets the current language
   **/
  public static function setLanguage(string $language) {
    self::$_currentLanguage=$language;
  }
  

  /**
   * load the given language files for the given languages
   * @param String $file (path and filename without extension)
   * @param String $language
   * @return Void
   **/
  public static function loadTranslations(String $file, string $language) {
    self::$_currentLanguage = $language;
    $languageFile = $file . '.' . $language . '.php';
    if (!file_exists($languageFile)) throw new Exception('Language File does not exist: ' . $languageFile);
//  if (!strstr(exec(PHP_BINDIR . '/php -l ' . escapeshellarg($languageFile) . ' 2>&1', $output), 'No syntax errors detected')) throw new Exception('Langage File contains errors: ' . $languageFile . ' ' . implode(" ", $output));

    include($languageFile);
    if (!isset($translation) || !is_array($translation)) throw new Exception('Language File does not set $translation Variable or $translation is not an array');

    if (Config::get('is_touch_device')) {
      if ($language==='de') {
        // replace 'click' and 'klick' with 'tipp'
        foreach ($translation as &$phrase) {
          $phrase=preg_replace('/(c|k)lick(e|st|en?)/', 'tipp$2', $phrase);
          $phrase=preg_replace('/(C|K)lick(e|st|en?)/', 'Tipp$2', $phrase);
        }
      } elseif($language==='en') {
        // replace 'click' by 'touch'
        foreach ($translation as &$phrase) {
          $phrase=preg_replace('/click(ing?)/', 'touch$1', $phrase);
          $phrase=preg_replace('/Click(ing?)/', 'Touch$1', $phrase);
        }
      }
    }

    self::$_translations = $translation;
  }


  /**
   * returns all translations
   * @return Array
   **/
  public static function getAll() {
    return self::$_translations;
  }


  /**
   * returns translation
   * @param String $key
   * @param Array $replaces
   * @return String
   **/
  public static function get(String $key, array $replaces=[]) :string {
    if (isset(self::$_translations[$key])) {
      return vsprintf(self::$_translations[$key], $replaces);
    }

    return '++ Missing translation for ' . self::getCurrentLanguage() . ':' . $key . '++';
  }
  
  
  /**
   * returns if a key is translated
   * @param String $key
   * @return Boolean
   **/
  public static function isTranslated(String $key): bool {
    return (!empty(self::$_translations[$key]));
  }


}

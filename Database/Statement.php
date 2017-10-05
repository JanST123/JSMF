<?php

namespace JSMF\Database;

class Statement extends \PDOStatement {
  private static $_lastParameters=null;
  private static $_lastQuery=null;



  protected function __construct() {
    // need this empty construct()!
  }

  /**
   * @param null $input_parameters
   * @return bool
   */
  public function execute($input_parameters = null) {
    self::$_lastParameters = $input_parameters;
    self::$_lastQuery = $this->queryString;

    return parent::execute($input_parameters);
  }


  public static function debugQuery($replaced=true)
  {
    $q = self::$_lastQuery;

    if (!$replaced) {
      return $q . "\n\n" . print_r(self::$_lastParameters, 1);
    }

    return preg_replace_callback('/(:[0-9a-z_]+)/i', 'self::_debugReplace', $q);
  }

  protected static function _debugReplace($m)
  {
    if (isset(self::$_lastParameters[$m[1]])) {
      $v = self::$_lastParameters[$m[1]];
      if ($v === null) {
        return "NULL";
      }
      if (!is_numeric($v)) {
        $v = str_replace("'", "''", $v);
      }

      return "'" . $v . "'";
    }
    return $m[0];
  }



}
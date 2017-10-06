<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Database class
 **/

namespace JSMF;


class Database extends \PDO {
  const FETCH_ALL=1;
  const FETCH_ONE=2;

  /**
   * @var \JSMF\Database
   * the class instance
   **/
  private static $_instance=null;




  /**
   * @return \JSMF\Database
   **/
  public static function getInstance() :\JSMF\Database {
    if (self::$_instance===null) {
      self::$_instance=new self();
    }
    return self::$_instance;
  }


  public function __construct() {
    if (!Config::has('pdo_dsn')) throw new Exception('Config Key pdo_dsn is missing');
    if (!Config::has('pdo_user')) throw new Exception('Config Key pdo_user is missing');
    if (!Config::has('pdo_pass')) throw new Exception('Config Key pdo_pass is missing');

    parent::__construct(Config::get('pdo_dsn'), Config::get('pdo_user'), Config::get('pdo_pass'), Config::get('pdo_options', []));
    $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, ['\\JSMF\\Database\\Statement', []]);

  }


  /**
   * reconnect
   */
  public static function reconnect() {
    self::$_instance=new self();
  }




  /**
   * init insert command
   * @param String $table
   * @param String|null $tableAlias
   * @param Boolean|false $ignore (do INSERT IGNORE)
   * @param Boolean|false $onDuplicateKeyUpdate
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function insertInto(string $table, string $tableAlias=null, bool $ignore=false, bool $onDuplicateKeyUpdate=false) :\JSMF\Database\Query {
    $query = new Database\Query($this, 'INSERT');
    $query->setTable($table, $tableAlias);

    if ($ignore) $query->addCommandExtra('IGNORE');
    if ($onDuplicateKeyUpdate) $query->addOnDuplicateKeyUpdate();

    return $query;
  }


  /**
   * init delete command
   * @param String $table
   * @param String|null $tableAlias
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function deleteFrom(string $table, string $tableAlias=null) :\JSMF\Database\Query {
    $query = new Database\Query($this, 'DELETE');
    $query->setTable($table, $tableAlias);

    return $query;
  }


  /**
   * init update command
   * @param String $table
   * @param String|null $tableALias
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function update(string $table, string $tableAlias=null) :\JSMF\Database\Query {
    $query = new Database\Query($this, 'UPDATE');
    $query->setTable($table, $tableAlias);

    return $query;
  }


  /**
   * init a select command, returns all rows (PDOStatement->fetchAll)
   * @param Array $fields
   * @param Boolean|false $distinct
   * @param Boolean|false $sqlNoCache
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function selectAllRows(array $fields, bool $distinct=false, bool $sqlNoCache=false) :\JSMF\Database\Query {
    $query = new Database\Query($this, 'SELECT', self::FETCH_ALL);
    $query->addFields($fields);
    if ($distinct) $query->addCommandExtra('DISTINCT');
    if ($sqlNoCache) $query->addCommandExtra('SQL_NO_CACHE');

    return $query;
  }


  /**
   * init a select command, returns one row (PDOStatement->fetch)
   * @param Array $fields
   * @param Boolean|false $distinct
   * @param Boolean|false $sqlNoCache
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function selectOneRow(array $fields, bool $distinct=false, bool $sqlNoCache=false) :\JSMF\Database\Query {
    $query = new Database\Query($this, 'SELECT', self::FETCH_ONE);
    $query->addFields($fields);
    if ($distinct) $query->addCommandExtra('DISTINCT');
    if ($sqlNoCache) $query->addCommandExtra('SQL_NO_CACHE');

    return $query;
  }

  


  /**
   * simple method for quoting identifiers, without name checking
   * @param String $identifier
   * @return String quoted identifier
   */
  public function quoteIdentifier(string $identifier) :string {
    if (preg_match('/^(.*)\.(.*)$/', $identifier)) {
      // database.table - do not quote database, but ensure only alphabetic characters where used
      $expl=explode('.', $identifier);
      if (preg_match('/^([a-z0-9]+)$/i', $expl[0])) {
        return $expl[0].'.'.$this->quoteIdentifier($expl[1]);
      }
    }

    return '`'.str_replace('`', '', $identifier).'`';
  }
}

<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Repository class
 **/

namespace JSMF;

class Repository {
  protected static $_table = null;
  protected static $_primaryKey = null;


  /**
   * check if everything is set we need to use the common repository methods
   * @throws \JSMF\Exception
   **/
  private static function _checkPrerequisites() {
    if (static::$_table === null) throw new Exception('protected $_table is not set in derived repository');
    if (static::$_primaryKey === null) throw new Exception('protected $_primaryKey is not set in derived repository');
  }


  /**
   * select data from table
   * @param mixed $primaryKeyValue
   * @param array $fields (empty array to select all fields)
   * @return Mixed Array $data or FALSE on failure
   * @throws \JSMF\Exception
   **/
  public static function select($primaryKeyValue, array $fields=[]) {
    self::_checkPrerequisites();
    $db = Database::getInstance();

    return $db->selectOneRow(empty($fields) ? ['*'] : $fields)
              ->from(static::$_table)
              ->where([ [ static::$_primaryKey, '=?']], [ $primaryKeyValue ])
              ->execute();
  }

  /**
   * counts datasets for primary key
   * @param Mixed $primaryKeyValue
   * @return int $count
   **/
  public static function count($primaryKeyValue) :int {
    self::_checkPrerequisites();
    $db = Database::getInstance();

    $row = $db->selectOneRow(['COUNT(*) as count'])
              ->from(static::$_table)
              ->where([ [ static::$_primaryKey, '=?']], [ $primaryKeyValue ])
              ->execute();
    return (int)$row['count'];
  }


  /**
   * updates a dataset
   * @param mixed $primaryKeyValue
   * @param array $data
   * @return boolean $success
   * @throws \JSMF\Exception
   **/
  public static function update($primaryKeyValue, array $data) :bool {
    self::_checkPrerequisites();
    $db = Database::getInstance();

    return $db->update(static::$_table)
              ->values($data)
              ->where([ [ static::$_primaryKey, '=?']], [ $primaryKeyValue ])
              ->execute();

  }


  /**
   * deletes a dataset
   * @param mixed $primaryKeyValue
   * @return boolean $success
   * @throws \JSMF\Exception
   **/
  public static function delete($primaryKeyValue) :bool {
    self::_checkPrerequisites();
    $db = Database::getInstance();

    return $db->deleteFrom(static::$_table)
      ->where([ [ static::$_primaryKey, '=?']], [ $primaryKeyValue ])
      ->execute();

  }


  /**
   * insert data into table, returns new id
   * @param array $data
   * @return int
   * @throws Exception
   */
  public static function insert(array $data) :int {
    self::_checkPrerequisites();
    $db = Database::getInstance();

    return $db->insertInto(static::$_table)
              ->values($data)
              ->execute();


  }


}
<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Database\Query class
 **/
 
namespace JSMF\Database;

use JSMF\Config;
use JSMF\Database;
use JSMF\Database\Type\DBFunction as TypeDBFunction;
use JSMF\Exception;
use JSMF\Session;

class Query {
  private $_tableInfoCache=[];

  private $_db;
  private $_command;
  private $_commandExtra=[];
  private $_extra=[];
  private $_table;
  private $_tableAlias;
  private $_tableAliases=[];
  private $_columnData=[];
  private $_fields=[];
  private $_joins=[];
  private $_joinFields=[];
  private $_params=[];
  private $_where=[];
  private $_orderBy=[];
  private $_groupBy=[];
  private $_fetchCount;
  private $_whereReplaceCount=0;
  private $_whereReplaceParams=null;
  private $_lastStmt=null;
  private $_collateTable=null;
  private $_collateTableField=null;
  private $_onDuplicateKeyUpdate=false;
  private $_lastQueryDuration=0;

  private static $_newSession=true;
  private static $_timeSpent=0;

  /**
   * creates new query
   * @param \JSMF\Database $database
   * @param String $command (INSERT,UPDATE,DELETE,SELECT)
   * @param Int|null $fetchCount (for select commands; fetch one row or all)
   **/
  public function __construct(\JSMF\Database $database, string $command, int $fetchCount=null) {
    $this->_db = $database;
    $this->_command = strtoupper($command);

    if ($fetchCount) $this->_fetchCount = $fetchCount;
    else $this->_fetchCount = Database::FETCH_ALL;
  }


  /**
   * returns the last PDOStatement if exists or null
   * @return \PDOStatement
   **/
  public function getLastStatement() :\PDOStatement {
    return $this->_lastStmt;
  }


  /**
   * adds the FROM clause to select commmands
   * @param String $table
   * @param String|null $tableAlias
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function from(string $table, string $tableAlias=null) :\JSMF\Database\Query {
    if ($this->getCommand() != 'SELECT')  { throw new Exception('from() method is only available for SELECT queries'); }

    $this->setTable($table, $tableAlias);

    return $this;
  }



  /**
   * add WHERE clause to select, update, delete commands
   * @param Array $queries
   * @param Array $params (the PDO params used in the queries)
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   *
   * @example for $queries:
   * [
   *  [ 'alias.fieldname', '=?'],
   *  'AND',
   *  [ 'alias.fieldname2', 'IS NOT NULL', 'OR', [
   *                                                ['alias.fieldname3', 'IS NULL'],
   *                                                'AND'
   *                                                ['alias.fieldname4', 'LIKE "%foo"']
   *                                             ]
   *  ],
   * ]
   *
   * This will result in a WHERE clause like:
   * WHERE (alias.fieldname=?) AND (alias.fieldname2 IS NOT NULL OR (alias.fieldname3 IS NULL AND alias.fieldname4 LIKE "%foo"))
   *
   * Queries can be nested as deep as needed by nesting the array too (example with fieldname3 and fieldname4)
   * Each part of the query that is an own array will be surrounded by brackets
   *
   * It looks more complicated than writing the query by hand, but with this technique the database class can ensure the correct COLLATION for each field
   **/
  public function where(array $queries, array $params=[]) :\JSMF\Database\Query {
    if (!in_array($this->getCommand(), ['SELECT', 'UPDATE', 'DELETE']))  { throw new Exception('where() method is only available for SELECT, UPDATE or DELETE queries'); }

    $this->addWhere($queries, $params);

    return $this;
  }


  /**
   *  adds order by (can be used multiple times)
   *  @param String $column
   *  @param String|ASC $direction
   *  @return \JSMF\Database\Query
   *  @throw JSMF\Exception
   **/
  public function orderBy(string $column, string $direction='ASC') :\JSMF\Database\Query {
    if (!in_array($this->getCommand(), ['SELECT']))  { throw new Exception('orderBy() method is only available for SELECT queries'); }

    $this->_addOrderBy($column, $direction);

    return $this;
  }


  /**
   *  adds group by (can be used multiple times)
   *  @param String $column
   *  @return \JSMF\Database\Query
   *  @throw JSMF\Exception
   **/
  public function groupBy(string $column) :\JSMF\Database\Query {
    if (!in_array($this->getCommand(), ['SELECT']))  { throw new Exception('groupBy() method is only available for SELECT queries'); }

    $this->_addGroupBy($column);

    return $this;
  }


  /**
   * set limit
   * @param Int $offset
   * @param Int $count
   *  @return \JSMF\Database\Query
   *  @throw JSMF\Exception
   **/
  public function limit(int $offset, int $count) :\JSMF\Database\Query {
    if (!in_array($this->getCommand(), ['SELECT', 'UPDATE', 'DELETE']))  { throw new Exception('limit() method is only available for SELECT, UPDATE and DELETE queries'); }

    $limit="LIMIT " . ($offset ? (int)$offset . ',' : '') . (int)$count;

    $this->_addExtra($limit);

    return $this;
  }


  /**
   * add JOIN
   * @param String $table
   * @param String $alias (Table Alias)
   * @param String $on (ON Command)
   * @param Array $fields (fields to select from the joined table)
   * @param Array $params (additional params when used in ON query)
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function join(string $table, string $tableAlias, string $on, array $fields = [], array $params = []) :\JSMF\Database\Query {
    $this->_addJoin($table, $tableAlias, '', $on, $fields, $params);

    return $this;
  }

  /**
   * add LEFT JOIN
   * @param String $table
   * @param String $alias (Table Alias)
   * @param String $on (ON Command)
   * @param Array $fields (fields to select from the joined table)
   * @param Array $params (additional params when used in ON query)
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function leftJoin(string $table, string $tableAlias, string $on, array $fields = [], array $params = []) :\JSMF\Database\Query {
    $this->_addJoin($table, $tableAlias, 'LEFT', $on, $fields, $params);

    return $this;
  }


  /**
   * add INNER JOIN
   * @param String $table
   * @param String $alias (Table Alias)
   * @param String $on (ON Command)
   * @param Array $fields (fields to select from the joined table)
   * @param Array $params (additional params when used in ON query)
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function innerJoin(string $table, string $tableAlias, string $on, array $fields = [], array $params = []) :\JSMF\Database\Query {
    $this->_addJoin($table, $tableAlias, 'INNER', $on, $fields, $params);

    return $this;
  }


  /**
   * set column data for INSERT or UPDATE commands
   * @param String $key
   * @param mixed $value
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function set(string $key, $value) :\JSMF\Database\Query {
    if (!in_array($this->getCommand(), ['INSERT', 'UPDATE']))  { throw new Exception('set() method is only available for INSERT or UPDATE queries'); }

    $this->_addColumnData([$key => $value]);

    return $this;
  }


  /**
   * set column data for INSERT or UPDATE commands
   * @param Array $data (key=>value)
   * @return \JSMF\Database\Query
   * @throw JSMF\Exception
   **/
  public function values(array $data) :\JSMF\Database\Query {
    if (!in_array($this->getCommand(), ['INSERT', 'UPDATE']))  { throw new Exception('values() method is only available for INSERT or UPDATE queries'); }

    $this->_addColumnData($data);

    return $this;
  }



  /**
   * set the table where the query should work on
   * @param String $table (database.tablename)
   * @param String|null $tableAlias (optional alias, recommended)
   * @return Void
   * @throw JSMF\Exception
   **/
  public function setTable(string $table, string $tableAlias=null) {
    $this->_table = $table;
    $this->_getTableInfo($table);

    if ($tableAlias) {
      if (isset($this->_tableAliases[$tableAlias])) throw new Exception('Duplicate Table Alias' . $tableAlias);
      $this->_tableAlias = $tableAlias;
      $this->_tableAliases[$tableAlias] = $table;
    }
  }


  /**
   * get the current query command
   * @return String $command (INSERT, UPDATE, SELECT or DELETE)
   **/
  public function getCommand() :string {
    return $this->_command;
  }


  /**
   * add a WHERE clause
   * @param Array $queries (for example, see doc-comment in JSMF\Database::where())
   * @param Array $params (the PDO params used in the queries)
   * @return Void
   * @throw JSMF\Exception
   **/
  public function addWhere(array $queries, array $params=[]) {
    if (isset($queries[0]) && !is_array($queries[0])) $queries = [ $queries ];

    $this->_whereReplaceCount=count($this->_params);
    $this->_whereReplaceParams=$params;

    $this->_where[] = $this->_addWhereDo($queries);

    $this->_addParams($this->_whereReplaceParams);
  }


  /**
   * parse the WHERE quries, calls itself recursively for nested queries
   * @param array $queries
   * @return String $query
   **/
  private function _addWhereDo(array $queries) :string {
    $return=[];
    foreach ($queries as $query) {
      if (is_scalar($query)) {
        // is operator
        $return[] = $query;
      } else {
        // is query
        $field=$operation=$nestedQueryOperator=$nestedQuery = null;
        if (count($query) > 2) {
          list($field, $operation, $nestedQueryOperator, $nestedQuery) = $query;
        } elseif (count($query) > 1) {
          list($field, $operation) = $query;
        } else {
          throw new Exception('Query must be divided in field and operation ' . print_r($query, 1));
        }

        // replace unnamed params with named params cause we merge them all together here
        $operation = preg_replace_callback('/\?/', [$this, '_whereReplaceParams'], $operation, -1, $this->_whereReplaceCount);

        // divide field into table and field, resolve table alias
        $table = $this->_table;
        $tableField = $field;

        // check if the field is a function call, extract the field(s) from the function call
        $matches=[];
        if (preg_match('/^([a-zA-Z0-9_]+)\s?\(([a-zA-Z0-9_\-\.,]+)\)$/', $tableField, $matches)) {
          if (!empty($matches[2])) {
            $function = $matches[1];
            $tmpFields=explode(',', $matches[2]);
            $lastCollation=null;
            foreach($tmpFields as $tmpField) {
              $tmpField = trim($tmpField);
              
              // check if field is alias, and resolve the alias
              $tmpTable=$this->_table;
              if (preg_match('/^([a-z]+)\.([a-zA-Z0-9_\-]+)$/', $tmpField, $tmpMatches)) {
                if (isset($this->_tableAliases[$tmpMatches[1]])) {
                  $tmpTable = $this->_tableAliases[$tmpMatches[1]];
                  $tmpField = $tmpMatches[2];
                }
              }

              // check if all fields have the same collation, if not throw exception
              $collation = $this->_getCollation($tmpTable, $tmpField);

              if ($lastCollation === null) $lastCollation = $collation;
              if ($collation != $lastCollation) {
                throw new Exception('The fields ' . $matches[2] . ' were used together in a ' . $function . ' Function but do have different collations (' . $lastCollation . ', ' . $collation . '). This will lead to crappy results!');
              }
            }

            // set field to first field, cause we have ensured they have the same collation
            $tableField = trim($tmpFields[0]);
          }
        }


        $matches=[];
        if (preg_match('/^([a-z]+)\.([a-zA-Z0-9_\-]+)$/', $tableField, $matches)) {
          if (isset($this->_tableAliases[$matches[1]])) {
            $table = $this->_tableAliases[$matches[1]];
            $tableField = $matches[2];
          }
        }

        // for each param check if the collation is not utf8 and collate them then
        $this->_collateTable = $table;
        $this->_collateTableField = $tableField;
        
        $operation = preg_replace_callback('/:([a-zA-Z0-9_]+)/', [$this, '_collate' ], $operation);

        if ($nestedQuery) {
          $operation .= "\n" . $nestedQueryOperator . ' ' . $this->_addWhereDo($nestedQuery);
        }

        $return[]='(' . $field . ' ' . $operation . ')';
      }
    }

    return implode("\n", $return);
  }


  /**
   * add a JOIN clause
   * @param String $table
   * @param String $tableAlias
   * @param String $joinType (LEFT, RIGHT, INNER....)
   * @param String $on (ON clause)
   * @param Array|null $fields
   * @param Array|null $params
   * @return Void
   * @throws \JSMF\Exception
   **/
  private function _addJoin(string $table, string $tableAlias, string $joinType, string $on, array $fields=null, array $params=null) {
    $this->_joins[$tableAlias]=$joinType . " JOIN " . $table . " AS " . $this->_db->quoteIdentifier($tableAlias) . " ON ". $on;

    if (isset($this->_tableAliases[$tableAlias])) throw new Exception('Duplicate Table Alias for JOIN' . $tableAlias);
    $this->_tableAliases[$tableAlias] = $table;

    if (is_array($fields)) {
      $this->_joinFields[$tableAlias] = $fields;
    }

    if (is_array($params)) {
      $this->_addParams($params);
    }
  }


  /**
   * adds fields for select
   * @param Array $fields
   **/
  public function addFields(array $fields) {
    if (!is_array($this->_fields)) $this->_fields = [];

    $this->_fields = array_merge($fields, $this->_fields);
  }


  /**
   * add column values for insert, update
   * @param Array $data
   **/
  private function _addColumnData(array $data) {
    if (!is_array($this->_columnData)) $this->_columnData = [];

    $this->_columnData = array_merge($this->_columnData, $data);
  }


  /**
   * adds order BY clause(s)
   * @param String $column
   * @param String|ASC $direction
   * @return Void
   **/
  private function _addOrderBy(string $column, string $direction='ASC') {
    if (!is_array($this->_orderBy)) $this->_orderBy = [];

    if (!in_array($direction, ['ASC', 'DESC'])) $direction = 'ASC';

    $this->_orderBy[] = $this->_db->quoteIdentifier($column) . ' ' . $direction;
  }


  /**
   * adds group BY clause(s)
   * @param String $column
   * @return Void
   **/
  private function _addGroupBy(string $column) {
    if (!is_array($this->_groupBy)) $this->_groupBy = [];

    $this->_groupBy[] = $this->_db->quoteIdentifier($column);
  }

  /**
   * extra to append to end of query
   * @param String $extra
   **/
  private function _addExtra(string $extra) {
    if (!is_array($this->_extra)) $this->_extra = [];

    $this->_extra[] = $extra;
  }


  /**
   * extra to append after command (e.g. for DISTINCT or SQL_NO_CACHE)
   * @param String $extra
   **/
  public function addCommandExtra(string $commandExtra) {
    if (!is_array($this->_commandExtra)) $this->_commandExtra =[];

    $this->_commandExtra[] = $commandExtra;
  }
  
  
    /**
   * appends the "on duplicate key update ..." phrase to the insert query
   **/
  public function addOnDuplicateKeyUpdate() {
    $this->_onDuplicateKeyUpdate=true;
  }

  
  /**
   * builds the "on duplicate key update ...." query
   **/
  private function _onDuplicateKeyUpdate() {
    if (!is_array($this->_extra)) $this->_extra =[];

    $query = 'ON DUPLICATE KEY UPDATE ';
    $first=true;
    foreach ($this->_columnData as $key => $value) {
      $param = ':' . $key;
      $query .= (!$first ? ',' : '') . $this->_db->quoteIdentifier($key) . '=' . $param;
      $first=false;
    }

    $this->_extra[] = $query;
  }


  /**
   * executes the prepared query
   * @return Boolean|Mixed $result (Data on selects, insert ID on INSERT, boolean success on other)
   * @throws \JSMF\Exception
   ***/
  public function execute() {
    try {
      $query = null;
      $params = [];
      switch ($this->_command) {
        case 'INSERT':
          if ($this->_onDuplicateKeyUpdate) {
            $this->_onDuplicateKeyUpdate();
          }

          $query = "INSERT " . (!empty($this->_commandExtra) ? implode(' ',
                $this->_commandExtra) . ' ' : '') . " INTO " . $this->_table . "
                           (" . implode(',', $this->_getColumnNames()) . ")
                           VALUES (" . implode(',', $this->_getColumnNamesAsVariables()) . ")" .
            ($this->_extra ? ' ' . implode("\n", $this->_extra) : '');
          $params = $this->_getColumnData();
          break;

        case 'UPDATE':
          $query = "UPDATE " . (!empty($this->_commandExtra) ? implode(' ',
                $this->_commandExtra) . ' ' : '') . $this->_table . " SET ";
          $first = true;
          foreach ($this->_columnData as $key => $value) {
            $param = ':' . $key;
            $params[$param] = $value;
            $query .= (!$first ? ',' : '') . $this->_db->quoteIdentifier($key) . '=' . $param;
            $first = false;
          }
          $query .= "\n" . implode("\n", $this->_joins) . "
                     WHERE " . implode("\n", $this->_where)
            . ($this->_extra ? "\n" . implode("\n", $this->_extra) : '');

          break;

        case 'DELETE':
          $query = "DELETE " . $this->_db->quoteIdentifier($this->_table) . " " . (!empty($this->_commandExtra) ? implode(' ',
                $this->_commandExtra) . ' ' : '') . " FROM " . $this->_table . "
                   " . implode("\n", $this->_joins) . "
                    WHERE " . implode("\n", $this->_where)
            . ($this->_extra ? "\n" . implode("\n", $this->_extra) : '');

          break;

        case 'SELECT':
          $query = "SELECT " . (!empty($this->_commandExtra) ? implode(' ', $this->_commandExtra) . ' ' : '')
            . implode(',', $this->_getSelectFields()) . "
                          FROM " . $this->_table . ($this->_tableAlias ? " AS " . $this->_db->quoteIdentifier($this->_tableAlias) : "") . "
                          " . implode("\n", $this->_joins) . "
                          " . (!empty($this->_where) ? "WHERE " . implode("\n", $this->_where) : "")
            . ($this->_groupBy ? "\nGROUP BY " . implode(", ", $this->_groupBy) : '')
            . ($this->_orderBy ? "\nORDER BY " . implode(", ", $this->_orderBy) : '')
            . ($this->_extra ? "\n" . implode("\n", $this->_extra) : '');


          break;

        default:
          throw new Exception('Unsupported command. Use INSERT,UPDATE,DELETE,SELECT');
      }


      if ($query) {
        $params = array_merge($this->_params, $params);

        $this->_debug('Executing Query: ' . $query . ' with params ' . json_encode($params));


        $benchStart = microtime(true);
        $this->_lastStmt = $this->_db->prepare($query);
        $result = $this->_lastStmt->execute($params);

        if ($this->_command == 'SELECT') {
          $this->_lastQueryDuration = microtime(true) - $benchStart;
          $this->_explain($query, $params);

          switch ($this->_fetchCount) {
            case Database::FETCH_ONE:
              $result = $this->_lastStmt->fetch(\PDO::FETCH_ASSOC);
              break;

            case Database::FETCH_ALL:
              $result = $this->_lastStmt->fetchAll(\PDO::FETCH_ASSOC);
              break;
          }
        } elseif ($this->_command == 'INSERT' && $result) {
          // on insert commands check if the primary key has the autoincrement extra and then return the last insert id
          $tableInfo = $this->_getTableInfo($this->_table);
          // check if autoincrement
          foreach ($tableInfo as $col => $info) {
            if (isset($info['Key']) && $info['Key'] == 'PRI' &&
              isset($info['Extra']) && $info['Extra'] == 'auto_increment'
            ) {
              $result = $this->_db->lastInsertId();
              break;
            }
          }
        }

        return $result;
      }
    } catch(\PDOException $e) {

      throw new Exception("DB Exception on Query:\n\n " . Statement::debugQuery() . "\n\n"  . $e->getMessage(), 0, $e);
    }

    return false;
  }


  /**
   * collates a field for operations if the field is not utf-8 (callback for preg_replace_callback)
   * @param Array $matches
   * @return String $collatedParam
   **/
  private function _collate(array $matches) :string {
    $param = ':' . $matches[1];
    
    $collation = $this->_getCollation($this->_collateTable, $this->_collateTableField);
    if (!empty($collation) && strpos($collation, 'utf8') !== 0) {
      if (strpos($collation, 'latin1') === 0){
        if (isset($this->_params[$param])) {
          $this->_params[$param] = utf8_decode($this->_params[$param]);
        } elseif(isset($this->_whereReplaceParams[$param])) {
          $this->_whereReplaceParams[$param] = utf8_decode($this->_whereReplaceParams[$param]);
        }
      } else {
        throw new Exception('Unknown collation, cannot recode value: ' . $collation . ' for field ' . $this->_collateTableField);
      }

      return '_' . explode('_', $collation)[0] . ' ' . $param . ' COLLATE ' . $collation;
    }

    return $param;
  }
  
  
  /**
   * get the table collation
   * @param String $table
   * @param String $field
   * @return String $collation or null if not found
   **/
  private function _getCollation(string $table, string $field)  {
    $tableInfo = $this->_getTableInfo($table);
    
    if (isset($tableInfo[$field])
        && isset($tableInfo[$field]['Collation'])
        && !empty($tableInfo[$field]['Collation'])) {
      return $tableInfo[$field]['Collation'];
    }
    return null;
  }


  /**
   * return the field string for SELECT command
   * @return Array
   **/
  private function _getSelectFields() :array {
    $return=[];

    // first the main fields
    foreach($this->_fields as $field) {
      if (strpos($field, '.') === FALSE && !preg_match('/^[a-zA-Z0-9_]+\((.*)\)/', $field)) {
        $return[] = ($this->_tableAlias ? $this->_tableAlias : $this->_table) . '.' . $field;
      } else {
        $return[] = $field;
      }
    }
    
    // then the join fields
    foreach($this->_joins as $tableAlias => $joinQuery) {
      foreach($this->_joinFields[$tableAlias] as $field) {
        $return[] = (strpos($field, '.') === FALSE && !preg_match('/^[a-zA-Z0-9_]+\((.*)\)/', $field) ? $tableAlias . '.' : '') . $field;
      }
    }

    return $return;
  }


  /**
   * returns the column names of $_columnData
   * @return Array
   **/
  private function _getColumnNames() :array {
    $names=[];
    foreach ($this->_columnData as $key => $val) {
      $names[]=$this->_db->quoteIdentifier($key);
    }
    return $names;
  }


  /**
   * returns if a value is a DB function call
   * @param string $val
   * @return bool
   */
  private function _isValueFunctionCall($val) :bool {
    if ($val instanceof TypeDBFunction) {
      return true;

    } else if(preg_match('/^([A-Za-z0-9_]+)\(\)/', $val, $valMatches) && in_array($valMatches[1], TypeDBFunction::$mySQLFunctions)) {
      // this is a mySQL function but the detection is legacy, so log a warning
      $trace=debug_backtrace();
      $fileLine='';

      foreach ($trace as $t) {
        if (!isset($t['file']) || !isset($t['class']) || !isset($t['function'])) continue;

        if ($t['function'] == 'execute' && $t['class'] == 'JSMF\Database\Query') {
          $fileLine=' Set in File ' . $t['file'] . ' (' . $t['line'] . ')';
          break;
        }
      }
       trigger_error('Database Value ' . $val . ' was interpreted as MySQL function and is not passed as prepared statement parameter. This is deprecated. Use Instance of JSMF\Database\Type\DBFunction to declare values as DBFunction!' . $fileLine, E_USER_WARNING);
      return true;
    }

    return false;
  }


  /**
   * returns the column names of $_columnData as variable placeholders for prepared statement
   * @return Array
   **/
  private function _getColumnNamesAsVariables() :array {
    $names=[];
    foreach ($this->_columnData as $key => $val) {
      if ($this->_isValueFunctionCall($val)) {
        $names[] = $val;

      } else {
        $names[]=':' . $key;
      }
    }
    return $names;
  }


  /**
   * returns column data for execute prepared statement
   * @return Array
   **/
  private function _getColumnData() :array {
    $return=[];
    foreach ($this->_columnData as $k => $v) {
      if (!$this->_isValueFunctionCall($v)) {
        $return[':' . $k] = $v;
      }
    }

    return $return;
  }


  /**
   * returns all columns for a table
   * @param String $table (database.table)
   * @return Array
   **/
  private function _getTableInfo(string $table) :array {
    if (!isset($this->_tableInfoCache[$table])) {
      $this->_tableInfoCache[$table]=Array();
      $res=$this->_db->query('SHOW FULL COLUMNS FROM ' . $table);
      if ($res) {
        $rows=$res->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
          $this->_tableInfoCache[$table][$row['Field']]=$row;
        }
      }
    }

    return $this->_tableInfoCache[$table];
  }


  /**
   * returns for a query if it has enough information to execute
   * @return Boolean $canExecute
   * @throws \JSMF\Exception
   **/
  private function _queryCanExecute() :bool {
    $missing=[];

    switch ($this->_command) {
      case 'INSERT':
        if (!empty($this->_columnData)) return true;
        else $missing[]='Column Data';
        break;

      case 'UPDATE':
        if (!empty($this->_columnData)) {
          if (empty($this->_where)) {
            throw new Exception('No Update without WHERE allowed! If you really want to update all, use "WHERE 1"');
          }
          return true;
        }
        else $missing[]='Column Data';
        break;

      case 'DELETE':
        if (!empty($this->_where)) return true;
        else throw new Exception('No Delete without WHERE allowed! If you really want to delete all, use "WHERE 1"');
        break;

      case 'SELECT':
        if (!empty($this->_table)) return true;
        else $missing[]='From Table';
        break;

      default:
        throw new Exception('Unsupported command. Use INSERT,UPDATE,DELETE,SELECT');

    }

    if (count($missing)) {
      throw new Exception('Query is not ready to execute yet. ' . implode($missing) . ' is missing');
    }
  }


  /**
   * replaces numeric placeholders (?) in a where query with named placeholders (callback for preg_replace_callback)
   * @param Array $matches
   * @return String
   **/
  private function _whereReplaceParams(array $matches) :string {
    if (isset($this->_whereReplaceParams[$this->_whereReplaceCount])) {
      $this->_whereReplaceParams[':param' . $this->_whereReplaceCount] = $this->_whereReplaceParams[$this->_whereReplaceCount];
      unset($this->_whereReplaceParams[$this->_whereReplaceCount]);
    }

    return ':param' . $this->_whereReplaceCount;
  }


  /**
   * adds PDO params
   * @param array $params
   * @return Void
   * @throw JSMF\Exception
   **/
  private function _addParams(array $params) {
    // check if all param keys where numeric or start with ':'
    foreach ($params as $key => $value) {
      if (!is_numeric($key) && strpos($key, ':') !== 0) {
        throw new Exception('Parameter ' . $key . ' is invalid. Parameter Names must be numeric or start with a ":"');
      }
    }

    $this->_params = array_merge($params, $this->_params);
  }


  private function _debug($str) {
//    echo "\n<br />====================================================\n<br />" . date('Y-m-d H:i:s') . " " . $str;
  }


  /**
   * log query explanations
   * @param string $query
   * @param array $params
   */
  private function _explain(string $query, array $params) {
    if (Config::get('explain_querys')) {
      $logFile = SRC . '/log/explain.log';

      if (self::$_newSession) {
        self::$_newSession=false;
        file_put_contents($logFile, "\n\n============= NEW SESSION ===========\n\n", FILE_APPEND);
      }



      $queryFlat=str_replace("\n", " ", $query);
      while(strstr($queryFlat, "  ")) $queryFlat=str_replace("  ", " ", $queryFlat);

      file_put_contents($logFile, "\n\n" . date('c') . ' Explaining ' . $queryFlat, FILE_APPEND);


      try {
        $stmt = $this->_db->prepare('EXPLAIN ' . $query);
        $stmt->execute($params);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        $txt = $row['select_type'] . ' Query. Possible Keys: ' . $row['possible_keys'] . ', Using ' . $row['key'] . '(' . $row['key_len'] . '). Searched ' . $row['rows'] . 'Rows, Ref: ' . $row['ref'];
        if (!empty($row['Extra'])) {
          $txt .= "\nExtra: " . $row['Extra'];
        }

        file_put_contents($logFile, "\n" . $txt, FILE_APPEND);
      } catch(\Exception $e) {
        file_put_contents($logFile, "\n" . 'Could not explain: ' . $e->getMessage(), FILE_APPEND);
      }
      file_put_contents($logFile, "\nQuery took: " . round($this->_lastQueryDuration * 1000,4) . 'ms', FILE_APPEND);
      self::$_timeSpent+=$this->_lastQueryDuration;
      file_put_contents($logFile, "\nAll queries took: " . round(self::$_timeSpent * 1000,4) . 'ms', FILE_APPEND);
    }
  }

}

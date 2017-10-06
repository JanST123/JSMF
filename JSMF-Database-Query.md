JSMF\Database\Query
===============






* Class name: Query
* Namespace: JSMF\Database







Methods
-------


### __construct

    mixed JSMF\Database\Query::__construct(\JSMF\Database $database, String $command, Int|null $fetchCount)

creates new query



* Visibility: **public**


#### Arguments
* $database **[JSMF\Database](JSMF-Database.md)**
* $command **String** - &lt;p&gt;(INSERT,UPDATE,DELETE,SELECT)&lt;/p&gt;
* $fetchCount **Int|null** - &lt;p&gt;(for select commands; fetch one row or all)&lt;/p&gt;



### getLastStatement

    \PDOStatement JSMF\Database\Query::getLastStatement()

returns the last PDOStatement if exists or null



* Visibility: **public**




### from

    \JSMF\Database\Query JSMF\Database\Query::from(String $table, String|null $tableAlias)

adds the FROM clause to select commmands



* Visibility: **public**


#### Arguments
* $table **String**
* $tableAlias **String|null**



### where

    \JSMF\Database\Query JSMF\Database\Query::where(Array $queries, Array $params)

add WHERE clause to select, update, delete commands



* Visibility: **public**


#### Arguments
* $queries **Array**
* $params **Array** - &lt;p&gt;(the PDO params used in the queries)&lt;/p&gt;



### orderBy

    mixed JSMF\Database\Query::orderBy(\JSMF\Database\string $column, String|\JSMF\Database\ASC $direction)

adds order by (can be used multiple times)

@param String $column

* Visibility: **public**


#### Arguments
* $column **JSMF\Database\string**
* $direction **String|JSMF\Database\ASC** - &lt;p&gt;@return \JSMF\Database\Query
@throw JSMF\Exception&lt;/p&gt;



### groupBy

    \JSMF\Database\Query JSMF\Database\Query::groupBy(\JSMF\Database\string $column)

adds group by (can be used multiple times)

@param String $column

* Visibility: **public**


#### Arguments
* $column **JSMF\Database\string**



### limit

    mixed JSMF\Database\Query::limit(Int $offset, Int $count)

set limit



* Visibility: **public**


#### Arguments
* $offset **Int**
* $count **Int** - &lt;p&gt;@return \JSMF\Database\Query
@throw JSMF\Exception&lt;/p&gt;



### join

    \JSMF\Database\Query JSMF\Database\Query::join(String $table, \JSMF\Database\string $tableAlias, String $on, Array $fields, Array $params)

add JOIN



* Visibility: **public**


#### Arguments
* $table **String**
* $tableAlias **JSMF\Database\string**
* $on **String** - &lt;p&gt;(ON Command)&lt;/p&gt;
* $fields **Array** - &lt;p&gt;(fields to select from the joined table)&lt;/p&gt;
* $params **Array** - &lt;p&gt;(additional params when used in ON query)&lt;/p&gt;



### leftJoin

    \JSMF\Database\Query JSMF\Database\Query::leftJoin(String $table, \JSMF\Database\string $tableAlias, String $on, Array $fields, Array $params)

add LEFT JOIN



* Visibility: **public**


#### Arguments
* $table **String**
* $tableAlias **JSMF\Database\string**
* $on **String** - &lt;p&gt;(ON Command)&lt;/p&gt;
* $fields **Array** - &lt;p&gt;(fields to select from the joined table)&lt;/p&gt;
* $params **Array** - &lt;p&gt;(additional params when used in ON query)&lt;/p&gt;



### innerJoin

    \JSMF\Database\Query JSMF\Database\Query::innerJoin(String $table, \JSMF\Database\string $tableAlias, String $on, Array $fields, Array $params)

add INNER JOIN



* Visibility: **public**


#### Arguments
* $table **String**
* $tableAlias **JSMF\Database\string**
* $on **String** - &lt;p&gt;(ON Command)&lt;/p&gt;
* $fields **Array** - &lt;p&gt;(fields to select from the joined table)&lt;/p&gt;
* $params **Array** - &lt;p&gt;(additional params when used in ON query)&lt;/p&gt;



### set

    \JSMF\Database\Query JSMF\Database\Query::set(String $key, mixed $value)

set column data for INSERT or UPDATE commands



* Visibility: **public**


#### Arguments
* $key **String**
* $value **mixed**



### values

    \JSMF\Database\Query JSMF\Database\Query::values(Array $data)

set column data for INSERT or UPDATE commands



* Visibility: **public**


#### Arguments
* $data **Array** - &lt;p&gt;(key=&gt;value)&lt;/p&gt;



### setTable

    Void JSMF\Database\Query::setTable(String $table, String|null $tableAlias)

set the table where the query should work on



* Visibility: **public**


#### Arguments
* $table **String** - &lt;p&gt;(database.tablename)&lt;/p&gt;
* $tableAlias **String|null** - &lt;p&gt;(optional alias, recommended)&lt;/p&gt;



### getCommand

    String JSMF\Database\Query::getCommand()

get the current query command



* Visibility: **public**




### addWhere

    Void JSMF\Database\Query::addWhere(Array $queries, Array $params)

add a WHERE clause



* Visibility: **public**


#### Arguments
* $queries **Array** - &lt;p&gt;(for example, see doc-comment in JSMF\Database::where())&lt;/p&gt;
* $params **Array** - &lt;p&gt;(the PDO params used in the queries)&lt;/p&gt;



### addFields

    mixed JSMF\Database\Query::addFields(Array $fields)

adds fields for select



* Visibility: **public**


#### Arguments
* $fields **Array**



### addCommandExtra

    mixed JSMF\Database\Query::addCommandExtra(\JSMF\Database\string $commandExtra)

extra to append after command (e.g. for DISTINCT or SQL_NO_CACHE)



* Visibility: **public**


#### Arguments
* $commandExtra **JSMF\Database\string**



### addOnDuplicateKeyUpdate

    mixed JSMF\Database\Query::addOnDuplicateKeyUpdate()

appends the "on duplicate key update .

.." phrase to the insert query

* Visibility: **public**




### execute

    Boolean|Mixed JSMF\Database\Query::execute()

executes the prepared query



* Visibility: **public**




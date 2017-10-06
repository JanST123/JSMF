JSMF\Database
===============






* Class name: Database
* Namespace: JSMF
* Parent class: PDO



Constants
----------


### FETCH_ALL

    const FETCH_ALL = 1





### FETCH_ONE

    const FETCH_ONE = 2







Methods
-------


### getInstance

    \JSMF\Database JSMF\Database::getInstance()





* Visibility: **public**
* This method is **static**.




### __construct

    mixed JSMF\Database::__construct()





* Visibility: **public**




### reconnect

    mixed JSMF\Database::reconnect()

reconnect



* Visibility: **public**
* This method is **static**.




### insertInto

    \JSMF\Database\Query JSMF\Database::insertInto(String $table, String|null $tableAlias, Boolean|false $ignore, Boolean|false $onDuplicateKeyUpdate)

init insert command



* Visibility: **public**


#### Arguments
* $table **String**
* $tableAlias **String|null**
* $ignore **Boolean|false** - &lt;p&gt;(do INSERT IGNORE)&lt;/p&gt;
* $onDuplicateKeyUpdate **Boolean|false**



### deleteFrom

    \JSMF\Database\Query JSMF\Database::deleteFrom(String $table, String|null $tableAlias)

init delete command



* Visibility: **public**


#### Arguments
* $table **String**
* $tableAlias **String|null**



### update

    \JSMF\Database\Query JSMF\Database::update(String $table, \JSMF\string $tableAlias)

init update command



* Visibility: **public**


#### Arguments
* $table **String**
* $tableAlias **JSMF\string**



### selectAllRows

    \JSMF\Database\Query JSMF\Database::selectAllRows(Array $fields, Boolean|false $distinct, Boolean|false $sqlNoCache)

init a select command, returns all rows (PDOStatement->fetchAll)



* Visibility: **public**


#### Arguments
* $fields **Array**
* $distinct **Boolean|false**
* $sqlNoCache **Boolean|false**



### selectOneRow

    \JSMF\Database\Query JSMF\Database::selectOneRow(Array $fields, Boolean|false $distinct, Boolean|false $sqlNoCache)

init a select command, returns one row (PDOStatement->fetch)



* Visibility: **public**


#### Arguments
* $fields **Array**
* $distinct **Boolean|false**
* $sqlNoCache **Boolean|false**



### quoteIdentifier

    String JSMF\Database::quoteIdentifier(String $identifier)

simple method for quoting identifiers, without name checking



* Visibility: **public**


#### Arguments
* $identifier **String**



JSMF\Repository
===============






* Class name: Repository
* Namespace: JSMF







Methods
-------


### select

    Mixed JSMF\Repository::select(mixed $primaryKeyValue, array $fields)

select data from table



* Visibility: **public**
* This method is **static**.


#### Arguments
* $primaryKeyValue **mixed**
* $fields **array** - &lt;p&gt;(empty array to select all fields)&lt;/p&gt;



### count

    integer JSMF\Repository::count(Mixed $primaryKeyValue)

counts datasets for primary key



* Visibility: **public**
* This method is **static**.


#### Arguments
* $primaryKeyValue **Mixed**



### update

    boolean JSMF\Repository::update(mixed $primaryKeyValue, array $data)

updates a dataset



* Visibility: **public**
* This method is **static**.


#### Arguments
* $primaryKeyValue **mixed**
* $data **array**



### delete

    boolean JSMF\Repository::delete(mixed $primaryKeyValue)

deletes a dataset



* Visibility: **public**
* This method is **static**.


#### Arguments
* $primaryKeyValue **mixed**



### insert

    integer JSMF\Repository::insert(array $data)

insert data into table, returns new id



* Visibility: **public**
* This method is **static**.


#### Arguments
* $data **array**



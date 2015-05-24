# Introduction #

**Edge** implements a lightweight Active Record ORM pattern.

This means that each database record is represented by a single instance.

Currently, **Edge** has builtin support for MySQL and Mongo DB, but because the framework has no support of building joins and complex queries by default, it should be trivial to add support for other SQL RDBMS.

Below is an example of an Article class

```
<?php
namespace Application\Models;

use Edge\Models\Identifiable;

class Article extends Identifiable {

    protected static $_members = array('feed_id');

    public static function getTable(){
        return 'article';
    }

    protected function feed(){
        return $this->belongsTo('Application\Models\Feed');
    }
}
```

Here, we see that the Article class extends Identifiable which is an abstract class (Edge/Models/Identifiable.php) and provides methods to retrieve records by id and name.

This means that the Article class has 3 attributes:

id, name and feed\_id and the corresponding table is **article**.

In the _protected static $**members** array you define the attributes that the class has and these need to be the same with the table's structure (in the case of MySQL)._

You do not need to define all attributes when inheriting from a class that defines its own attributes, like the case we have here.

The base Record class, iterates the inheritance chain and constructs an array with all the defined attributes.

## Saving records ##

To create and save an Article

```
$article = new Article();
$article->name = "Edge";
$article->feed_id = 1;
$article->save();

//or 

$data = [
  "name" => "Edge",
  "feed_id" => 1
];
$article = new Article($data);
$article->save();

print $article->id;
```

Identifiable defines that the id attribute is a Primary Key by implementing the below method
```
    public static function getPk(){
        return array("id");
    }
```

When the object is saved, **Edge** will detect if any of the PKs have an auto increment flag and set the value.

## Retrieving, updating and deleting ##
```
$article = Article::getItemById(1);
$article->name = "New value";
$article->update();

//to delete
$article->delete();
```

## Setters and Getters ##

To define a setter or/and getter for an attribute simply implement a protected method and prepend it with set or get. Example:

```
/**
* Invoked every time $this->name is called
*/
protected function getName(){
  return $this->attributes['name'];
}

/**
* Invoked every time $this->name = 'value' is called
*/
protected function setName($val){
  $this->assignAttribute('name', $val);
}
```

## Defining Relationships ##

**Edge** allows you to define relationships between classes, the same way you define relationships between database tables.

There are 4 types of relationships:

  1. One to One via hasOne()
  1. One to Many via hasMany()
  1. A record belongs to a parent via belongsTo()
  1. Many to Many via manyToMany()

In the Article class above we can see that there is a method
```
    protected function feed(){
        return $this->belongsTo('Application\Models\Feed');
    }
```

This means that there is a relationship between Article and Feed.

By accessing
```
//we access it as a property and not as a method
$article->feed
```

you get back an instance of the Feed class to which the Article belongs.

In SQL terms it is equivelant to
```
SELECT * FROM feed where id=1;
```

Let's have a look at the Feed class

```
<?php
namespace Application\Models;

use Edge\Models\Identifiable;

class Feed extends Identifiable {

    public static function getTable(){
        return 'feed';
    }

    protected function articles(){
        return $this->hasMany('Application\Models\Article');
    }
}
```

These tells us that the relationship between Articles and Feeds is one to many (1 Feed has many Articles).

So if we access
```
foreach($feed->articles as $article){
   echo $article->name;
}
```

we get back an iterator (`Edge/Core/Database/ResultSet/ResultSet.php`) which yields an Article instance on each iteration.

It is important to note here 2 things:

  1. The relationships are resolved upon request. When the object is instantiated nothing is loaded. When a request is made i.e to $feed->articles, the resolution takes place then.

  1. Once a relationship is resolved, it is cached so any subsequent requests (during the thread's execution) does not reprocess the relationship.

**hasOne()** functions the same way as **belongsTo()**

### Assumptions ###
When you do not pass a second argument to hasOne(), hasMany(), belongsTo(), **Edge** makes some assumptions in order to resolve dependencies.

  * In the case of **hasOne()** and **hasMany()** it assumes that the FK is named tablename\_id (ie feed\_id) and that the value by which to query can be retrieved by invoking $this->id.

  * In the case of **belongsTo()** it assumes that the column of the table is named id and the value to query for can be retrieved by $this->tablename\_id in the current instance (ie $this->feed\_id)

If the above rules are not met by your class you can pass an array in order to provide them

```

$this->belongsTo('Application\Models\Article', [
      'fk' => 'fid', 
      'value' => $this->fk
]);

```

### Many to Many relationships ###
Many to many relationships are a bit more complex when defining them.
The common way we define many to many relationships in RDBMS, is by creating a link table between the 2 entities.

Let's consider an example with a RBAC (Role Based Access Control) system, which is implemented by **Edge**.

Each user can have many roles and a role can have many users.
So having a look at the User class (Edge/Models/User.php) we load the roles the user belongs to as follows

```
    /**
     * Load the roles that are assigned to the user
     * @return ResultSet
     */
    protected function roles(){
        return $this->manyToMany('Edge\Models\Role', array(
            'linkTable' => 'user_role',
            'fk1' => 'user_id',
            'fk2' => 'role_id',
            'value' => $this->id
        ));
    }

```

We also pass an array which describes the link table, the FK's and the user id value with which to retrieve the results. The generated SQL looks like

```
SELECT role.* FROM role
                INNER JOIN user_role u
                ON role.id = u.role_id
                AND u.user_id = '1'

```

## Event Handlers ##

Event handlers are methods where you can define logic to be executed before or after a record is created, updated or deleted.

**Edge** exposes the below events via methods you can implement on the Models
```
public function onCreate(){}
public function onAfterCreate(){}
public function onUpdate(){}
public function onAfterUpdate(){}
public function onDelete(){}
public function onAfterDelete(){}
```

An example of where such behavior is helpful is when for instance you wish to send a verification email to a user after they register.

To accomplish that you would implement the below in the User model

```
public function onAfterCreate(){
   //always call the parent's method so that if the parent
   //has defined logic, it gets executed as well
   parent::onAfterCreate();
   $this->sendVerificationEmail();
}

private function sendVerificationEmail(){
  //logic to send email
}
```

## Querying Records ##

**Edge** provides a convenient way of querying records in a simple manner.

All parameters are escaped to prevent SQL injection.

Additionally, you can specify caching directives to increase performance of slow queries. Check [Caching](Caching.md) for details

```
SELECT * FROM users WHERE username='admin'

User::select()
     ->where(array("username"=>"admin"))
     ->fetch();



SELECT * FROM users WHERE id='2'

User::select()
     ->where(array("id"=>"2"))
     ->fetch(User::FETCH_ASSOC_ARRAY);



SELECT id,name FROM users
         WHERE id in (1,2)
         OR name IN ("Thomas", "John")
         AND sex = "male"
         ORDER BY username ASC

User::select(["id","name"])
    ->where("id"))
    ->in([1,2])
    ->orWhere("name")
    ->in(["Thomas", "John"])
    ->andWhere(["sex"=>"male"])
    ->order(["username"=>"asc"])
    ->cache(["ttl"=>10*60])
    ->fetch(User::FETCH_RESULTSET);



SELECT * FROM users ORDER BY username ASC limit 0,10

User::select()
      ->order(["username"=>"asc"])
      ->limit(0)
      ->offset(10)
      ->fetch(Record::FETCH_NATIVE_RESULTSET);
```

By default **Edge** will return an instance of the class that run the query (in the examples above User).

You can override this by defining one of the below
  * Record::FETCH\_NATIVE\_RESULTSET: Returns the database's native result set (mysqli\_result for MySQL or MongoCursor for Mongo)
  * Record::FETCH\_RESULTSET: Returns an **Edge** iterator which returns an instance of the class that called the select() at each iteration.
  * Record::FETCH\_ASSOC\_ARRAY: returns a PHP associative array (for results that return just 1 record).
## Introduction ##

Caching is an essential component for modern high traffic web applications. It helps scale the application and eleviate database load.

A lot of the below fetaures were implemented after going through how other popular frameworks implement caching.

**Edge** supports three storage engines

  1. Memcached (http://memcached.org)
  1. Redis (http://redis.io)
  1. File caching

The first 2 options store data in memory and are considerably faster than file caching, so opt for these options whenever possible.

## Configuration and Usage ##

As any other services within **Edge**, we configure caching in the config.php file.

Below are some samples for each one of the 3 options

```
        /**
         * Memcached storage
         * We can pass as many servers as we want
         * The order is host:port:weight
         */
        'cache' => array(
            'invokable' => 'Edge\Core\Cache\MemoryCache',
            'args' => array(
                array('localhost:11311:1')
            ),
            'shared' => true
        )

        /**
         * Redis storage
         */
        'cache' => array(
            'invokable' => 'Edge\Core\Cache\RedisCache',
            'args' => array('localhost:6379') ,
            'shared' => true
        )

        /**
         * File storage
         */
        'cache' => array(
            'invokable' => 'Edge\Core\Cache\FileCache',
            'args' => array('/data/cache'),
            'shared' => true
        )
```


To access the cache and use it, you do as with every other service like
```
use Edge\Core\Edge;
$cache = Edge::app()->cache;

//assume $data stores some expensive to calculate data
$cache->add("some_unique_key", $data, 10*60);

//to retrieve the data
$data = $cache->get("some_unique_key");

//to delete the data
$cache->delete("some_unique_key");
```

## Query Caching ##

By default **Edge** caches each instance of any Model that derives from Record. This means that every time you query a model and the result you get back is 1 entry, this willbe cached.

So when you query the below

```
User::getItemById(1);

User::select()
     ->where(["username" => "admin"])
     ->run();

```

these queries return a User instance and by default they are cached.

When you update the specific instance **Edge** automatically invalidates its cached items referring to the specific instance. The same happens when you delete the record, all caches of the record are deleted.

If you do not want this behavior you can define the below method in your Model classes which will disable caching.

```
public static function cacheRecord(){
    return false;
}
```

You can implicitly specify a query to be cached

```
User::select()
     ->where("id")
     ->in([1,50,200])
     ->cache([
         'ttl' => 20*60'
      ])
     ->run();
```

## Cache Validators ##

Additionally, you can specify a condition which is evaluated every time you retrieve a cached item and determines whether the data are stale or not.

**Edge** currently supports two ways for validating caches

  1. By executing a query (`QueryValidator`)
  1. By checking the modification time of a file (`FileValidator`)

Let's take as an example a View that displays information for a list of users. In this scenario, the Action loads some entries from a user table, pass the result to the view which iterates the result and outputs some HTML. Not very intensive in db terms but it will do for our example.

We can cache the db query and pass a validator object that checks if the user count has changed, in which case the cache is invalidated.

```
public function list(){
	$users = User::select()
		     ->cache([
                          'ttl' => 0, //never expire, defaults to this if not specified
		          'cacheValidator' => new QueryValidator("SELECT COUNT(id) FROM users")
		       ])
		    ->run();
	$view = static::loadView('ui.users.tpl');
	$view->users = $users;
	$view->parse();
}
```

### Options for Views and Fragment Caching ###

The above rules also apply when specifying caching directives for views and fragment caching.

Additionally, for these components you can specify to vary the generated cache based on

  1. The requested URL (which is the default by the way)
  1. The session

This means that when genearting the cache key, the engine will take into account the above parameters.

```
public function list(){
	$view = static::loadView('ui.users.tpl', [
             'varyBy' => 'session' //or url
             'ttl' => 30*60
         ]);
	$view->parse();
}

```

## Cache Stampede Prevention ##
[Cache stampede](http://en.wikipedia.org/wiki/Cache_stampede) is a situation where a heavily accessed item expires causing a lot of threads to recalculate the data at the same time. In a high traffic website this can cause a spike in the system load especially if the value to be cached is slow to calculate and may lead to unresponsive or slow server response.

To prevent situations like the above you can specify a second parameter to the get() method.

It is highly recommended to follow this, especially if the data you are caching are slow to calculate. Below is a sample of the method invokation

```


$cache = Edge::app()->cache;
$expensiveValue = $cache->get("unique_id", true);


```

In the above scenario, the first thread that will  identify that the cached item is about to expire, will assume responsibility for calculating the value. All other threads will be served the current value (although a bit stale), until the new value is persisted in the cache storage.
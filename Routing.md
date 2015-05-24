**Edge** provides developers with the ability to map URLs to Controllers and Actions.

The way this is accomplished, is by defining the routes as an associative array.

This file resides under

```
Application/Config/routes.php
```

and has the below structure

```
<?php
return array(
    'GET' => array(
        '/' => array("Home", "index"),
        '/page/action/:name/:id' => array("Home", "index"),
        '/new/test/:id' => array("Home", "test"),
        '/view/city/:id' => array("Home", "city"),
        '/view/:slug/*  => array("PageController", "display")
    ),
    'POST' => array(
        '/rest/api/:id' => array('Home', 'post')
    ),
    '*' => array(
        '/api/update/:id' => array("Home", "test")
    )
);
```

As you can see we return an associative array, where there is another nested array, that maps URLs to controller => action, per HTTP method.

In this way, we restrict the URLs so that they are accessible only with the specified HTTP method.

Another thing to note here, is the existence of the below notation
```
/page/action/:name/:id
```

This notation is useful when you need to route URLs with variable parameters, to the same action.

In the above case, the all the below URLs would be routed to Home => index and anything starting with **:** would become an argument to index

```
/page/action/user/1
/page/action/name/2
/page/action/1/1
```

So the method signature for index would be

```
public function index($id, $name){}
```

Note, that **Edge** does not make any checks on the type of the variables, while resolving a URL to a route. There are no regular expressions run during the resolution phase.

## Defining Access Control to Routes ##

**Edge** provides the ability to the developer to optionally define access control permissions to the routes, by supplying an array with permissions, as a 3rd member of each route array. This functionality, combined with the AccessControl filter, provide a way so that you can control which users can access your actions.

```
return array(
        'POST' => array(
            '/users/delete/:id' => array("Application\\Controllers\\User", "delete",
                                       "acl"=>array("Delete Users"))
        )
    );
```

To take advantage of this functionality, you need to add the below filter to your controllers
```
public function filters(){
        return array_merge(parent::filters(), array(
            array(
                'Edge\Core\Filters\AccessControl',
                "permissions" => Edge:app()->router->getPermissions()
            )
        ));
    }
```
In order for a user to be able to request an action that has an acl defined, this user need to have been granted the appropriate permission.
You can read details on this [here](RBAC.md)
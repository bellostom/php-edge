# Introduction #
Filters offer a convenient way of managing common application logic, shared by multiple controller actions, from a single place.
Filters are classes that extend the
```
\Edge\Core\Filters\BaseFilter abstract class.
```

You can define as many filters as you want in a controller and these will be run **in the order defined within the array**.

Additionally, if one of the filters **returns false, this will immediately stop the execution of any remainding filters**.

In order to apply your logic, you need to override and implement either the preProcess or postProcess methods, or both, depending on your needs.
By default, a filter is applied to all controlller actions (but that can be modified as we will see below).
Below is an example of how you define filters within a controller
```
public function filters(){
        return array_merge(parent::filters(), array(
                array('Cms\Filters\CmsAccess'),
                array(
                    "Edge\Core\Filters\AccessControl",
                    "permissions" => Edge::app()->router->getPermissions(),
                    "exceptions" => ["createPage"]
                ),
                array(
                    'Edge\Core\Filters\CsrfProtection',
                    "applyTo" => ["save", "update", "delete"]
                )
            )
        );
    }
```

Each filter declaration consists of an array with:

  * **The filter class.** This is the class containing the filter logic and needs to be the 1st element of the array.

  * **exceptions key (optional)**. By defining an exceptions array key, you can define an array of actions, for which the filter will not be applied. This is useful for cases where you have a lot of actions to which the filter needs to be applied, but only a few that need to be excluded.

  * **applyTo key (optional).** By default filters are applied to all actions. You can override this behavior, by supplying this key with an array of methods to which the filter will be applied.

  * **any other keys (optional).** You can also define other array keys which will be made available to your filter.


Below is an example of how a filter is used from a Controller and the corresponding filter class.


```
namespace Edge\Controllers;

abstract class AuthController extends BaseController{

    public function filters(){
        return array(
            array(
                'Edge\Core\Filters\Authentication',
                'url' => $this->getLoginUrl()
            )
        );
    }

    /**
     * Define the url for the login page
     * @return mixed
     */
    abstract protected function getLoginUrl();
}
```

and here is the Authentication class filter
```

namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http,
    Edge\Core\Exceptions\Unauthorized;

/**
 * Preprocess filter that requires
 * a user to be authenticated before invoking the specified
 * action
 * @package Edge\Core\Filters
 */
class Authentication extends BaseFilter{

    protected $url;

    public function __construct(array $attrs){
        $this->url = $attrs['url'];
        parent::__construct($attrs);
    }

    public function preProcess(Http\Response $response, Http\Request $request){
        if(Edge::app()->user()->isGuest()){
            if($request->is("GET")){
                Edge::app()->session->redirectUrl = $request->getRequestUrl();
            }
            if($request->isAjax()){
                throw new Unauthorized("Unauthorized access");
            }
            $response->redirect($this->url);
        }
    }
}

```

This filter overrides the preProcess method and checks to see if the user that is executing the request is a guest one.
If it is, it either redirects him to the login page (if the request method was GET) or throws an Unauthorized exception (in case of POST),
which sends a 401 HTTP header to the client.

Another, more complete example of a core filter , is the one responsible for caching a page.

```
namespace Edge\Core\Filters;

use Edge\Core\Edge,
    Edge\Core\Http;

/**
 * Class PageCache
 * Filter that handles page caching
 * @package Edge\Core\Filters
 */
class PageCache extends BaseFilter{

    use \Edge\Core\TraitCachable;

    private $isCached = false;

    public function __construct(array $attrs){
        parent::__construct($attrs);
        //INIT LOGIC FROM TraitCacheable
        $this->init($attrs);
    }

    /**
     * Check if there is a valid cached item and if so
     * send it directly to the browser
     * @param Http\Response $response
     * @param Http\Request $request
     */
    public function preProcess(Http\Response $response, Http\Request $request){
        $val = $this->get($lock=true);
        if($val){
            $response->body = $val;
            $this->isCached = true;
            Edge::app()->logger->debug("Loading from cache page ".$request->getRequestUrl());
            return false;
        }
        return true;
    }

    /**
     * After the request has been processed, get the response
     * body and cache it
     * @param Http\Response $response
     * @param Http\Request $request
     */
    public function postProcess(Http\Response $response, Http\Request $request){
        if(!$this->isCached){
            Edge::app()->logger->debug("Creating page cache for ". $request->getRequestUrl());
            $this->set($response->body);
        }
        return true;
    }
}

A sample filter declaration that makes use of the above filter, would be

{{{
public function filters(){
        return array_merge(parent::filters(), array(
            array(
                'Edge\Core\Filters\PageCache',
                'ttl' => 10*60,
                'varyBy' => 'url',
                'cacheValidator' => new Validator\QueryValidator("SELECT MAX(updated) FROM articles"),
                'applyTo' => array('index')
            ),
            array('Edge\Core\Filters\DynamicOutput')
        ));
    }


}}}
```
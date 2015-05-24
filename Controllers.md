All Controllers within an **Edge** application should derive (directly or indirectly) from the BaseController class located at
```
Edge\Controllers\BaseController.php
```

Each URL is mapped to a Controller and an Action, which is basically a public method of the Controller.

Within an **Edge** Controller, apart from the public and private methods, you can also define:
  * **A layout file.** This is the main template file of your application. Usually, most web applications have many blocks that are common to every page and only a certain part changes between pages. A layout template, is just that. You define placeholders for these common blocks which are managed from one place and feed it just the part that changes.

  * **Static css and js files**. You may define css and js files that the Layout needs and **Edge** combines, minifies and caches them to reduce HTTP requests and improve performance

  * **Filters.** Filters are classes that are invoked before and after a Controller's action is invoked. Cases of where filters are useful include, checking whether a user can access a page, page caching etc. Check [Filters](Filters.md) for details

An example of a Controller can be found below

```
class Home extends BaseController{

    protected static $layout = 'Layouts/ui.layout.tpl';

    protected static $js = [
        'static/js/facebook.js'
    ];

    protected static $css = [
        'static/css/theme.css',
        'static/css/main.css'
    ];

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

        /**
     * Return JSON response
     * @return string
     */
    public function json(){
        return json_encode(array(
            "name"=>"John",
            "surname"=>"Doe"
        ));
    }

    /**
     * Return just the template without decorating
     * it with a Layout. Common scenario when a request
     * is made via AJAX
     * @return string
     */
    public function renderPartial(){
        $tpl = static::loadView('ui.index.tpl');
        $tpl->title = 'Welcome';
        return $tpl->parse();
    }

    /**
     * Return the template decorated with a Layout
     * @return string
     */
    public function index(){
        $tpl = static::loadView('ui.index.tpl');
        $tpl->title = 'Welcome';
        return parent::render($tpl);
    }
}
```

The above is a basic example that includes the features discussed earlier.
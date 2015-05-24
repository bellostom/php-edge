**Edge** views are template files that render HTML, XML or any other content the developer wishes.

**Edge** does not use any templating language, other than PHP itself which is a templating language on its own, thus adding no overhead to the template parsing procedure.

## Basic Example ##

Let's have a look at a sample view

```
<div class="menu">
	<h1><?= $title; ?></h1>
	<ul>
		<?php foreach($menuItems as $item): ?>
		<li><a href="<?= $item['link']; ?>"><?= $item['name']; ?></a></li>
                <?php endforeach; ?>
	</ul>
</div>
```

Nothing special going on here. We have defined the variables $title and $menuItems, from our Action and we loop the $menuItems to display a list and outputting the $title.

Let's have a look on what the Action that loads the View looks like

```
public function menu(){
	$tpl = static::loadView('ui.index.tpl');
	$tpl->title = 'Welcome';
	$tpl->menuItems = [
		["name" => "Home", "link" => "/home/index"],
		["name" => "Contact", "link" => "/contact/index"]
	];
	return $tpl->parse();
}
```

Additionally, you can safely output untrusted content by escaping it using

```
<p class="content"><?= $this->escape($untrustedContent); ?></p>
```

## Caching a View ##

You can cache the output of a View by specifying an array with caching attributes, as the second argument to the **loadView** method.
```
public function menu(){
	$tpl = static::loadView('ui.index.tpl', [
		'ttl' => 10 * 60 //cache for 10 minutes,
		'varyBy' => 'url' //generate cache key based on the requested URL
	]);
	$tpl->title = 'Welcome';
	$tpl->menuItems = [
		["name" => "Home", "link" => "/home/index"],
		["name" => "Contact", "link" => "/contact/index"]
	];
	return $tpl->parse();
}
```

The above will cache the view for 10 minutes and will generate the cache key by creating a hash that includes the requested URL.
You can also define **session** as the value, which creates a cache key
based on the session id.
You can also specify 0 as the TTL value, which means that the cache never expires.

## Fragment Caching ##

**Edge** supports fragment caching within a View.

This means that you can define a certain block of a View to be cached.

```
<?php if($this->startCache("menu")): ?>
	<ul>
		<?= foreach($menuItems as $item): ?>
		<li><a href="<?= $item['link']; ?>"><?= $item['name']; ?></a></li>
	</ul>
<?php $this->endCache(); ?>
<?php endif; ?>
```

In the above example we wrap the menu UL within a startCache() endCache() block. If the content is not cached the loop is executed and its output is captured and cached. Any subsequent requests will use the cached version of the menu.

Note here, that the 1st argument to the **startCache()** method defines the **cache key**, so you should choose something unique in order to avoid any strange side effects.

Again here, you can define an array with cache attributes as the second argument to **startCache**.

## Advanced Caching Concepts ##

There are case, when you cache some content, but there are certain blocks that need to be dynamic.

As an example we can think of a header that displays the loggedin user's email address. This information is dynamic, while the rest of the header is most of the times static.

To overcome this problem **Edge** supports a way to define that an expression should always be evaluated even if it resides within a cached block.

So going back on our example where we cache the whole view
```
<div class="menu">
    <h1><?= $title; ?></h1>
    <h2><?php $this->alwaysEvaluate("\Application\Controllers\Home::fetchUser"); ?></h2>
    <ul>
        <?php foreach($menuItems as $item): ?>
        <li><a href="<?= $item['link']; ?>"><?= $item['name']; ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
```

This way, although the view is cached the expression within **alwaysEvaluate** will be invoked and the value will be displayed.

Note here that whenever you use **alwaysEvaluate** you must add the **`DynamicOutput`** filter in the controller. So in our example the Controller would look like
```
class Home extends BaseController{

    protected static $layout = 'Layouts/ui.layout.tpl';

    public function filters(){
        return array_merge(parent::filters(), array(
            array('Edge\Core\Filters\DynamicOutput')
        ));
    }

    public function index(){
        $tpl = static::loadView('ui.index.tpl',["ttl"=>60]);
        $tpl->title = 'Welcome';
        $tpl->menuItems = [
            ["name" => "Home", "link" => "/home/index"],
            ["name" => "Contact", "link" => "/contact/index"]
        ];
        return $tpl->parse();
    }

    public static function fetchUser(){
        return time();
    }
```

Of course you can add **alwaysEvaluate** expressions within fragment caching also.

You can find more details on caching [here](Caching.md)
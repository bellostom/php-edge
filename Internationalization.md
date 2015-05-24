In order to support multilingual support for your web application you need to create one file per language and place them under a directory ie
```
Application/Languages
```

The structure of the file should be like the below
```
<?php
return array(
    'HOME' => 'home',
    'CONTACT' => 'Contact Us'
);
```

Then, you need to define a service in your
```
Application/Config/config.php
```

to handle the strings

```
'i18n' => array(
            'invokable' => function($c){
                $lang = $c['session']->lang;
                if(!$lang){
                    $lang = 'en';
                }
                $vals = include(realpath(__DIR__."/../Languages/$lang.php"));
                return new Edge\Core\i18n\String($vals);
            },
            'shared' => true
        ),
```

As you can see here, in order to resolve which file to load, we check the session for the lang attribute.

In order to access the localized strings within a view
```
<div>
  <h1><?= $i18n['HOME']; ?></h1>
  or
  <h1><?= $i18n->HOME; ?></h1>
</div>
```

To access the strings from anywhere else just invoke the service
```
use Edge\Core\Edge;

Edge::app()->i18n['HOME'];

```
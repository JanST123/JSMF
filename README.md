# Welcome to the JSMF - The Jan S. MVC Framework

With this framework you can build a whole MVC (Model-View-Controller)-based PHP Application or just use it as a collection of useful PHP classes. All parts of the framework can be used individually.

See the [ApiIndex](docs/ApiIndex.md) for all available classes and methods.

Please refer to the **example application** while the documentation is in this incomplete state.

## Installation
You can install JSMF via Composer. Add the following dependency to your composer.json
```json
{
  "require": {
    "janst123/jsmf":">=1.0"
}
```

You can also clone JSMF from this repository (use the version tags or clone the master branch for latest changes). In this case you have to write your own autoloader.

## Sample Application Bootstrap
This is only needed if you want to base your whole application on JSMF. You can also use single Classes, using the JSMF autoloader or your own.

Place this code in your applications index file. Route all request thru this file (See this [Gist](https://gist.github.com/RaVbaker/2254618) for an introduction on how to route all requests to index.php with Apache)
Using this minimal setup will let the JSMF\Application class determine the Model/Controller/Action from the request url (http://host/module/controller/action).

If one or more url parts are not present, the application will always use the "index" action (the "index" controller, the "index" module). 

Example: Requesting http://host will try to call module "index" -> IndexController -> indexAction, Request to http://host/misc/faq will call module "misc" -> FaqController -> indexAction

```php
<?php
define('DEV_SERVER', true); // define this 
define('SRC', realpath(dirname(__FILE__) . '/../'));
require(SRC . '/vendor/autoload.php');

try {
  // optional: load an application wide config
  JSMF\Config::load(SRC . '/config/base.config.php');

  // optional: load application wide translations
  JSMF\Language::loadTranslations(SRC . '/language/translations', 'de');

  // optional: route special URLs to special modules / controllers / actions ( I always place the legal texts in a Module named misc)
  JSMF\Request::addRoute('/^\/disclaimer\/?$/i', 'misc', 'index', 'disclaimer'); // route a request to /disclaimer to the disclaimer Action in the IndexController in the module "misc"
  JSMF\Request::addRoute('/^\/privacy\/?$/i', 'misc', 'index', 'privacy');

  // run the application
  JSMF\Application::run();

  // output the applications response (can be HTML, JSON ...)
  JSMF\Response::output();

} catch(JSMF\Exception\NotFound $e) {
  // do some special things for not-found errors e.g. redirect to a static 404 page
  JSMF\Request::redirect('/404.html');
  JSMF\Response::output(); // output method also sends the headers (here: the Location header)

} catch(JSMF\Exception $e) {
  // output is done by JSMF
  JSMF\Response::output();

} catch(Exception $e) {
  // do something on common non-JSMF Exception
}
```





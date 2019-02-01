<?php
define('DEV_SERVER', true); // define this to get detailed exceptions
define('SRC', realpath(dirname(__FILE__) . '/../'));
require(SRC . '/vendor/autoload.php');

if (DEV_SERVER) {
  ini_set('display_errors', 1);
  ini_set('error_reporting', E_ALL);
}

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
  if (DEV_SERVER) {
    print_r($e);

  } else {
    JSMF\Request::redirect('/404.html');
    JSMF\Response::output(); // output method also sends the headers (here: the Location header)
  }

} catch(JSMF\Exception $e) {
  // output is done by JSMF
  JSMF\Response::setException($e);
  JSMF\Response::output();

} catch(Exception $e) {
  // @TODO: do something useful on common non-JSMF Exception
  print_r($e);
}

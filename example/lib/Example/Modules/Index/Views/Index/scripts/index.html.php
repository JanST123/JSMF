<h1><?=$this->_('hello')?></h1>
<h2><?=$this->_('hello_user', [ 'Jan' ])?></h2>

This is the view of the indexAction in indexController and Module "Index"


<h3>Tickets:</h3>
<?php
foreach ($this->tickets as $ticket) {
  echo $this->partial('partials/ticket', ['ticket' => $ticket]);
}
?>


<h3>Other examples</h3>
<a href="<?=\JSMF\Config::get('baseUri', '')?>/index/index/json">Test JSON Action</a><br />
<a href="<?=\JSMF\Config::get('baseUri', '')?>/misc/legal">Test Legal with no action -> calling indexAction in legalController</a><br />
i<a href="<?=\JSMF\Config::get('baseUri', '')?>/misc/legal/privacy">Test Privacy (other module)</a><br />

<br />
Nested Config Properties can be accessed like this: <?=\JSMF\Config::get('bar.baz')?>

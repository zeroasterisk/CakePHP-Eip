Eip: Edit in Place for CakePHP
===================

This is a helper to simplify Edit in Place functionality in CakePHP views

* https://github.com/zeroasterisk/CakePHP-Eip
* Author:      Alan Blount
* Author URL:  http://zeroasterisk.com/
* Copyright (c) 2013 Alan Blount
* Licensed under The MIT License

Inspiration from:

* https://github.com/kareypowell/CakePHP-InPlace-Editing
* Author:      Karey H. Powell
* Author URL:  http://kareypowell.com/
* Copyright (c) 2012 Karey H. Powell
* Licensed under The MIT License

Requirements
-----------------

* requires jquery or zepto
* requires https://github.com/tuupola/jquery_jeditable (may switch js engine)
* recommended twitter bootstrap (tooltip handling / styling)

Install
-----------------

```
git clone https://github.com/zeroasterisk/CakePHP-Eip.git app/Plugin/Eip
curl 'http://www.appelsiini.net/download/jquery.jeditable.mini.js' > app/webroot/js/jquery.jeditable.mini.js
```

Add the following to `app/Config/bootstrap.php`

```
CakePlugin::load('Eip');
```

Usage
-----------------

*Controller*

```
public $helpers = array('Eip.Eip' => array(
	// path where you downloaded jeditable.js
	'pathToScript' => '/js/jquery.jeditable.mini.js',
	// default options for Eip->input()
	'options' => array(),
);

public $components = array('Eip.Eip');

/**
 * simple option & lazy option
 * no view needed
 */
public function eip() {
	$this->Eip->auto('Page');
}

/**
 * manual option offers more control and doesn't break MVC
 * view needed
 */
public function eipManual() {
	if (!$this->myOwnSecurity($this->Auth->user())) {
		return $this->redirect('/');
	}
	$data = $this->Eip->setupData('Page', array('Page' => array('is_active' => 1)));
	$saved = $this->Page->save($data);
	$this->set(compact('data', 'saved'));
}
```

*View*

In the view where you want the Edit in Place text to show (index, view, etc)

```
<?php echo $this->Eip->input('Page.title', $page); ?> <!-- no hover title -->
<?php echo $this->Eip->input('Page.title_alt', $page, 'Alternate Title of Page'); ?>
<?php echo $this->Eip->input('Page.title_head', $page, array('title' => 'Title of Page in the header')); ?>
<?php echo $this->Eip->input('Page.title_other', $page, $options); ?> <!-- see list of options below -->
```

*Layout*

```
<?php
// At the bottom of the page
// might already be there (non-inlined JS from HtmlHelper)
// http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::scriptBlock
echo $this->fetch('script');
?>


```

Options you can pass into Eip Helper
-----------------

```
public $options = array(
	'submitUrl' => array('action' => 'eip'), // url to be submit to (array or string)
	'id' => null, // if empty, will attempt to get from the data
	'display' => null, // if set, overrides the display of the Eip
	'element' => 'div', // wrapper container
	'elementClass' => 'eip-wrap', // wrapper class for container
	'formHelper' => 'Form', // can set to some other helper eg: TwitterBootstrap
	'toolTip' => 'Click to Edit', //
	'loadurl' => null, // Normally content of the form will be same as content of the edited element. However using this parameter you can load form content from external URL.
	// --------- form options can be used from the form helper
	'rows' => 1,
	'cols' => 5,
	'label' => null, // if set, displays a label
	'type' => 'textarea', // defaults to textarea
	'cssclass' => 'eip', // any class for input
	'style' => 'eip', // any style for input
	'value' => null, // if null, will attempt to get from the data
	// --------- button options
	'submit' => 'Save', // or false to hide the button
	'cancel' => 'Cancel', // or false to hide the button
	/*
	// --------- hackery, these could be set and should work
	'data' => null,
	'modelName' => null,
	'fieldName' => null,
	*/
);
```

(NOTE: you can set these options when loading the helper too)

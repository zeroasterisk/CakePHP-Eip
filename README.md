Eip: Edit in Place for CakePHP
===================

This is a helper to simplify Edit in Place functionality in CakePHP views

Validation is done in the Model, optionally also in the View (currently little
)

[Demo Screencast](http://screencast.com/t/gwCPpjqIPg) (2:21)

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

Huge credit to the fantastic X-editable library:

* http://vitalets.github.io/x-editable/index.html
* http://github.com/vitalets/x-editable
* Author:      Vitaliy Potapov
* Author URL:  https://github.com/vitalets

Requirements
-----------------

* @requires bootstrapjs or jquery or zepto (not included in this repo)
* @requires http://vitalets.github.io/x-editable/ (included in this repo)

Install
-----------------

```
git clone https://github.com/zeroasterisk/CakePHP-Eip.git app/Plugin/Eip
```

Decide on a supported Library.

* Bootstrap
* jQueryUI
* jQuery

For more information on these options check out
(X-editable)[http://vitalets.github.io/x-editable/index.html]

All following examples assume bootstrap is chosen
(others are included in this package, you just have to move them into place appropriatly)

Setup Library JS/CSS: Method A: symlink
-----------------

```
cd app/webroot
ln -s ../Plugin/Eip/webroot/bootstrap-editable/bootstrap-editable eip
```

Setup Library JS/CSS: Method B: copy the directory
-----------------

```
cd app/webroot
cp -r ../Plugin/Eip/webroot/bootstrap-editable/bootstrap-editable eip
```

Setup the Config file (for option defaulting)
-----------------

A basic config file has been included in the package, just copy it into place
and edit...

```
cd app/Config
cp ../Plugin/Eip/Config/eip.php ./
```

If you already have a config file that is being loaded, you can also use that one

```
$config['Eip'] = array(
	...
);
```

Configure
-----------------

Add the following to `app/Config/bootstrap.php`

```
CakePlugin::load('Eip');
```

Configure: Edit app/Config/eip.php
-----------------

The `app/Config/eip.php` file is the easiest way to configure Eip.

It allows you to setup sitewide configurations for Eip, but you can still
override these configuration on a per-controller basis by passing in settings
when initializing the helper.

Also, the config file is only loaded when the helper is iniitalized, so there's
no extra applicaiton latency on controllers which don't load the helper.

Configure: Pass in settings when loading the helpers
-----------------

```
Class SomethingController extends AppController {
	public $helpers = array(
		'Eip.Eip' => array(
			'pathToJs' => '...',
			'pathToJs' => '...',
			'options' => array(...)
		),
	)
}
```


Usage
-----------------

*Controller*

```
public $helpers = array('Eip.Eip');

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
```

Options
-----------------

For more details on options see
(X-editable)[http://vitalets.github.io/x-editable/docs.html#editable]

You may set these options anywhere you like. Here's the load order (later overwrites)

* in the config file,
* or in the `$helper` variable options, in the controller
* or in the `$this->Eip->input($path, $data, $options);` parameter, in the view

```
$options = array(
	// -----------------------
	// options for container on page & data setup
	// url to be submit to via ajax (array or string)
	//   (or it can be a custom JS function instead of an ajax url)
	//   the default will end up being on the current prefix/controller
	//     with the 'action' of 'eip'
	'url' => array('action' => 'eip'),
	// if empty, will attempt to get from the data
	//   translates to 'pk' option in x-editable
	'id' => null,
	// if set, overrides the display of the Eip
	'display' => null,
	// wrapper container
	'element' => 'span',
	// wrapper class for container
	'elementClass' => 'eip-wrap',
	// title/tooltip
	'title' => null,
	// rel
	'rel' => null,
	// -----------------------
	// options for input
	// Type of input. Can be text|textarea|select|date|checklist and more
	'type' => 'text',
	'mode' => 'popup', // inline or popup
	// -----------------------
	// x-editable options which are passed through "as is" if not null
	//   for more info: http://vitalets.github.io/x-editable/docs.html
	'ajaxOptions' => null,
	'anim' => null,
	'autotext' => null,
	'disabled' => null,
	'emptyclass' => null,
	'emptytext' => null, // placeholder
	'name' => null, // taken from id attribute
	'onblur' => null,
	'params' => null,
	'placement' => null,
	'savenochange' => null,
	'selector' => null,
	'send' => null,
	'showbuttons' => null,
	'success' => null,
	'toggle' => null,
	'validate' => null, // custom clientside validation
	'value' => null, // Initial value of input. If not set, taken from element's text.
	// x-editable options for other input types
	'inputclass' => null,
	'tpl' => null,
	// textarea
	'rows' => null,
	// select & checklist
	'source' => null,
	'prepend' => null, // empty option
	'sourceCache' => null,
	'sourceError' => null,
	'separator' => null, // checklist only
	// date
	'format' => null,
	'viewformat' => null,
	'datepicker' => null,
	'clear' => null,
	// combodate
	//   <script src="js/moment.min.js"></script>
	'template' => null,
	'combodate' => null,
	// wysihtml5
	//   Wysihtml5 default options.
	//   https://github.com/jhollingworth/bootstrap-wysihtml5#options
	'wysihtml5' => null,
);
```

Exceptions
----------------

Want to customize the feedback for error / exception handling?

No Problem... just create the EipDataException class somewhere before the
EipComponent is initialized into the controller, and your Exception handler
takes over rendering.

Note: it must return a non-200 Http Status code...

	class EipDataException extends CakeException {
		protected $_messageTemplate = '%s %s';
		public function __construct($message, $data=null, $debugOnly=null) {
			header("HTTP/1.0 417 Expectation Failed");
			if (!empty($data)) {
				$data = preg_replace('#[^a-zA-Z0-9 \-\_:]+#', ' ', json_encode($data));
				$data = "($data)";
			}
			echo sprintf($this->_messageTemplate, $message, $data);
			if (Configure::read('debug') > 2) {
				echo json_encode($debugOnly);
			}
			exit;
		}
	}


<?php
/**
 * This is a helper to simplify Edit in Place functionality in CakePHP views
 *
 * ----------------------------------------------------------------------------
 * Package:     CakePHP Eip Plugin
 * Link:        https://github.com/zeroasterisk/CakePHP-Eip
 * Version:     0.1
 * Date:        2013-04-08
 * Description: CakePHP plugin for in-place-editing,
 *              aka: inline editing, edit in place
 *              It should work for any form element.
 * Author:      Alan Blount
 * Author URL:  http://zeroasterisk.com/
 * ----------------------------------------------------------------------------
 * Copyright (c) 2013 Alan Blount
 * Licensed under The MIT License
 * ----------------------------------------------------------------------------
 * Inspiration from:
 * @link https://github.com/kareypowell/CakePHP-InPlace-Editing
 * Author:      Karey H. Powell
 * Author URL:  http://kareypowell.com/*
 *
 * ----------------------------------------------------------------------------
 * @requires jquery or zepto
 * @requires https://github.com/tuupola/jquery_jeditable (may switch js engine)
 * @recommended twitter bootstrap (tooltip handling / styling)
 * ----------------------------------------------------------------------------
 *
 * Setup in controller:
 * $helpers = array('Eip.Eip' => array('pathToScript' => * '/js/jeditable.custom.js', 'options' => array());
 *
 * Usage:
 * <?php echo $this->Eip->input('Page.title', $page); ?>
 * <?php echo $this->Eip->input('Page.title', $page, 'Main Title of Page'); ?>
 * <?php echo $this->Eip->input('Page.title', $page, array('title' => 'Main Title of Page')); ?>
 *
 */
class EipHelper extends AppHelper {

	public $helpers = array('Html', 'Js');

	/**
	 * Configuration for the Path to the script
	 */
	public $pathToScript = '/js/vendors/jquery.jeditable.mini.js';

	/**
	 * placeholder for data which can be set on the helper object
	 * useful for large datasets where you are accessing the same set of data
	 * over and over again, vs. passing it into input() over and over again
	 *
	 * @access public
	 */
	public $data = null;

	/**
	 * placeholder for all the JS commands to be rendered to page
	 * done as one block on beforeRender()
	 * triggered via Html->scriptBlock()
	 * render in layout via echo $this->fetch('script');
	 *
	 * @access public
	 */
	public $js = '';

	/**
	 * default options to be passed into input
	 * you can overwrite these at runtime
	 *
	 *   'submitUrl' => array('action' => 'eip'), // url to be submit to (array or string)
	 *   'id' => null, // if empty, will attempt to get from the data
	 *   'display' => null, // if set, overrides the display of the Eip
	 *   'element' => 'div', // wrapper container
	 *   'elementClass' => 'eip-wrap', // wrapper class for container
	 *   'formHelper' => 'Form', // can set to some other helper eg: TwitterBootstrap
	 *   'tooltip' => 'Click to Edit',
	 *   'loadurl' => null, // Normally content of the form will be same as content of the edited element. However using this parameter you can load form content from external URL.
	 *   --------- form options can be used from the form helper
	 *   'rows' => 1,
	 *   'cols' => 5,
	 *   'label' => false, // if set, displays a label
	 *   'type' => 'textarea', // defaults to textarea
	 *   'cssclass' => 'eip', // any class for input
	 *   'style' => 'eip', // any style for input
	 *   'value' => null, // if null, will attempt to get from the data
	 *   --------- button options
	 *   'submit' => 'Save', // or false to hide the button
	 *   (return should still save if hidden)
	 *   'cancel' => 'Cancel', // or false to hide the button
	 *   (esc should still cancel if hidden)
	 *
	 * @access public
	 */
	public $options = array(
		'submitUrl' => array('action' => 'eip'), // url to be submit to (array or string)
		'id' => null, // if empty, will attempt to get from the data
		'display' => null, // if set, overrides the display of the Eip
		'element' => 'div', // wrapper container
		'elementClass' => 'eip-wrap', // wrapper class for container
		'formHelper' => 'Form', // can set to some other helper eg: TwitterBootstrap
		'tooltip' => 'Click to Edit', //
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

	/**
	 * constuct, handle settings
	 */
	public function __construct(View $view, $settings = array()) {
		parent::__construct($view, $settings);
		if (!empty($settings['pathToScript'])) {
			$this->pathToScript = $settings['pathToScript'];
		}
		if (!empty($settings['options'])) {
			$this->options = am($this->options, $settings['options']);
		}
	}

	/**
	 * Create an Edit In Place element
	 * the view will be the
	 *
	 * @param string $path Model.field
	 * @param array $data array(Model => array(field => value))
	 * @param array @options
	 *
	 */
	public function input($path=null, $data=null, $options=null) {
		// set options
		if (!is_array($options)) {
			$options = array('title' => $options);
		}
		extract(am($this->options, $options));

		// validate the path
		if (strpos($path, '.')) {
			list($modelName, $fieldName) = explode('.', trim($path));
		}
		if (empty($modelName) || empty($fieldName)) {
			throw new OutOfBoundsException('EipHelper::input() invalid $path arg, should be "Model.field"');
			return false;
		}

		// validate the data
		if (empty($data) && !empty($this->data)) {
			$data = $this->data;
		}
		if (empty($data) || !array_key_exists($modelName, $data) || !is_array($data[$modelName]) || !array_key_exists($fieldName, $data[$modelName])) {
			throw new OutOfBoundsException('EipHelper::input() unable to find $path in data, $path should be "Model.field" and must exist in passed in $data.');
			return false;
		}

		// default the value
		if ($value == null) {
			$value = (isset($data[$modelName][$fieldName]) ? $data[$modelName][$fieldName] : '');
		}

		// default the display
		if (empty($display)) {
			$display = $value;
		}


		// validate the ID
		if (empty($id)) {
			$id = (isset($data[$modelName]['id']) ? $data[$modelName]['id'] : null);
		}
		if (empty($id)) {
			throw new OutOfBoundsException('EipHelper::input() unable to find id');
		}

		// validate the submitUrl
		if (empty($submitUrl)) {
			throw new OutOfBoundsException('EipHelper::input() unable to find submitUrl');
		}

		// generate an elementId
		$elementId = $key = String::uuid();

		// generate a secure hash
		$hash = Security::hash(serialize(compact('key', 'id', 'modelName', 'fieldName')), 'sha1', true);

		// revise the submitUrl
		$submitUrl = Router::url($submitUrl);
		if (substr($submitUrl, '-1') !== '/') {
			$submitUrl .= '/';
		}
		$submitUrl .= "hash:{$hash}/key:{$elementId}/id:{$id}/";

		// misc
		if (!empty($title)) {
			$tooltip = $title;
		}

		// generate JS to trigger EIP
		// http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::scriptBlock
		$name = "data[$modelName][$fieldName]";
		$jsOptions = Set::filter(compact('name', 'type', 'cancel', 'submit', 'tooltip',
			'cssclass', 'style', 'rows', 'cols', 'loadurl'));
		$this->js .= '$("#' . $elementId . '").editable("' . $submitUrl . '", ' . $this->Js->object($jsOptions) . ');';

		// return URL
		// note: wrapped with data-* attributes for
		// http://twitter.github.io/bootstrap/javascript.html#tooltips
		return sprintf('<%s id="%s" class="%s" data-toggle="tooltip" data-placement="left" title="%s">%s</%s>',
			$element,
			$elementId,
			$elementClass,
			$tooltip,
			$display,
			$element
		);
	}

	/**
	 * use the callback of afterRender to inject all JS
	 * this triggers AFTER the view, but BEFORE the layout
	 * so if you need to customize $this->pathToScript, do so in the view
	 * or you can pass in as settings
	 * via Html->scriptBlock()
	 */
	public function afterRender($a=null) {
		if (!empty($this->js)) {
			$this->Html->scriptBlock('$.getScript("' . $this->pathToScript . '", function() { ' . $this->js . '});', array('inline' => false));
		}
		return parent::afterRender($a);
	}
}

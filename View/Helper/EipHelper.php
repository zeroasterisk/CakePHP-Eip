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
 * ----------------------------------------------------------------------------
 * Huge credit to the fantastic X-editable library:
 * @link http://vitalets.github.io/x-editable/index.html
 * @link http://github.com/vitalets/x-editable
 * Author:      Vitaliy Potapov
 * Author URL:  https://github.com/vitalets
 *
 * ----------------------------------------------------------------------------
 * @requires bootstrapjs or jquery or zepto (not included in this repo)
 *
 * @requires http://vitalets.github.io/x-editable/ (included in this repo)
 *
 *
 * Setup:
 * ----------------------------------------------------------------------------
 *   See README
 *
 * Usage: (in views)
 * ----------------------------------------------------------------------------
 * <?php echo $this->Eip->input('Page.title', $page); ?>
 * <?php echo $this->Eip->input('Page.title', $page, 'Main Title of Page'); ?>
 * <?php echo $this->Eip->input('Page.title', $page, array('title' => 'Main Title of Page')); ?>
 *
 */
App::uses('AppHelper', 'View/Helper');

class EipHelper extends AppHelper {

	public $helpers = array('Html', 'Js');

	/**
	 * Configuration for the Path to the script
	 */
	public $pathToJs = '/eip/js/bootstrap-editable.js';

	/**
	 * Configuration for the Path to the script
	 */
	public $pathToCss = '/eip/css/bootstrap-editable.css';

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
	 *   'url' => array('action' => 'eip'), // url to be submit to (array or string)
	 *   'id' => null, // if empty, will attempt to get from the data
	 *   'element' => 'span', // wrapper container
	 *   ...
	 *
	 * @access public
	 */
	public $options = array(
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
		// Type of input. Can be text|textarea|select|date|checkbox and more
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
		// select & checkbox
		'source' => null,
		'prepend' => null, // empty option
		'sourceCache' => null,
		'sourceError' => null,
		'separator' => null, // checkbox only
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
		// only for directl helper settings
		'autoLoadConfig' => false
	);


	/**
	 * some of the options' keys are commonly known as other parameters
	 * so this is a simple mapping array to translate from alias => real
	 *
	 * @access public
	 */
	public $optionAliases = array(
		'placeholder' => 'emptytext',
	);

	/**
	 * constuct, handle settings
	 */
	public function __construct(View $view, $settings = array()) {
		parent::__construct($view, $settings);
		// auto load config file (if exists)
		if (!empty($settings['autoLoadConfig']) || file_exists(APP . 'Config' . DS . 'eip.php')) {
			Configure::load('eip');
		}
		$eipConfig = (array)Configure::read('Eip');
		if (!empty($eipConfig)) {
			// extend settings with config (settings override on conflict)
			$settings = Set::merge(Set::diff($eipConfig, array(null)), $settings);
		}
		if (!empty($settings['pathToJs'])) {
			$this->pathToJs = $settings['pathToJs'];
		}
		if (!empty($settings['pathToCss'])) {
			$this->pathToCss = $settings['pathToCss'];
		}
		if (!empty($settings['options'])) {
			$this->options = array_merge($this->options, $settings['options']);
		}
	}

	/**
	 * Create an Edit In Place element
	 * the view will be the
	 *
	 * @param string $path Model.field
	 * @param array $data array(Model => array(field => value))
	 * @param array @options
	 * @return string Input form element
	 */
	public function input($path, $data = array(), $options = array()) {
		// set options
		if (!is_array($options)) {
			$options = array('title' => $options);
		}
		$options = array_merge($this->options, $options);
		foreach ($this->optionAliases as $alias => $real) {
			if (array_key_exists($alias, $options) && empty($options[$real])) {
				$options[$real] = $options[$alias];
			}
		}
		extract($options);

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
		if ($value === null) {
			$value = (isset($data[$modelName][$fieldName]) ? $data[$modelName][$fieldName] : '');
		}

		//$value = 'eee';

		// default the display
		if (empty($display)) {
			$display = $value;
		}

		// default the emptytext (placeholder)
		if ($emptytext === null) {
			$emptytext = Inflector::humanize($fieldName);
		}

		// validate the ID
		if (empty($id)) {
			$id = (isset($data[$modelName]['id']) ? $data[$modelName]['id'] : null);
		}
		if (empty($id)) {
			throw new OutOfBoundsException('EipHelper::input() unable to find id');
		}

		// validate the url
		if (empty($url)) {
			throw new OutOfBoundsException('EipHelper::input() unable to find url');
		}

		// generate an elementId
		$elementId = $key = 'eip_' . String::uuid();

		// generate a secure hash
		// TODO: secure further by integrating session id into $hash
		$hash = Security::hash(serialize(compact('key', 'id', 'modelName', 'fieldName')), 'sha1', true);

		// revise the url
		if (is_string($url) && strpos($url, '(') !== false && strpos($url, ')') !== false) {
			// url is a JS function call, do not touch
		} else {
			// url is a URL, pass through Router::url();
			$url = Router::url($url);
			// ensure it ends in a slash
			if (substr($url, '-1') !== '/') {
				$url .= '/';
			}
			// add hash to end of URL
			$url .= "hash:{$hash}/key:{$elementId}/id:{$id}/";
			// add unique URL to defeate browser cache
			$url .= '?u=_'.time();
		}

		// generate JS to trigger EIP
		//   http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::scriptBlock
		// translate a few customized options
		//   name is sent as a POST value, so Model.field is the easiest to parse
		//   not sent as: "data[$modelName][$fieldName]";
		$name = "$modelName.$fieldName";
		$pk = $id;
		// passthrough options
		//   http://vitalets.github.io/x-editable/docs.html
		$jsOptions = compact(
			// customized
			'pk',
			'type',
			'mode',
			'url',
			'name',
			// misc
			'ajaxOptions',
			'anim',
			'autotext',
			'disabled',
			'emptyclass',
			'emptytext',
			'onblur',
			'params',
			'placement',
			'savenochange',
			'selector',
			'send',
			'showbuttons',
			'success',
			'toggle',
			'validate',
			'value',
			// extras for other types
			'format',
			'viewformat',
			'datepicker'
		);
		$jsOptions = array_diff($jsOptions, array(null));
		$this->js .= '$("#' . $elementId . '").editable(' . $this->Js->object($jsOptions) . ');';

		// return URL
		// note: wrapped with data-* attributes for
		// http://twitter.github.io/bootstrap/javascript.html#tooltips
		return sprintf('<%s href="#eip" id="%s" class="%s" data-pk="%s" data-type="%s" title="%s">%s</%s>',
			$element,
			$elementId,
			$elementClass,
			$id,
			(empty($type) ? 'text' : $type),
			$title,
			$display,
			$element
		);
	}

	/**
	 * use the callback of afterRender to inject all JS
	 * this triggers AFTER the view, but BEFORE the layout
	 * so if you need to customize $this->pathToJs, do so in the view
	 * or you can pass in as settings
	 * via Html->scriptBlock()
	 */
	public function afterRender($viewFile = null) {
		$content = parent::afterRender($viewFile);
		if (!empty($this->js)) {
			$content .= $this->Html->css( $this->pathToCss, null, array('inline' => false) );
			//$content .= $this->Html->script( $this->pathToJs, array('inline' => false) );
			$this->Html->scriptBlock('$.getScript("' . $this->pathToJs . '", function() { ' . $this->js . '});', array('inline' => false));
		}
		return $content;
	}

}

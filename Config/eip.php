<?php
/**
 * Eip configuration
 * --------------------
 *
 * initial setup of this config
 *
 *   cd app/Config
 *   cp ../Plugin/Eip/Config/eip.php ./
 *   vim eip.php
 * --------------------
 *
 * you may configure Eip in this config file, which is simpler
 * than putting settings into the helper options on every controller.
 *
 * All settings/options are supported here.
 *
 * They will override the defaults in the plugin helper,
 * but any settings you pass into the Controller->helper will override these configurations
 */
$config = array(
	'Eip' => array(
		// whereever you put the x-editable JS file
		//     as a symlink
		//     cd app/webroot; ln -s ../Plugin/eip/webroot/bootstrap-editable eip
		//   eg: 'pathToJs' => '/eip/bootstrap-editable/js/bootstrap-editable.min.js',
		//     as a copied directory
		//     cd app/webroot; cp -r ../Plugin/eip/webroot/bootstrap-editable/bootstrap-editable ./
		//   eg: 'pathToJs' => '/bootstrap-editable/js/bootstrap-editable.min.js',
		'pathToJs' => null,
		// same as pathToCss
		'pathToCss' => null,
		// full list of options
		//   see: https://github.com/zeroasterisk/CakePHP-Eip#Options
		'options' => array(
			'mode' => 'inline',
		)
	)
);

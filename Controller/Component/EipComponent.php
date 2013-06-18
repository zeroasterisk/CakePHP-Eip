<?php
/**
 * This is a component to simplify Edit in Place functionality in CakePHP controllers
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
 * Setup:
 * ----------------------------------------------------------------------------
 *   See README
 *
 * Usage: (in controllers)
 * ----------------------------------------------------------------------------
 * public $helpers = array('Eip.Eip');
 * public $components = array('Eip.Eip');
 *
 * public function eip() {
 *     $this->Eip->auto('Page'); // simple option
 * }
 *
 * public function eipManual() {
 *     if (!$this->myOwnSecurity($this->Auth->user())) {
 *         return $this->redirect('/');
 *     }
 *     $data = $this->Eip->setupData('Page', array('Page' => array('is_active' => 1)));
 *     $this->Page->save($data);
 *     $this->set(compact('data'));
 * }
 *
 */
App::uses('Component', 'Controller');

/**
 * Data is validated by the model
 * if Eip expectations fail or the model invalidates the data on save
 * we throw a EipDataException error, which shows up in the interface
 * and sets the HTTP status to something other than a 200
 *
 * You may create your own EipDataException class before this component initializes
 */
if (!class_exists('EipDataException')) {
	class EipDataException extends CakeException {

		protected $_messageTemplate = '%s %s';

		public function __construct($message, $data = null, $debugOnly = null) {
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
}

class EipComponent extends Component {

	/**
	 * Placeholder for modelName
	 */
	public $modelName = null;

	/**
	 * Placeholder for fieldName
	 */
	public $fieldName = null;

	/**
	 * Placeholder for request
	 */
	public $request = null;

	/**
	 * Placeholder for passedArgs
	 */
	public $passedArgs = null;

	/**
	 * initialize Component - gets data from Controller
	 *
	 * @param object $controller
	 * @return boolean
	 */
	public function initialize(Controller $controller) {
		$this->request = $controller->request;
		$this->passedArgs = $controller->passedArgs;
		$this->controller = $controller;
	}

	/**
	 * auto will setupData() and then automatically save() the record
	 * then it will respondLazy()
	 * this is a "shortcut" option, if your needs are basic
	 *
	 * @param string $modelName required
	 * @param array $defaultData optionally inject some data, would be overwritten by passed in data
	 * @return void
	 */
	public function auto($modelName = null, $fieldName = null, $defaultData = array()) {
		if ($modelName === null) {
			$modelName = $this->controller->{$this->controller->modelClass}->alias;
		}
		$data = $this->setupData($modelName, $fieldName, $defaultData);
		$saved = $this->save($data, $modelName);
		return $this->respondLazy($saved, $modelName, $fieldName);
	}

	/**
	 * Help parse Eip data/arguments into a normalized CakePHP data array
	 * Will also handle security/validation for the Eip hash
	 * Doesn't save anything, just resturns $data
	 * (NOTE: you should handle authentication independantly before or after this)
	 *
	 * @param string $modelName required
	 * @param string $fieldName optional (if empty, derived from data)
	 * @param array $defaultData optionally inject some data, would be overwritten by passed in data
	 * @return array $data
	 */
	public function setupData($modelName = null, $fieldName = null, $defaultData = array()) {
		// data as reformatted by x-editable
		if (!empty($this->request->data['name']) && isset($this->request->data['value'])) {
			list($_model, $_field) = explode('.', $this->request->data['name']);
			$this->request->data[$_model][$_field] = $this->request->data['value'];
			unset($this->request->data['name']);
			unset($this->request->data['value']);
			if (!empty($this->request->data['pk'])) {
				$this->request->data[$_model]['id'] = $this->request->data['pk'];
				unset($this->request->data['pk']);
			}
		}
		// verify inputs and data
		if (empty($modelName)) {
			throw new EipDataException('not saved', 'missing modelName from $data', compact('modelName', 'fieldName', 'defaultData'));
		}
		// verify data[model]
		if (empty($this->request->data[$modelName])) {
			throw new EipDataException('not saved', '$data[' . $modelName . '] is empty', $this->request->data);
		}
		if (empty($fieldName)) {
			$fieldName = key($this->request->data[$modelName]);
		}
		if (empty($fieldName)) {
			throw new EipDataException('not saved', 'missing ' . $fieldName . ' from $data', $this->request->data);
		}
		extract($this->passedArgs);
		if (empty($id) || empty($key) || empty($hash)) {
			throw new EipDataException('not saved', 'Security: missing required passedArgs from URL', compact('id', 'key', 'hash'));
		}
		$hashShouldBe = Security::hash(serialize(compact('key', 'id', 'modelName', 'fieldName')), 'sha1', true);
		if ($hash !== $hashShouldBe) {
			throw new EipDataException('not saved', 'Security: check failure', compact('hashShouldBe', 'hash'));
		}
		// setup on model for easy access in other methods
		$this->modelName = $modelName;
		$this->fieldName = $fieldName;
		// finalize data setup & return
		$data = $this->request->data;
		if (!empty($defaultData)) {
			$data = Set::merge($defaultData, $data);
		}
		$data[$modelName] = array_merge($data[$modelName], compact('id'));
		return $data;
	}

	/**
	 * setupData() and then automatically save the record
	 * this is a "shortcut" option, if your needs are basic
	 *
	 * @param array $data required (can pass in from setupData())
	 * @param string $modelName required
	 * @return mixed $data or false (Model->save() response)
	 */
	public function save($data, $modelName = array()) {
		if (empty($modelName)) {
			$modelName = $this->modelName;
		}
		if (empty($modelName)) {
			throw new EipDataException('not saved', 'missing modelName [component::save()]', compact('modelName', 'data'));
		}
		if (empty($data)) {
			throw new EipDataException('not saved', 'missing data to save [component::save()]', compact('modelName', 'data'));
		}
		$Model = ClassRegistry::init($modelName);
		$Model->create(false);
		$saved = $Model->save($data);
		if (!$saved) {
			// if we didn't save, here's where we throw back validationErrors
			throw new EipDataException('not saved', $Model->validationErrors, $data);
		}
		return $saved;
	}

	/**
	 * You can use this option to respond the view...
	 * it breaks MVC and is generally considered bad form,
	 * but for EIP this might be acceptable.
	 *
	 * @param array $data
	 * @return void
	 */
	public function respondLazy($data, $modelName = null, $fieldName = null) {
		if (empty($modelName)) {
			$modelName = $this->modelName;
		}
		if (empty($modelName)) {
			throw new EipDataException('eip::respondLazy()', 'missing modelName');
		}
		if (empty($fieldName)) {
			$fieldName = $this->fieldName;
		}
		if (empty($fieldName)) {
			throw new EipDataException('eip::respondLazy()', 'missing fieldName');
		}
		$this->controller->response->autoRender = false;
		if (!empty($data[$modelName][$fieldName])) {
			$this->controller->response->body($data[$modelName][$fieldName]);
		}
	}

}

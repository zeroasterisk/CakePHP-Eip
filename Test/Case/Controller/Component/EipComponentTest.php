<?php
App::uses('EipComponent', 'Eip.Controller/Component');
App::uses('Controller', 'Controller');

class EipDataException extends CakeException {
	public function __construct($message, $data = null, $debugOnly = null) {
		$code = 417;
		parent::__construct($message . ': ' . $data, $code);
	}
}

class User extends CakeTestModel {

}

/**
 * TODO: use fixtures
 */
class EipComponentTest extends CakeTestCase {

	public $fixtures = array('core.user');

	public function setUp() {
		parent::setUp();

		$this->Controller = new EipTestController(new CakeRequest, new CakeResponse);
		$this->Controller->constructClasses();
		$this->Controller->startupProcess();
	}

	public function tearDown() {
		parent::tearDown();

		unset($this->Controller->TestEip);
		unset($this->Controller);
	}


	/**
	 * EipComponentTest::testSetupData()
	 *
	 * @expectedException EipDataException
	 * @return void
	 */
	public function testSetupDataInvalid() {
		$is = $this->Controller->TestEip->setupData();

	}

	/**
	 * EipComponentTest::testSetupData()
	 *
	 * @return void
	 */
	public function testSetupData() {
		$id = 'my-id';
		$key = 'key';
		$modelName = 'User';
		$fieldName = 'email';
		$hash = Security::hash(serialize(compact('key', 'id', 'modelName', 'fieldName')), 'sha1', true);
		$this->Controller->passedArgs = compact('key', 'id', 'modelName', 'fieldName', 'hash');
		$this->Controller->startupProcess();

		$this->Controller->request->data[$modelName][$fieldName] = 'some@mail.com';
		$is = $this->Controller->TestEip->setupData($modelName, $fieldName);
		$expected = array($modelName => array(
			$fieldName => $this->Controller->request->data[$modelName][$fieldName],
			'id' => $id
		));
		$this->assertSame($expected, $is);
	}

	/**
	 * EipComponentTest::testAuto()
	 *
	 * @return void
	 */
	public function testAuto() {
		$id = 'my-id';
		$key = 'key';
		$modelName = 'User';
		$fieldName = 'email';
		$hash = Security::hash(serialize(compact('key', 'id', 'modelName', 'fieldName')), 'sha1', true);
		$this->Controller->passedArgs = compact('key', 'id', 'modelName', 'fieldName', 'hash');
		$this->Controller->startupProcess();

		$this->Controller->request->data[$modelName][$fieldName] = 'some@mail.com';
		$this->Controller->TestEip->auto();
		$is = $this->Controller->response->body();
		$this->assertSame($this->Controller->request->data[$modelName][$fieldName], $is);
	}

}

/*** other files ***/


class TestEipComponent extends EipComponent {

}

class EipTestController extends Controller {

	public $uses = array('User');

/**
 * components property
 *
 * @var array
 * @access public
 */
	public $components = array('Session', 'TestEip');
/**
 * failed property
 *
 * @var bool false
 * @access public
 */
	public $failed = false;
/**
 * Used for keeping track of headers in test
 *
 * @var array
 * @access public
 */
	public $testHeaders = array();
/**
 * fail method
 *
 * @access public
 * @return void
 */
	public function fail() {
		$this->failed = true;
	}
/**
 * redirect method
 *
 * @param mixed $option
 * @param mixed $code
 * @param mixed $exit
 * @access public
 * @return void
 */
	public function redirect($url, $status = null, $exit = true) {
		return $status;
	}
/**
 * Conveinence method for header()
 *
 * @param string $status
 * @return void
 * @access public
 */
	public function header($status) {
		$this->testHeaders[] = $status;
	}

}

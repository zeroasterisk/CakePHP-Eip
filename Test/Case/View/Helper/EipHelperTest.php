<?php
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('EipHelper', 'Eip.View/Helper');

/**
 * EipHelper Test Case
 *
 */
class EipHelperTest extends CakeTestCase {

	/**
	 * data
	 */
	public $user = array(
		'User' => array(
			'id' => 'user-1',
			'name' => 'Fixture User',
			'email' => 'user1@example.com',
		),
		'Profile' => array(
			'id' => 'profile-1',
			'user_id' => 'user-1',
			'zip' => '40202',
		),
		'BadData' => array(
			'user_id' => 'user-1',
			'reason' => 'no "id" so no primary key :(',
		),
		'Category' => array(
			0 => array('id' => 'cat1', 'name' => 'cat one'),
			1 => array('id' => 'cat2', 'name' => 'cat two'),
		)
	);

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$View = new View();
		$this->Eip = new EipHelper($View);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->Eip);

		parent::tearDown();
	}

	/**
	 * testInput bad inputs
	 *
	 * @return void
	 */
	public function testInputBad() {
		/*
		$result = $this->Eip->input();
		try {
			$result = $this->Eip->input();
			$this->fail('No exception');
		} catch (OutOfBoundsException $e) {
			$this->pass('Correct exception thrown');
		}
		try {
			$result = $this->Eip->input('FieldOnly');
			$this->fail('No exception');
		} catch (OutOfBoundsException $e) {
			$this->pass('Correct exception thrown');
		}
		try {
			$result = $this->Eip->input('ModelOnly');
			$this->fail('No exception');
		} catch (OutOfBoundsException $e) {
			$this->pass('Correct exception thrown');
		}
		try {
			$result = $this->Eip->input('MissingDot');
			$this->fail('No exception');
		} catch (OutOfBoundsException $e) {
			$this->pass('Correct exception thrown');
		}
		 */
	}

	/**
	 * testInput good inputs
	 *
	 * @return void
	 */
	public function testInputGood() {
		$result = $this->Eip->input('User.email', $this->user);
		$pattern = '#<div id="[a-f0-9\-]{36}" class="eip-wrap" data-toggle="tooltip" data-placement="left" title="">user1@example.com</div>#';
		$this->assertEquals(1, preg_match($pattern, $result));
		$result = $this->Eip->input('Profile.zip', $this->user);
		$pattern = '#<div id="[a-f0-9\-]{36}" class="eip-wrap" data-toggle="tooltip" data-placement="left" title="">40202</div>#';
		$this->assertEquals(1, preg_match($pattern, $result));

		// this wont work, we aren't smart enough for this path :(
		//$result = $this->Eip->input('Category.0.name', $this->user);
		// but this would:
		$result = $this->Eip->input('Category.name', array('Category' => $this->user['Category'][0]));
		$pattern = '#<div id="[a-f0-9\-]{36}" class="eip-wrap" data-toggle="tooltip" data-placement="left" title="">cat one</div>#';
		$this->assertEquals(1, preg_match($pattern, $result));
		$result = $this->Eip->input('Category.name', array('Category' => $this->user['Category'][1]));
		$pattern = '#<div id="[a-f0-9\-]{36}" class="eip-wrap" data-toggle="tooltip" data-placement="left" title="">cat two</div>#';
		$this->assertEquals(1, preg_match($pattern, $result));
		// TODO: stub out the Html helper and verify JS
	}


}

<?php
/**
 * group test - Eip
 */
class AllEipTest extends PHPUnit_Framework_TestSuite {

	/**
	 * suite method, defines tests for this suite.
	 *
	 * @return void
	 */
	public static function suite() {
		$Suite = new CakeTestSuite('All Eip plugin tests');
		$path = dirname(__FILE__);
		$Suite->addTestDirectory($path . DS . 'Controller' . DS . 'Component');
		$Suite->addTestDirectory($path . DS . 'View' . DS . 'Helper');
		return $Suite;
	}
}

<?php
namespace TYPO3\Flow\Tests\Unit\Reflection;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Testcase for ReflectionService
 *
 */
class ReflectionServiceTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	public function setUp() {
		$this->reflectionService = $this->getAccessibleMock('\TYPO3\Flow\Reflection\ReflectionService', array('dummy'), array(), '', FALSE);
		$environment = $this->getMock('\TYPO3\Flow\Utility\Environment', array('dummy'), array(new \TYPO3\Flow\Core\ApplicationContext('Testing/Unit')));
		$this->reflectionService->injectEnvironment($environment);
	}

	/**
	 * @test
	 */
	public function fileWithNoClassAreMarkedUnconfigurable() {
		$this->reflectionService->_call('reflectClass', 'TYPO3\Flow\Tests\Reflection\Fixture\FileWithNoClass');
		$this->assertTrue($this->reflectionService->isClassUnconfigurable('TYPO3\Flow\Tests\Reflection\Fixture\FileWithNoClass'));

	}

	/**
	 * @test
	 */
	public function isTagIgnoredWorksWithAssociativeArrayConfiguration() {
		$settings = array('reflection' => array('ignoredTags' => array('ignored' => TRUE, 'notignored' => FALSE)));
		$this->reflectionService->injectSettings($settings);

		$this->assertTrue($this->reflectionService->_call('isTagIgnored', 'ignored'));
		$this->assertFalse($this->reflectionService->_call('isTagIgnored', 'notignored'));
		$this->assertFalse($this->reflectionService->_call('isTagIgnored', 'notconfigured'));
	}

	/**
	 * @test
	 */
	public function isTagIgnoredWorksWithOldConfiguration() {
		$settings = array('reflection' => array('ignoredTags' => array('ignored')));
		$this->reflectionService->injectSettings($settings);

		$this->assertTrue($this->reflectionService->_call('isTagIgnored', 'ignored'));
		$this->assertFalse($this->reflectionService->_call('isTagIgnored', 'notignored'));
	}
}

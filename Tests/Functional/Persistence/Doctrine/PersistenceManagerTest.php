<?php
/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

namespace TYPO3\Flow\Tests\Functional\Persistence\Doctrine;

use TYPO3\Flow\Tests\Functional\Persistence\Fixtures\EntityWithManyToOneToMultipleIdentityEntity;
use TYPO3\Flow\Tests\Functional\Persistence\Fixtures\MultipleIdentityEntity;
use TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SingleIdentityEntity;

/**
 * Class PersistenceManagerTest
 *
 * @package TYPO3\Flow\Tests\Functional\Persistence\Doctrine
 */
class PersistenceManagerTest extends \TYPO3\Flow\Tests\FunctionalTestCase {

	/**
	 * @var boolean
	 */
	static protected $testablePersistenceEnabled = TRUE;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		if (!$this->persistenceManager instanceof \TYPO3\Flow\Persistence\Doctrine\PersistenceManager) {
			$this->markTestSkipped('Doctrine persistence is not enabled');
		}
	}

	/**
	 * @test
	 */
	public function persistenceManagerReturnsSimpleIdentityIfObjectContainsSingleIdentityAndIsJustRegisteredAsNewObject() {
		$entity = new SingleIdentityEntity('foo');
		$this->persistenceManager->registerNewObject($entity);
		$identifier = $this->persistenceManager->getIdentifierByObject($entity);
		$this->assertEquals('foo', $identifier);
	}

	/**
	 * @test
	 */
	public function persistenceManagerReturnsSimpleIdentityIfObjectContainsSingleIdentityAndObjectIsNotKnownByPersistence() {
		$entity     = new SingleIdentityEntity('foo');
		$identifier = $this->persistenceManager->getIdentifierByObject($entity);
		$this->assertEquals('foo', $identifier);
	}

	/**
	 * @test
	 */
	public function persistenceManagerReturnsSimpleIdentityIfObjectContainsSingleIdentityAndObjectIsKnownByPersistence() {
		$entity = new SingleIdentityEntity('foo');
		$this->persistenceManager->add($entity);
		$identifier = $this->persistenceManager->getIdentifierByObject($entity);
		$this->assertEquals('foo', $identifier);
	}

	/**
	 * @test
	 */
	public function persistenceManagerReturnsSimpleIdentityIfObjectContainsSingleIdentitiesAndObjectIsPersisted() {
		$entity = new SingleIdentityEntity('foo');
		$this->persistenceManager->add($entity);
		$this->persistenceManager->persistAll();
		$identifier = $this->persistenceManager->getIdentifierByObject($entity);
		$this->assertEquals('foo', $identifier);
	}

	/**
	 * @test
	 */
	public function persistenceManagerReturnsIdentityArrayIfObjectContainsMultipleIdentitiesAndIsJustRegisteredAsNewObject() {
		$entity = new MultipleIdentityEntity('foo', 'acme.bar');
		$this->persistenceManager->registerNewObject($entity);
		$identifier = $this->persistenceManager->getIdentifierByObject($entity);
		$this->assertEquals(array('id' => 'foo', 'workspace' => 'acme.bar'), $identifier);
	}

	/**
	 * @test
	 */
	public function persistenceManagerReturnsSimpleIdentityIfObjectContainsMultipleIdentitiesAndObjectIsNotKnownByPersistence() {
		$entity     = new MultipleIdentityEntity('foo', 'acme.bar');
		$identifier = $this->persistenceManager->getIdentifierByObject($entity);
		$this->assertEquals(array('id' => 'foo', 'workspace' => 'acme.bar'), $identifier);
	}

	/**
	 * @test
	 */
	public function persistenceManagerReturnsSimpleIdentityIfObjectContainsMultipleIdentitiesAndObjectIsKnownByPersistence() {
		$entity = new MultipleIdentityEntity('foo', 'acme.bar');
		$this->persistenceManager->add($entity);
		$identifier = $this->persistenceManager->getIdentifierByObject($entity);
		$this->assertEquals(array('id' => 'foo', 'workspace' => 'acme.bar'), $identifier);
	}

	/**
	 * @test
	 */
	public function persistenceManagerReturnsSimpleIdentityIfObjectContainsMultipleIdentitiesAndObjectIsPersisted() {
		$entity = new MultipleIdentityEntity('foo', 'acme.bar');
		$this->persistenceManager->add($entity);
		$this->persistenceManager->persistAll();
		$identifier = $this->persistenceManager->getIdentifierByObject($entity);
		$this->assertEquals(array('id' => 'foo', 'workspace' => 'acme.bar'), $identifier);
	}

	/**
	 * @test
	 */
	public function persistedSingleIdentityEntityGetableByString() {
		$identifier = 'foo';
		$entity     = new SingleIdentityEntity($identifier);
		$this->persistenceManager->add($entity);
		$this->persistenceManager->persistAll();
		$object = $this->persistenceManager->getObjectByIdentifier($identifier, 'TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SingleIdentityEntity');

		$this->assertInstanceOf('TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SingleIdentityEntity', $object);
		$this->assertEquals($identifier, $this->persistenceManager->getIdentifierByObject($object));
	}

	/**
	 * @test
	 */
	public function persistedSingleIdentityEntityGetableByIdentityArray() {
		$identifier = 'foo';
		$entity     = new SingleIdentityEntity($identifier);
		$this->persistenceManager->add($entity);
		$this->persistenceManager->persistAll();
		$object = $this->persistenceManager->getObjectByIdentifier(array('id' => $identifier), 'TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SingleIdentityEntity');

		$this->assertInstanceOf('TYPO3\Flow\Tests\Functional\Persistence\Fixtures\SingleIdentityEntity', $object);
		$this->assertEquals($identifier, $this->persistenceManager->getIdentifierByObject($object));
	}

	/**
	 * @test
	 */
	public function persistedMultipleIdentityEntityGetableByIdentityArray() {
		$identifier = array('id' => 'foo', 'workspace' => 'acme.bar');
		$entity     = new MultipleIdentityEntity('foo', 'acme.bar');
		$this->persistenceManager->add($entity);
		$this->persistenceManager->persistAll();
		$object = $this->persistenceManager->getObjectByIdentifier($identifier, 'TYPO3\Flow\Tests\Functional\Persistence\Fixtures\MultipleIdentityEntity');

		$this->assertInstanceOf('TYPO3\Flow\Tests\Functional\Persistence\Fixtures\MultipleIdentityEntity', $object);
		$this->assertEquals($identifier, $this->persistenceManager->getIdentifierByObject($object));
	}

	/**
	 * @test
	 */
	public function persistedMultipleIdentityEntityGetableByIdentityArray1() {
		$subEntity = new MultipleIdentityEntity('foo', 'acme.bar');
		$aggregate = new EntityWithManyToOneToMultipleIdentityEntity('foo', $subEntity);
		$this->persistenceManager->add($subEntity);
		$this->persistenceManager->add($aggregate);
		$this->persistenceManager->persistAll();

		/** @var EntityWithManyToOneToMultipleIdentityEntity $object */
		$object = $this->persistenceManager->getObjectByIdentifier('foo', 'TYPO3\Flow\Tests\Functional\Persistence\Fixtures\EntityWithManyToOneToMultipleIdentityEntity');

		$this->assertInstanceOf('TYPO3\Flow\Tests\Functional\Persistence\Fixtures\EntityWithManyToOneToMultipleIdentityEntity', $object);
		$this->assertEquals('foo', $this->persistenceManager->getIdentifierByObject($object));

		$this->assertInstanceOf('TYPO3\Flow\Tests\Functional\Persistence\Fixtures\MultipleIdentityEntity', $object->getSubEntity());
		$this->assertEquals(array('id' => 'foo', 'workspace' => 'acme.bar'), $this->persistenceManager->getIdentifierByObject($object->getSubEntity()));
	}
}

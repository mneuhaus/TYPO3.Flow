<?php
namespace TYPO3\Flow\Tests\Functional\Persistence\Fixtures;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A sample entity for tests containing a single id
 *
 * @Flow\Entity
 */
class EntityWithManyToOneToMultipleIdentityEntity {

	/**
	 * @var string
	 * @ORM\Id
	 * @ORM\Column(length=40)
	 * @Flow\Identity
	 */
	protected $id;

	/**
	 * @var MultipleIdentityEntity
	 * @ORM\ManyToOne
	 * @ORM\JoinColumns({
	 *      @ORM\JoinColumn(name="EntityID", referencedColumnName="id"),
	 *      @ORM\JoinColumn(name="WorkspaceId", referencedColumnName="workspace")
	 * })
	 */
	protected $subEntity;

	/**
	 * @param $id
	 * @param MultipleIdentityEntity $subEntity
	 */
	public function __construct($id, MultipleIdentityEntity $subEntity) {
		$this->id = $id;
		$this->subEntity = $subEntity;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return MultipleIdentityEntity
	 */
	public function getSubEntity() {
		return $this->subEntity;
	}

}

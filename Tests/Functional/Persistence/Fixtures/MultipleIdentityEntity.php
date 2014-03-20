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
 * A sample entity for tests containing multiple ids
 *
 * @Flow\Entity
 */
class MultipleIdentityEntity {

	/**
	 * @var string
	 * @ORM\Id
	 * @ORM\Column(length=40)
	 * @Flow\Identity
	 */
	protected $id;

	/**
	 * @var string
	 * @ORM\Id
	 * @ORM\Column(length=40)
	 * @Flow\Identity
	 */
	protected $workspace;

	/**
	 * @param string $id
	 * @param string $workspace
	 */
	public function __construct($id, $workspace) {
		$this->id = $id;
		$this->workspace = $workspace;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getWorkspace() {
		return $this->workspace;
	}

}

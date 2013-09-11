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
 * A sample category entity for tests
 *
 * @Flow\Entity
 */
class Category {

	/**
	 * @var \Doctrine\Common\Collections\Collection<\TYPO3\Flow\Tests\Functional\Persistence\Fixtures\Post>
	 * @ORM\ManyToMany(mappedBy="categories")
	 */
	protected $posts;

	/**
	 * @var boolean
	 */
	protected $approved = FALSE;

	/**
	 * @param boolean $approved
	 */
	public function setApproved($approved) {
		$this->approved = $approved;
	}
}
?>
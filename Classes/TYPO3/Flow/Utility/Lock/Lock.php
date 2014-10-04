<?php
namespace TYPO3\Flow\Utility\Lock;

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

/**
 * A general lock class.
 *
 * @Flow\Scope("prototype")
 * @api
 */
class Lock {

	/**
	 * @var \TYPO3\Flow\Utility\Lock\LockStrategyInterface
	 * @Flow\Inject
	 */
	protected $lockStrategy;

	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var boolean
	 */
	protected $exclusiveLock = TRUE;

	/**
	 * @param string $subject
	 * @param boolean $exclusiveLock TRUE to, acquire an exclusive (write) lock, FALSE for a shared (read) lock. An exclusive lock ist the default.
	 */
	public function __construct($subject, $exclusiveLock = TRUE) {
		$this->subject = $subject;
		$this->exclusiveLock = $exclusiveLock;
	}

	/**
	 * Initialize object, acquires the lock
	 * @return void
	 */
	public function initializeObject() {
		$this->lockStrategy->acquire($this->subject, $this->exclusiveLock);
	}

	/**
	 * Releases the lock
	 * @return boolean TRUE on success, FALSE otherwise
	 */
	public function release() {
		return $this->lockStrategy->release();
	}

	/**
	 * Destructor, releases the lock
	 * @return void
	 */
	public function __destruct() {
		$this->release();
	}
}

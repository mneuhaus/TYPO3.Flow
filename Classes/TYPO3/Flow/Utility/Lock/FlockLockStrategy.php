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
 * A file system based lock strategy.
 *
 * @Flow\Scope("prototype")
 */
class FlockLockStrategy implements LockStrategyInterface {

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 * @Flow\Inject
	 */
	protected $environment;

	/**
	 * Identifier used for this lock
	 * @var string
	 */
	protected $id;

	/**
	 * File resource used for this lock
	 * @var Resource
	 */
	protected $lockFileName;

	/**
	 * File pointer if using flock method
	 * @var resource
	 */
	protected $filepointer;

	/**
	 * @param string $subject
	 * @param boolean $exclusiveLock TRUE to, acquire an exclusive (write) lock, FALSE for a shared (read) lock.
	 * @return void
	 */
	public function acquire($subject, $exclusiveLock) {
		$this->lockFileName = \TYPO3\Flow\Utility\Files::concatenatePaths(array($this->environment->getPathToTemporaryDirectory(), md5($subject)));

		if (($this->filepointer = fopen($this->lockFileName, 'w+')) == FALSE) {
			throw new \RuntimeException('Lock file could not be opened', 1386520596);
 		}

		if ($exclusiveLock === TRUE && flock($this->filepointer, LOCK_EX) === TRUE) {
			//Exclusive lock acquired
		} elseif ($exclusiveLock === FALSE && flock($this->filepointer, (LOCK_EX | LOCK_NB)) === TRUE) {
			//Shared lock acquired
		} else {
			throw new \RuntimeException('Could not lock file "' . $this->lockFileName . '"', 1386520597);
		}
	}

	/**
	 * Releases the lock
	 * @return boolean TRUE on success, FALSE otherwise
	 */
	public function release() {
		$success = TRUE;
		if (is_resource($this->filepointer)) {
			if (flock($this->filepointer, LOCK_UN) == FALSE) {
				$success = FALSE;
			}
			fclose($this->filepointer);
		}
		\TYPO3\Flow\Utility\Files::unlink($this->lockFileName);

		return $success;
	}
}

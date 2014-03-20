<?php
namespace TYPO3\Flow\Annotations;

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
 * Marks a method as a signal for the signal/slot implementation
 * of Flow. The method will be augmented as needed (using AOP)
 * to be a usable signal.
 *
 * Marks a method as a slot for the the signal/slow implementation
 * of Flow. The method will be automatically wired to the specified
 * signal.
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Slot {
	/**
	 * The ClassName to wire this slot to
	 * @var string
	 */
	public $class;

	/**
	 * The signalName to wire this slot to
	 * @var string
	 */
	public $signal;

	/**
	 * @var string
	 */
	public $passSignalInformation = FALSE;

	/**
	 * @param array $values
	 */
	public function __construct(array $values) {
		if (isset($values['class'])) {
			$this->class = ltrim($values['class'], '\\');
		}
		if (isset($values['signal'])) {
			$this->signal = lcfirst(str_replace('emit', '', $values['signal']));
		}
		if (isset($values['passSignalInformation'])) {
			$this->passSignalInformation = $values['passSignalInformation'];
		}
	}
}

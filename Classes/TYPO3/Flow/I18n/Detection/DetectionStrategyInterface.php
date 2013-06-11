<?php
namespace TYPO3\Flow\I18n\Detection;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Flow".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * In order to detect an appropriate Locale for a request, a Locale detection
 * chain can be configured. Each such Locale detection strategy will care about
 * finding out a Locale depending on the strategy's implementation.
 */
interface DetectionStrategyInterface {

	/**
	 * Detects the according locale, or NULL if no sufficient decision could be made
	 *
	 * @return \TYPO3\Flow\I18n\Locale
	 */
	public function detectLocale();
}
?>
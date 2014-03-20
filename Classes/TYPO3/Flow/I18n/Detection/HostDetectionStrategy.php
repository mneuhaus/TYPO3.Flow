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
use TYPO3\Flow\Http\HttpRequestHandlerInterface;
use TYPO3\Flow\I18n\Exception\InvalidLocaleIdentifierException;
use TYPO3\Flow\I18n\Locale;

/**
 * Tries to find a locale from the host part of the current request.
 * Configured host name patterns can be set up to map against an intended
 * locale (see TYPO3.Flow.i18n.hostDetectionPatternMap setting and its description)
 *
 * @Flow\Scope("singleton")
 */
class HostDetectionStrategy implements DetectionStrategyInterface {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Core\Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\LocaleCollection
	 */
	protected $localeCollection;

	/**
	 * @var array
	 */
	protected $patternMap = array();

	/**
	 * Tries to detect a locale if a host pattern matches
	 *
	 * @throws \TYPO3\Flow\I18n\Exception\InvalidLocaleIdentifierException
	 * @return \TYPO3\Flow\I18n\Locale
	 */
	public function detectLocale() {
		$activeRequestHandler = $this->bootstrap->getActiveRequestHandler();
		if (!$activeRequestHandler instanceof HttpRequestHandlerInterface) {
			return;
		}
		$host = $activeRequestHandler->getHttpRequest()->getUri()->getHost();
		foreach ($this->patternMap as $pattern => $localeIdentifier) {
			if (preg_match($pattern, $host) !== 1) {
				continue;
			}
			try {
				$locale = new Locale($localeIdentifier);
				$bestMatchingLocale = $this->localeCollection->findBestMatchingLocale($locale);
				if ($bestMatchingLocale !== NULL) {
					return $bestMatchingLocale;
				}
			} catch (InvalidLocaleIdentifierException $exception) {
				throw new InvalidLocaleIdentifierException(sprintf('The locale identifier "%s" configured for the HostDetectionStrategy with host pattern "%s" is not valid', $localeIdentifier, $pattern), 1370974887, $exception);
			}
		}
		return NULL;
	}
}
?>
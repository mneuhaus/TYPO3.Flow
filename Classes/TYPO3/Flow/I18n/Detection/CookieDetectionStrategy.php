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
 * Tries to find an appropriate locale by a possibly set HTTP cookie.
 * The name of the Cookie which is examined is configured in the protected
 * field $cookieName of this class, and can be changed via Objects.yaml.
 *
 * @Flow\Scope("singleton")
 */
class CookieDetectionStrategy implements DetectionStrategyInterface {

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
	 * The name of the cookie where to try to find a locale identifier,
	 * is set via Objects configuration.
	 *
	 * @var string
	 */
	protected $cookieName;

	/**
	 * Tries to detect a locale given by a Cookie value
	 *
	 * @return \TYPO3\Flow\I18n\Locale
	 */
	public function detectLocale() {
		$activeRequestHandler = $this->bootstrap->getActiveRequestHandler();
		if (!$activeRequestHandler instanceof HttpRequestHandlerInterface) {
			return;
		}
		$cookie = $activeRequestHandler->getHttpRequest()->getCookie($this->cookieName);
		if ($cookie === NULL) {
			return;
		}
		try {
			$locale = new Locale($cookie->getValue());
			$bestMatchingLocale = $this->localeCollection->findBestMatchingLocale($locale);
			if ($bestMatchingLocale !== NULL) {
				return $bestMatchingLocale;
			}
		} catch (InvalidLocaleIdentifierException $exception) {
		}
		return NULL;
	}
}
?>
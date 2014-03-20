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
use TYPO3\Flow\I18n\Locale;

/**
 * Tries to find an appropriate Locale by reading the Accept-Language HTTP header.
 *
 * @Flow\Scope("singleton")
 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
 */
class AcceptLanguageHeaderDetectionStrategy implements DetectionStrategyInterface {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Core\Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\Detector
	 */
	protected $localeDetector;

	/**
	 * Tries to detect the Locale by the Accept-Language header
	 *
	 * @return \TYPO3\Flow\I18n\Locale
	 */
	public function detectLocale() {
		$activeRequestHandler = $this->bootstrap->getActiveRequestHandler();
		if (!$activeRequestHandler instanceof HttpRequestHandlerInterface) {
			return;
		}
		$request = $activeRequestHandler->getHttpRequest();
		if (!$request->hasHeader('Accept-Language')) {
			return;
		}
		$possiblyDetectedLocale = $this->localeDetector->getLocaleFromHttpHeader($request->getHeader('Accept-Language'));
		if ($possiblyDetectedLocale !== NULL) {
			$activeRequestHandler->getHttpResponse()->setHeader('Vary', 'Accept-Language', FALSE);
			return $possiblyDetectedLocale;
		}
		return;
	}
}
?>
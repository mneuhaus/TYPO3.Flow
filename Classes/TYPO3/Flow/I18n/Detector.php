<?php
namespace TYPO3\Flow\I18n;

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
use TYPO3\Flow\I18n\Detection\DetectionStrategyInterface;
use TYPO3\Flow\Object\Exception\UnknownObjectException;

/**
 * The Detector class provides methods for automatic locale detection
 *
 * @Flow\Scope("singleton")
 * @api
 */
class Detector {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\Flow\I18n\Service
	 */
	protected $localizationService;

	/**
	 * A collection of Locale objects representing currently installed locales,
	 * in a hierarchical manner.
	 *
	 * @var \TYPO3\Flow\I18n\LocaleCollection
	 */
	protected $localeCollection;

	/**
	 * @param \TYPO3\Flow\I18n\Service $localizationService
	 * @return void
	 */
	public function injectLocalizationService(\TYPO3\Flow\I18n\Service $localizationService) {
		$this->localizationService = $localizationService;
	}

	/**
	 * @param \TYPO3\Flow\I18n\LocaleCollection $localeCollection
	 * @return void
	 */
	public function injectLocaleCollection(\TYPO3\Flow\I18n\LocaleCollection $localeCollection) {
		$this->localeCollection = $localeCollection;
	}

	/**
	 * Returns best-matching Locale object based on the Accept-Language header
	 * provided as parameter. System default locale will be returned if no
	 * successful matches were done.
	 *
	 * @param string $acceptLanguageHeader The Accept-Language HTTP header
	 * @return \TYPO3\Flow\I18n\Locale Best-matching existing Locale instance
	 * @api
	 */
	public function detectLocaleFromHttpHeader($acceptLanguageHeader) {
		$detectedLocale = $this->getLocaleFromHttpHeader($acceptLanguageHeader);
		if ($detectedLocale !== NULL) {
			return $detectedLocale;
		}
		return $this->localizationService->getConfiguration()->getDefaultLocale();
	}

	/**
	 * Like detectLocaleFromHttpHeader, but returns NULL if no valuable
	 * Locale information could be extracted from the given header.
	 *
	 * @param string $acceptLanguageHeader The Accept-Language HTTP header
	 * @return \TYPO3\Flow\I18n\Locale Best matching locale or NULL if none could be found
	 * @api
	 */
	public function getLocaleFromHttpHeader($acceptLanguageHeader) {
		$acceptableLanguages = Utility::parseAcceptLanguageHeader($acceptLanguageHeader);
		if ($acceptableLanguages === FALSE) {
			return NULL;
		}

		foreach ($acceptableLanguages as $languageIdentifier) {
			if ($languageIdentifier === '*') {
				return NULL;
			}
			try {
				$locale = new Locale($languageIdentifier);
			} catch (Exception\InvalidLocaleIdentifierException $exception) {
				continue;
			}

			$bestMatchingLocale = $this->localeCollection->findBestMatchingLocale($locale);
			if ($bestMatchingLocale !== NULL) {
				return $bestMatchingLocale;
			}
		}
		return NULL;
	}

	/**
	 * Returns best-matching Locale object based on the locale identifier
	 * provided as parameter. System default locale will be returned if no
	 * successful matches were done.
	 *
	 * @param string $localeIdentifier The locale identifier as used in Locale class
	 * @return \TYPO3\Flow\I18n\Locale Best-matching existing Locale instance
	 * @api
	 */
	public function detectLocaleFromLocaleTag($localeIdentifier) {
		try {
			return $this->detectLocaleFromTemplateLocale(new Locale($localeIdentifier));
		} catch (Exception\InvalidLocaleIdentifierException $e) {
			return $this->localizationService->getConfiguration()->getDefaultLocale();
		}
	}

	/**
	 * Returns best-matching Locale object based on the template Locale object
	 * provided as parameter. System default locale will be returned if no
	 * successful matches were done.
	 *
	 * @param \TYPO3\Flow\I18n\Locale $locale The template Locale object
	 * @return \TYPO3\Flow\I18n\Locale Best-matching existing Locale instance
	 * @api
	 */
	public function detectLocaleFromTemplateLocale(Locale $locale) {
		$bestMatchingLocale = $this->localeCollection->findBestMatchingLocale($locale);
		if ($bestMatchingLocale !== NULL) {
			return $bestMatchingLocale;
		}
		return $this->localizationService->getConfiguration()->getDefaultLocale();
	}

	/**
	 * Loops through the DetectionStrategyInterface implementations given in $detectionStrategiesChain
	 * and asks each to detect a matching locale. If no Locale at all could be determined, NULL is returned.
	 *
	 * @param array $detectionStrategiesChain An array containing class names strings for objects implementing the DetectionStrategyInterface
	 * @return \TYPO3\Flow\I18n\Locale
	 */
	public function detectLocaleFromConfiguredStrategies(array $detectionStrategiesChain) {
		foreach ($detectionStrategiesChain as $configuredDetectorName) {
			$detector = $this->getDetectorObject($configuredDetectorName);
			$probablyDetectedLocale = $detector->detectLocale();
			if ($probablyDetectedLocale !== NULL) {
				return $probablyDetectedLocale;
			}
		}
		return NULL;
	}

	/**
	 * @param string $configuredDetectorName
	 * @return \TYPO3\Flow\I18n\Detection\DetectionStrategyInterface
	 * @throws \TYPO3\Flow\I18n\Exception
	 */
	protected function getDetectorObject($configuredDetectorName) {
		if (strpos($configuredDetectorName, '\\') === FALSE) {
			$detectorClassName = sprintf('TYPO3\Flow\I18n\Detection\%sDetectionStrategy', $configuredDetectorName);
		} else {
			$detectorClassName = $configuredDetectorName;
		}
		try {
			$detectorObject = $this->objectManager->get($detectorClassName);
		} catch (UnknownObjectException $exception) {
			throw new Exception(sprintf('The given locale detection strategy "%s" does not exist.', $detectorClassName), 1370947113, $exception);
		}
		if (!$detectorObject instanceof DetectionStrategyInterface) {
			throw new Exception(sprintf('The given locale detection strategy "%s" does not implement the DetectionStrategyInterface.', $detectorClassName), 1370947080);
		}
		return $detectorObject;
	}
}

<?php
namespace TYPO3\Flow\Security\RequestPattern;

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
 * This class holds a host URI pattern and decides, if a \TYPO3\Flow\Mvc\ActionRequest object matches against this pattern
 * Note: the pattern is a simple wildcard matching pattern, with * as the wildcard character.
 * Example: *.typo3.org will match "flow.typo3.org" and "neos.typo3.org", but not "typo3.org"
 *          www.mydomain.* will match all TLDs of www.mydomain, but not "blog.mydomain.net" or "mydomain.com"
 */
class Host implements \TYPO3\Flow\Security\RequestPatternInterface {

	/**
	 * The URI pattern
	 * @var string
	 */
	protected $uriPattern = '';

	/**
	 * Returns the set pattern.
	 *
	 * @return string The set pattern
	 */
	public function getPattern() {
		return $this->uriPattern;
	}

	/**
	 * Sets an URI pattern
	 *
	 * @param string $uriPattern The URL pattern
	 * @return void
	 */
	public function setPattern($uriPattern) {
		$this->uriPattern = $uriPattern;
	}

	/**
	 * Matches a \TYPO3\Flow\Mvc\RequestInterface against its set URL pattern rules
	 *
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request The request that should be matched
	 * @return boolean TRUE if the pattern matched, FALSE otherwise
	 */
	public function matchRequest(\TYPO3\Flow\Mvc\RequestInterface $request) {
		$uriPattern = str_replace('\\*', '.*', preg_quote($this->uriPattern, '/'));
		return (boolean)preg_match('/^' . $uriPattern . '$/', $request->getHttpRequest()->getUri()->getHost());
	}
}

?>
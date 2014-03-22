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
 * This class holds a CIDR IP pattern an decides, if a \TYPO3\Flow\Mvc\ActionRequest object matches against this pattern
 * The pattern can contain IPv4 and IPv6 addresses (including IPv6 wrapped IPv4 addresses).
 * @see http://tools.ietf.org/html/rfc4632
 * @see http://tools.ietf.org/html/rfc4291#section-2.3
 */
class Ip implements \TYPO3\Flow\Security\RequestPatternInterface {

	/**
	 * The CIDR styled IP pattern
	 * @var string
	 */
	protected $ipPattern = '';

	/**
	 * Returns the set pattern.
	 *
	 * @return string The set pattern
	 */
	public function getPattern() {
		return $this->ipPattern;
	}

	/**
	 * Sets an IP pattern (CIDR syntax)
	 *
	 * @param string $ipPattern The CIDR styled IP pattern
	 * @return void
	 */
	public function setPattern($ipPattern) {
		$this->ipPattern = $ipPattern;
	}

	/**
	 * Matches a CIDR range pattern against an IP
	 *
	 * @param string $ip The IP to match
	 * @param string $range The CIDR range pattern to match against
	 * @return boolean TRUE if the pattern matched, FALSE otherwise
	 */
	protected function cidrMatch($ip, $range)
	{
		if (strpos($range, '/') === FALSE) {
			$bits = NULL;
			$subnet = $range;
		} else {
			list ($subnet, $bits) = explode('/', $range);
		}

		$ip = inet_pton($ip);
		$subnet = inet_pton($subnet);
		if ($ip === FALSE || $subnet === FALSE) {
			return FALSE;
		}

		if (strlen($ip) > strlen($subnet)) {
			$subnet = str_pad($subnet, strlen($ip), chr(0), STR_PAD_LEFT);
		} elseif (strlen($subnet) > strlen($ip)) {
			$ip = str_pad($ip, strlen($subnet), chr(0), STR_PAD_LEFT);
		}

		if ($bits === NULL) {
			return ($ip === $subnet);
		} else {
			for ($i = 0; $i < strlen($ip); $i++) {
				$mask = 0;
				if ($bits > 0) {
					$mask = ($bits >= 8) ? 255 : (256 - (1 << (8 - $bits)));
					$bits -= 8;
				}
				if ((ord($ip[$i]) & $mask) !== (ord($subnet[$i]) & $mask)) {
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	/**
	 * Matches a \TYPO3\Flow\Mvc\RequestInterface against its set IP pattern rules
	 *
	 * @param \TYPO3\Flow\Mvc\RequestInterface $request The request that should be matched
	 * @return boolean TRUE if the pattern matched, FALSE otherwise
	 */
	public function matchRequest(\TYPO3\Flow\Mvc\RequestInterface $request) {
		$ipPattern = str_replace('SERVER_ADDR', $request->getHttpRequest()->getServerIpAddress(), $this->ipPattern);
		return (boolean)$this->cidrMatch($request->getHttpRequest()->getClientIpAddress(), $ipPattern);
	}
}

?>
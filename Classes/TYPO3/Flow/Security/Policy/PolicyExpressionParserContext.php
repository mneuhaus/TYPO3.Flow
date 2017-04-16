<?php
namespace TYPO3\Flow\Security\Policy;

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
use TYPO3\Flow\Security\Exception\InvalidPolicyException;

/**
 * A specialized pointcut expression parser tailored to policy expressions
 *
 * @Flow\Scope("singleton")
 * @Flow\Proxy(false)
 */
class PolicyExpressionParserContext extends \TYPO3\Flow\Aop\Pointcut\PointcutExpressionParserContext {

	/**
	 * @var array The resources array from the configuration.
	 */
	protected $methodResourcesTree = array();

	/**
	 * @param array $methodResourcesTree
	 */
	public function setMethodResourcesTree($methodResourcesTree) {
		$this->methodResourcesTree = $methodResourcesTree;
	}

	/**
	 * Walks recursively through the method resources tree.
	 *
	 * @param string $operator The operator
	 * @param string $pointcutExpression The pointcut expression (value of the designator)
	 * @param \TYPO3\Flow\Aop\Pointcut\PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the pointcut filter) will be added to this composite object.
	 * @param array &$trace
	 * @return void
	 * @throws \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException
	 */
	protected function parseDesignatorPointcut($operator, $pointcutExpression, \TYPO3\Flow\Aop\Pointcut\PointcutFilterComposite $pointcutFilterComposite, array &$trace = array()) {
		if (!isset($this->methodResourcesTree[$pointcutExpression])) {
			throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('The given resource was not defined: "' . $pointcutExpression . '".', 1222014591);
		}

		$pointcutFilterComposite->addFilter($operator, $this->parseMethodResources($this->methodResourcesTree[$pointcutExpression], array(), $trace));
	}
}
?>
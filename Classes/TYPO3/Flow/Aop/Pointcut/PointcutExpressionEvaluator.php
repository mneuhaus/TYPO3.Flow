<?php
namespace TYPO3\Flow\Aop\Pointcut;

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
 * The pointcut expression parser parses the definition of the place and circumstances
 * where advices can be inserted later on. The input of the parse() function is a string
 * from a pointcut- or advice annotation and returns a pointcut filter composite.
 *
 * @see \TYPO3\Flow\Aop\Pointcut, PointcutFilterComposite
 * @Flow\Scope("singleton")
 * @Flow\Proxy(false)
 */
class PointcutExpressionEvaluator {

	/**
	 * @var \TYPO3\Flow\Aop\Builder\ProxyClassBuilder
	 */
	protected $proxyClassBuilder;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var string
	 */
	protected $sourceHint = '';

	/**
	 * @param \TYPO3\Flow\Aop\Builder\ProxyClassBuilder $proxyClassBuilder
	 * @return void
	 */
	public function injectProxyClassBuilder(\TYPO3\Flow\Aop\Builder\ProxyClassBuilder $proxyClassBuilder) {
		$this->proxyClassBuilder = $proxyClassBuilder;
	}

	/**
	 * @param \TYPO3\Flow\Reflection\ReflectionService $reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\Flow\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param \TYPO3\Flow\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\Flow\Object\ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Parses a string pointcut expression and returns the pointcut
	 * objects accordingly
	 *
	 * @param string $pointcutExpression The expression defining the pointcut
	 * @param string $sourceHint A message giving a hint on where the expression was defined. This is used in error messages.
	 * @return PointcutFilterComposite A composite of class-filters, method-filters and pointcuts
	 * @throws \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException
	 * @throws \TYPO3\Flow\Aop\Exception
	 */
	public function evaluate($pointcutExpression, $sourceHint) {
		$this->sourceHint = $sourceHint;
echo '###############' . $pointcutExpression . PHP_EOL;
		if (!is_string($pointcutExpression) || strlen($pointcutExpression) === 0) {
			throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('Pointcut expression must be a valid string, ' . gettype($pointcutExpression) . ' given, defined in ' . $this->sourceHint, 1168874738);
		}

		$pointcutFilterComposite = new PointcutFilterComposite();
		$pointcutExpressionParserContext = new PointcutExpressionParserContext($this->proxyClassBuilder, $this->reflectionService, $this->objectManager, $pointcutFilterComposite);

		$parser = new PointcutExpressionParser($pointcutExpression, new \TYPO3\Eel\Context($pointcutExpressionParserContext), array());
		$parser->match_Expression();
//TODO: check the parsing result?!
		$blub = $pointcutExpressionParserContext->getPointcutFilterComposite();
\TYPO3\Flow\var_dump($blub);
		return $blub;
	}
}
?>
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
 * @Flow\Scope("singleton")
 * @Flow\Proxy(false)
 */
class PointcutExpressionParserContext {

	const PATTERN_MATCHVISIBILITYMODIFIER = '/^(public|protected) +/';
	const PATTERN_MATCHRUNTIMEEVALUATIONSDEFINITION = '/(?:
														(?:
															\s*(   "(?:\\\"|[^"])*"
																|\(.*?\)
																|\'(?:\\\\\'|[^\'])*\'
																|[a-zA-Z0-9\-_.]+
															)
															\s*(===?|!==?|<=|>=|<|>|in|contains|matches)\s*
															(   "(?:\\\"|[^"])*"
																|\(.*?\)
																|\'(?:\\\\\'|[^\'])*\'
																|[a-zA-Z0-9\-_.]+
															)
														)
														\s*,{0,1}?
													)+
												/x';
	const PATTERN_MATCHRUNTIMEEVALUATIONSVALUELIST = '/(?:
																	\s*(
																		"(?:\\\"|[^"])*"
																		|\'(?:\\\\\'|[^\'])*\'
																		|(?:[a-zA-Z0-9\-_.])+
																	)
																	\s*,{0,1}?
																)+
																/x';
	const PATTERN_MATCHMETHODNAMEANDARGUMENTS = '/^(?P<MethodName>.*)\((?P<MethodArguments>.*)\)$/';

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
	 * @var PointcutFilterComposite
	 */
	protected $pointcutFilterComposite;

	/**
	 * @var string
	 */
	protected $operator = '||';

	/**
	 * @param \TYPO3\Flow\Aop\Builder\ProxyClassBuilder $proxyClassBuilder
	 * @param \TYPO3\Flow\Reflection\ReflectionService $reflectionService
	 * @param \TYPO3\Flow\Object\ObjectManagerInterface $objectManager
	 * @param PointcutFilterComposite $pointcutFilterComposite PointcutFilterComposite $pointcutFilterComposite An instance of the pointcut filter composite. The result (ie. the class annotation filter) will be added to this composite object.
	 */
	public function __construct(\TYPO3\Flow\Aop\Builder\ProxyClassBuilder $proxyClassBuilder, \TYPO3\Flow\Reflection\ReflectionService $reflectionService, \TYPO3\Flow\Object\ObjectManagerInterface $objectManager, PointcutFilterComposite $pointcutFilterComposite) {
		$this->proxyClassBuilder = $proxyClassBuilder;
		$this->reflectionService = $reflectionService;
		$this->objectManager = $objectManager;
		$this->pointcutFilterComposite = $pointcutFilterComposite;
	}

	/**
	 * @param string $operator
	 */
	public function setOperator($operator) {
		$this->operator = $operator;
	}

	/**
	 * @param string $operator
	 */
	public function negateOperator() {
		if (strpos($this->operator, '!') === FALSE) {
			$this->operator = '!' . $this->operator;
		} else {
			$this->operator = substr($this->operator, 1);
		}
	}

	/**
	 * @return PointcutFilterComposite
	 */
	public function getPointcutFilterComposite() {
		return $this->pointcutFilterComposite;
	}

	/**
	 * Call a method on this context
	 *
	 * @param string $pointcutDesignator
	 * @param array $arguments Arguments to the method, if of type Context they will be unwrapped
	 * @return mixed
	 * @throws \Exception
	 */
	public function __call($pointcutDesignator, array $arguments = array()) {
	echo '++++++++' . $pointcutDesignator . PHP_EOL;
		switch ($pointcutDesignator) {
			case 'classAnnotatedWith':
			case 'class' :
			case 'methodAnnotatedWith':
			case 'methodTaggedWith' :
			case 'method' :
			case 'within' :
			case 'namedPointcut' :
			case 'filter' :
			case 'setting' :
				$parseMethodName = 'parseDesignator' . ucfirst($pointcutDesignator);
				call_user_func_array(array($this, $parseMethodName), $arguments);
				break;
			case 'evaluate' :
				call_user_func_array(array($this, 'parseRuntimeEvaluations'), $arguments);
				break;
			default :
				throw new \TYPO3\Flow\Aop\Exception('Support for pointcut designator "' . $pointcutDesignator . '" has not been implemented (yet), defined in ' /* TODO: . $this->sourceHint*/, 1168874740);
		}
	}

	/**
	 * Takes a class annotation filter pattern and adds a so configured class annotation filter to the
	 * filter composite object.
	 *
	 * @param string $classAnnotationPattern The pattern expression as configuration for the class annotation filter
	 * @return void
	 */
	protected function parseDesignatorClassAnnotatedWith($classAnnotationPattern) {
		$filter = new PointcutClassAnnotatedWithFilter($classAnnotationPattern);
		$filter->injectReflectionService($this->reflectionService);
		$this->pointcutFilterComposite->addFilter($this->operator, $filter);
	}

	/**
	 * Takes a class filter pattern and adds a so configured class filter to the
	 * filter composite object.
	 *
	 * @param string $classPattern The pattern expression as configuration for the class filter
	 * @return void
	 */
	protected function parseDesignatorClass($classPattern) {
		$filter = new PointcutClassNameFilter($classPattern);
		$filter->injectReflectionService($this->reflectionService);
		$this->pointcutFilterComposite->addFilter($this->operator, $filter);
	}

	/**
	 * Takes a method annotation filter pattern and adds a so configured method annotation filter to the
	 * filter composite object.
	 *
	 * @param string $methodAnnotationPattern The pattern expression as configuration for the method annotation filter
	 * @return void
	 * @deprecated since 1.0
	 */
	protected function parseDesignatorMethodAnnotatedWith($methodAnnotationPattern) {
		$filter = new PointcutMethodAnnotatedWithFilter($methodAnnotationPattern);
		$filter->injectReflectionService($this->reflectionService);
		$this->pointcutFilterComposite->addFilter($this->operator, $filter);
	}

	/**
	 * Takes a method tag filter pattern and adds a so configured method tag filter to the
	 * filter composite object.
	 *
	 * @param string $methodTagPattern The pattern expression as configuration for the method tag filter
	 * @return void
	 */
	protected function parseDesignatorMethodTaggedWith($methodTagPattern) {
		$filter = new PointcutMethodTaggedWithFilter($methodTagPattern);
		$filter->injectReflectionService($this->reflectionService);
		$this->pointcutFilterComposite->addFilter($this->operator, $filter);
	}

	/**
	 * Splits the parameters of the pointcut designator "method" into a class
	 * and a method part and adds the appropriately configured filters to the
	 * filter composite object.
	 *
	 * @param string $signaturePattern The pattern expression defining the class and method - the "signature"
	 * @return void
	 * @throws \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException if there's an error in the pointcut expression
	 */
	protected function parseDesignatorMethod($signaturePattern) {
		if (strpos($signaturePattern, '->') === FALSE) {
			throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('Syntax error: "->" expected in "' . $signaturePattern . '", defined in ' . $this->sourceHint, 1169027339);
		}
		$methodVisibility = $this->getVisibilityFromSignaturePattern($signaturePattern);
		list($classPattern, $methodPattern) = explode ('->', $signaturePattern, 2);
		if (strpos($methodPattern, '(') === FALSE ) {
			throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('Syntax error: "(" expected in "' . $methodPattern . '", defined in ' . $this->sourceHint, 1169144299);
		}

		$matches = array();
		preg_match(self::PATTERN_MATCHMETHODNAMEANDARGUMENTS, $methodPattern, $matches);

		$methodNamePattern = $matches['MethodName'];
		$methodArgumentPattern = $matches['MethodArguments'];
		$methodArgumentConstraints = $this->getArgumentConstraintsFromMethodArgumentsPattern($methodArgumentPattern);

		$classNameFilter = new PointcutClassNameFilter($classPattern);
		$classNameFilter->injectReflectionService($this->reflectionService);
		$methodNameFilter = new PointcutMethodNameFilter($methodNamePattern, $methodVisibility, $methodArgumentConstraints);
		$methodNameFilter->injectSystemLogger($this->objectManager->get('TYPO3\Flow\Log\SystemLoggerInterface'));
		$methodNameFilter->injectReflectionService($this->reflectionService);

		if ($this->operator !== '&&') {
			$subComposite = new PointcutFilterComposite();
			$subComposite->addFilter('&&', $classNameFilter);
			$subComposite->addFilter('&&', $methodNameFilter);

			$this->pointcutFilterComposite->addFilter($this->operator, $subComposite);
		} else {
			$this->pointcutFilterComposite->addFilter('&&', $classNameFilter);
			$this->pointcutFilterComposite->addFilter('&&', $methodNameFilter);
		}
	}

	/**
	 * Adds a class type filter to the poincut filter composite
	 *
	 * @param string $signaturePattern The pattern expression defining the class type
	 * @return void
	 */
	protected function parseDesignatorWithin($signaturePattern) {
		$filter = new PointcutClassTypeFilter($signaturePattern);
		$filter->injectReflectionService($this->reflectionService);
		$this->pointcutFilterComposite->addFilter($this->operator, $filter);
	}

	/**
	 * Splits the value of the pointcut designator "pointcut" into an aspect
	 * class- and a pointcut method part and adds the appropriately configured
	 * filter to the composite object.
	 *
	 * @param string $pointcutExpression The pointcut expression (value of the designator)
	 * @return void
	 * @throws \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException
	 */
	protected function parseDesignatorNamedPointcut($pointcutExpression) {
		if (strpos($pointcutExpression, '->') === FALSE) {
			throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('Syntax error: "->" expected in "' . $pointcutExpression . '", defined in ' . $this->sourceHint, 1172219205);
		}
		list($aspectClassName, $pointcutMethodName) = explode ('->', $pointcutExpression, 2);
		$pointcutFilter = new PointcutFilter($aspectClassName, $pointcutMethodName);
		$pointcutFilter->injectProxyClassBuilder($this->proxyClassBuilder);
		$this->pointcutFilterComposite->addFilter($this->operator, $pointcutFilter);
	}

	/**
	 * Adds a custom filter to the poincut filter composite
	 *
	 * @param string $filterObjectName Object Name of the custom filter (value of the designator)
	 * @return void
	 * @throws \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException
	 */
	protected function parseDesignatorFilter($filterObjectName) {
		$customFilter = $this->objectManager->get($filterObjectName);
		if (!$customFilter instanceof PointcutFilterInterface) {
			throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('Invalid custom filter: "' . $filterObjectName . '" does not implement the required PointcutFilterInterface, defined in ' . $this->sourceHint, 1231871755);
		}
		$this->pointcutFilterComposite->addFilter($this->operator, $customFilter);
	}

	/**
	 * Adds a setting filter to the pointcut filter composite
	 *
	 * @param string $configurationPath The path to the settings option, that should be used
	 * @return void
	 */
	protected function parseDesignatorSetting($configurationPath) {
		$filter = new PointcutSettingFilter($configurationPath);
		$filter->injectConfigurationManager($this->objectManager->get('TYPO3\Flow\Configuration\ConfigurationManager'));

		$this->pointcutFilterComposite->addFilter($this->operator, $filter);
	}

	/**
	 * Adds runtime evaluations to the pointcut filter composite
	 *
	 * @param string $runtimeEvaluations The runtime evaluations string
	 * @return void
	 */
	protected function parseRuntimeEvaluations($runtimeEvaluations) {
		$runtimeEvaluationsDefinition = array(
			$this->operator => array(
				'evaluateConditions' => $this->getRuntimeEvaluationConditionsFromEvaluateString($runtimeEvaluations)
			)
		);

		$this->pointcutFilterComposite->setGlobalRuntimeEvaluationsDefinition($runtimeEvaluationsDefinition);
	}

	/**
	 * Parses the signature pattern and returns the visibility modifier if any. If a modifier
	 * was found, it will be removed from the $signaturePattern.
	 *
	 * @param string &$signaturePattern The regular expression for matching the method() signature
	 * @return string Visibility modifier or NULL of none was found
	 * @throws \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException
	 */
	protected function getVisibilityFromSignaturePattern(&$signaturePattern) {
		$visibility = NULL;
		$matches = array();
		$numberOfMatches = preg_match_all(self::PATTERN_MATCHVISIBILITYMODIFIER, $signaturePattern, $matches, PREG_SET_ORDER);
		if ($numberOfMatches > 1) {
			throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('Syntax error: method name expected after visibility modifier in "' . $signaturePattern . '", defined in ' . $this->sourceHint, 1172492754);
		}
		if ($numberOfMatches === FALSE) {
			throw new \TYPO3\Flow\Aop\Exception\InvalidPointcutExpressionException('Error while matching visibility modifier in "' . $signaturePattern . '", defined in ' . $this->sourceHint, 1172492967);
		}
		if ($numberOfMatches === 1) {
			$visibility = $matches[0][1];
			$signaturePattern = trim(substr($signaturePattern, strlen($visibility)));
		}
		return $visibility;
	}

	/**
	* Parses the method arguments pattern and returns the corresponding constraints array
	*
	* @param string $methodArgumentsPattern The arguments pattern defined in the pointcut expression
	* @return array The corresponding constraints array
	*/
	protected function getArgumentConstraintsFromMethodArgumentsPattern($methodArgumentsPattern) {
		$matches = array();
		$argumentConstraints = array();

		preg_match_all(self::PATTERN_MATCHRUNTIMEEVALUATIONSDEFINITION, $methodArgumentsPattern, $matches);

		for ($i = 0; $i < count($matches[0]); $i++) {
			if ($matches[2][$i] === 'in' || $matches[2][$i] === 'matches') {
				$list = array();
				$listEntries = array();

				if (preg_match('/^\s*\(.*\)\s*$/', $matches[3][$i], $list) > 0) {
					preg_match_all(self::PATTERN_MATCHRUNTIMEEVALUATIONSVALUELIST, $list[0], $listEntries);
					$matches[3][$i] = $listEntries[1];
				}
			}

			$argumentConstraints[$matches[1][$i]]['operator'][] = $matches[2][$i];
			$argumentConstraints[$matches[1][$i]]['value'][] = $matches[3][$i];
		}
		return $argumentConstraints;
	}

	/**
	 * Parses the evaluate string for runtime evaluations and returns the corresponding conditions array
	 *
	 * @param string $evaluateString The evaluate string defined in the pointcut expression
	 * @return array The corresponding constraints array
	 */
	protected function getRuntimeEvaluationConditionsFromEvaluateString($evaluateString) {
		$matches = array();
		$runtimeEvaluationConditions = array();

		preg_match_all(self::PATTERN_MATCHRUNTIMEEVALUATIONSDEFINITION, $evaluateString, $matches);

		for ($i = 0; $i < count($matches[0]); $i++) {
			if ($matches[2][$i] === 'in' || $matches[2][$i] === 'matches') {
				$list = array();
				$listEntries = array();

				if (preg_match('/^\s*\(.*\)\s*$/', $matches[3][$i], $list) > 0) {
					preg_match_all(self::PATTERN_MATCHRUNTIMEEVALUATIONSVALUELIST, $list[0], $listEntries);
					$matches[3][$i] = $listEntries[1];
				}
			}

			$runtimeEvaluationConditions[] = array(
				'operator' => $matches[2][$i],
				'leftValue' => $matches[1][$i],
				'rightValue' => $matches[3][$i],
			);
		}
		return $runtimeEvaluationConditions;
	}
}
?>
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
 */
class PointcutExpressionParser extends \TYPO3\Eel\InterpretedEelParser {

	public function NotExpression_exp(&$result, $sub) {
	echo '|||||||||||||||||NotExpression_exp!' . PHP_EOL;
//negate the next operator
		$this->context->call('negateOperator');
		parent::NotExpression_exp($result, $sub);
	}

	public function Disjunction_rgt(&$result, $sub) {
		echo '|||||||||||||||||Disjunction_rgt||' . PHP_EOL;
var_dump($result);
var_dump($sub);
//set last operator to this
		$this->context->call('setOperator', array('||'));
		parent::Disjunction_rgt($result, $sub);
	}

	public function Conjunction_rgt(&$result, $sub) {
		echo '|||||||||||||||||Conjunction_rgt&&' . PHP_EOL;
		//set last operator to this
		$this->context->call('setOperator', array('&&'));
		parent::Conjunction_rgt($result, $sub);
	}

}

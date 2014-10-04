<?php
use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\PyStringNode;
use Flowpack\Behat\Tests\Behat\FlowContext;
use TYPO3\Flow\Cache\CacheManager;

require_once(__DIR__ . '/../../../../../../Application/Flowpack.Behat/Tests/Behat/FlowContext.php');
require_once(__DIR__ . '/SecurityTrait.php');
require_once(__DIR__ . '/SubProcess/SubProcess.php');

/**
 * Features context
 */
class FeatureContext extends BehatContext {

	use SecurityTrait;

	/**
	 * Initializes the context
	 *
	 * @param array $parameters Context parameters (configured through behat.yml)
	 */
	public function __construct(array $parameters) {
		$this->useContext('flow', new FlowContext($parameters));
		$flowContext = $this->getFlowSubContext();
		$this->objectManager = $flowContext->getObjectManager();
		$this->environment = $this->objectManager->get('TYPO3\Flow\Utility\Environment');
	}

	/**
	 * @Then /^I can (not )?call the method "([^"]*)" of class "([^"]*)"(?: with arguments "([^"]*)")?$/
	 */
	public function iCanCallTheMethodOfClass($not, $methodName, $className) {
		$subProcessResponse = $this->getSubProcess()->execute('typo3.flow.tests.functional:behathelper:callmethod --className ' . $className . ' --methodName ' . $methodName);
		if ($not === '') {
			\PHPUnit_Framework_Assert::assertStringStartsWith('SUCCESS:', $subProcessResponse, 'Expected "SUCCESS: ..." output got "' . $subProcessResponse . '"');
		} else {
			\PHPUnit_Framework_Assert::assertSame('EXCEPTION: 1222268609', $subProcessResponse, 'Expected AccessDeniedException (#1222268609), got "' . $subProcessResponse . '"');
		}
	}
}

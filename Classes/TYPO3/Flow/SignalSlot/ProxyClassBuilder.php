<?php
namespace TYPO3\Flow\SignalSlot;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Object\Configuration\Configuration;
use TYPO3\Flow\Object\Configuration\ConfigurationArgument;
use TYPO3\Flow\Object\Configuration\ConfigurationProperty;
use TYPO3\Flow\Utility\Arrays;

/**
 * A Proxy Class Builder which automatically wires
 * slots to signals based on the slot annotation
 *
 * @Flow\Scope("singleton")
 * @Flow\Proxy(false)
 */
class ProxyClassBuilder {
	/**
	 * @var string
	 */
	protected $slots = array();

	/**
	 * @var \TYPO3\Flow\Cache\CacheManager
	 */
	protected $cacheManager;

	/**
	 * @var \TYPO3\Flow\Object\Proxy\Compiler
	 */
	protected $compiler;

	/**
	 * @var \TYPO3\Flow\SignalSlot\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * @var \TYPO3\Flow\Utility\Environment
	 */
	protected $environment;

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\Flow\Object\CompileTimeObjectManager
	 */
	protected $objectManager;

	/**
	 * @param \TYPO3\Flow\Cache\CacheManager $cacheManager
	 * @return void
	 */
	public function injectCacheManager(\TYPO3\Flow\Cache\CacheManager $cacheManager) {
		$this->cacheManager = $cacheManager;
	}

	/**
	 * @param \TYPO3\Flow\Object\Proxy\Compiler $compiler
	 * @return void
	 */
	public function injectCompiler(\TYPO3\Flow\Object\Proxy\Compiler $compiler) {
		$this->compiler = $compiler;
	}

	/**
	 * @param \TYPO3\Flow\SignalSlot\Dispatcher $dispatcher
	 * @return void
	 */
	public function injectDispatcher(\TYPO3\Flow\SignalSlot\Dispatcher $dispatcher) {
		$this->dispatcher = $dispatcher;
	}

	/**
	 * @param \TYPO3\Flow\Utility\Environment $environment
	 * @return void
	 */
	public function injectEnvironment(\TYPO3\Flow\Utility\Environment $environment) {
		$this->environment = $environment;
	}

	/**
	 * @param \TYPO3\Flow\Reflection\ReflectionService $reflectionService
	 * @return void
	 */
	public function injectReflectionService(\TYPO3\Flow\Reflection\ReflectionService $reflectionService) {
		$this->reflectionService = $reflectionService;
	}

	/**
	 * @param \TYPO3\Flow\Object\CompileTimeObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\Flow\Object\CompileTimeObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Analyzes the Object Configuration provided by the compiler and builds the necessary PHP code for the proxy classes
	 * to realize dependency injection.
	 *
	 * @return void
	 */
	public function build() {
		$this->objectConfigurations = $this->objectManager->getObjectConfigurations();
		$cache = $this->cacheManager->getCache('Flow_SignalSlot_Annotations');
		$classSlots = $cache->get('ClassSlots');

		if ($classSlots === FALSE) {
			$classSlots = array();
		}
		$classSlots = array();

		foreach ($this->objectConfigurations as $objectName => $objectConfiguration) {
			$className = $objectConfiguration->getClassName();
			if ($this->compiler->hasCacheEntryForClass($className) === TRUE) {
				continue;
			}

			if ($objectName !== $className || $this->reflectionService->isClassAbstract($className) || $this->reflectionService->isClassFinal($className)) {
				continue;
			}
			$proxyClass = $this->compiler->getProxyClass($className);
			if ($proxyClass === FALSE) {
				continue;
			}

			$package = $objectConfiguration->getPackageKey();

			$classMethods = $this->reflectionService->getClassMethodsAnnotatedWith($className, 'TYPO3\Flow\Annotations\Slot');
			if (count($classMethods) > 0) {
				$classSlots[$className] = array();
			}
			foreach ($classMethods as $methodName) {
				$annotationInstance = $this->reflectionService->getMethodAnnotation($className, $methodName, 'TYPO3\Flow\Annotations\Slot');

				$slot = array(
					'signalClassName' => $annotationInstance->class,
					'signalName' => $annotationInstance->signal,
					'slotClassName' => $className,
					'slotName' => $methodName,
					'passSignalInformation' => $annotationInstance->passSignalInformation === TRUE
				);
				$classSlots[$className][] = $slot;
				$this->dispatcher->connect($slot['signalClassName'], $slot['signalName'], $slot['slotClassName'], $slot['slotName'], $slot['passSignalInformation']);
			}
		}

		// Cleanup deleted or renamed classes
		foreach ($classSlots as $className => $slots) {
			if (class_exists($className) === FALSE) {
				unset($classSlots[$className]);
			}
		}

		$cache->set('ClassSlots', $classSlots);

		$tempPath = $this->environment->getPathToTemporaryDirectory();
		\TYPO3\Flow\Utility\Files::createDirectoryRecursively($tempPath);
		file_put_contents($tempPath . '/SignalSlots.php', '<?php return ' . str_replace('\\\\', '\\', var_export($classSlots, true)) . '; ?>');
	}
}

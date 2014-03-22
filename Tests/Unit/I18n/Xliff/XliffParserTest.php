<?php
namespace TYPO3\Flow\Tests\Unit\I18n\Xliff;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use TYPO3\Flow\I18n\Xliff\XliffParser;

/**
 * Testcase for the XliffParser
 *
 */
class XliffParserTest extends \TYPO3\Flow\Tests\UnitTestCase {

	/**
	 * @test
	 */
	public function parsesXliffFileCorrectly() {
		$mockFilenamePath = __DIR__ . '/../Fixtures/MockXliffData.xlf';

		$parser = new XliffParser();
		$result = $parser->getParsedData($mockFilenamePath);

		$this->assertSame(array_keys($result['files']), array('foo.po', 'more', 0));
		$this->assertSame(array_keys($result['transUnitIdsInFiles']), array('key1','key2','key3','keyInAnotherFile','keyInNestedGroup'));
		$this->assertSame($result['files']['foo.po'], $result['transUnitIdsInFiles']['key1']['file']);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\I18n\Xliff\Exception\InvalidXliffDataException
	 */
	public function missingIdInSingularTransUnitCausesException() {
		$mockFilenamePath = __DIR__ . '/../Fixtures/MockInvalidXliffData.xlf';

		$parser = new XliffParser();
		$parser->getParsedData($mockFilenamePath);
	}

	/**
	 * @test
	 * @expectedException \TYPO3\Flow\I18n\Xliff\Exception\InvalidXliffDataException
	 */
	public function missingIdInPluralTransUnitCausesException() {
		$mockFilenamePath = __DIR__ . '/../Fixtures/MockInvalidPluralXliffData.xlf';

		$parser = new XliffParser();
		$parser->getParsedData($mockFilenamePath);
	}

}

<?php
namespace TYPO3\Flow\I18n\Xliff;

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
use TYPO3\Flow\I18n\Locale;

/**
 * A class which parses XLIFF file to simple but useful array representation.
 *
 * As for now, this class supports only basic XLIFF specification.
 * - multiple <file> and <group> elements are possible, being able to be nested
 *   following the specification.
 * - reads only "source" and "target" in "trans-unit" tags
 *
 * @Flow\Scope("singleton")
 * @throws Exception\InvalidXliffDataException
 * @see http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html [1]
 * @see http://docs.oasis-open.org/xliff/v1.2/xliff-profile-po/xliff-profile-po-1.2-cd02.html#s.detailed_mapping.tu [2]
 */
class XliffParser extends \TYPO3\Flow\I18n\AbstractXmlParser {

	/**
	 * Returns array representation of XLIFF data, starting from a root node.
	 * So far this intends to keep track of the <file> occurrences and each <trans-unit> inside.
	 * <group> tags are not regarded specially, but recursively crawled through.
	 * The final output of this file would look like the following::
	 *
	 * 	files:
	 * 	  'somefile.html': (the `original` attribute of the <file> element)
	 * 	    sourceLocale: {TYPO3\Flow\I18n\Locale object of the `source-language` attribute}
	 * 	    targetLocale: {TYPO3\Flow\I18n\Locale object of the `target-language` attribute, if present}
	 * 	    translationUnits:
	 * 	      'msg112178': (the `id` attribute of the <trans-unit> element
	 * 	        - 'some sample message'
	 * 	        - 'some sample messages, plural'
	 * 	      'lbl11815127':
	 * 	        - 'some single sampla label, having no plural'
	 *
	 * 	transUnitIdsInFiles:
	 * 	  'msg112178':
	 * 	    file:
	 * 	      {array reference pointer to 'somefile.html' above}
	 * 	      sourceLocale: {see above}
	 * 	      translationUnits:
	 *        ... etc. like above
	 *
	 * @param \SimpleXMLElement $root A root node
	 * @return array An array representing parsed XLIFF
	 * @throws Exception\InvalidXliffDataException
	 */
	protected function doParsingFromRoot(\SimpleXMLElement $root) {
		$parsedData = array(
			'files' => array(),
			'transUnitIdsInFiles' => array()
		);

		foreach ($root->file as $fileElement) {
			$parsedFileData = array(
				'sourceLocale' => new Locale((string)$fileElement->attributes()->{'source-language'}),
				'targetLocale' => $fileElement->attributes()->{'target-language'} ? new Locale((string)$fileElement->attributes()->{'target-language'}) : NULL,
			);

			foreach ($fileElement->body->children() as $bodyChildElement) {
				if (in_array($bodyChildElement->getName(), array('group', 'bin-unit', 'trans-unit'))) {
					$handlerMethodName = $this->getHandlerMethodNameFromElementName($bodyChildElement->getName());
					call_user_func_array(array($this, $handlerMethodName), array($bodyChildElement, &$parsedFileData));
				} else {
					throw new Exception\InvalidXliffDataException(sprintf('A not allowed element "%s" was found as child of an /xliff/file/body element, validate your XLIFF files. Only allowed are group, trans-unit, bin-unit elements.', $bodyChildElement->getName()), 1371022120);
				}
			}
			$fileOriginalName = (string)$fileElement->attributes()->original;
			if ($fileOriginalName !== '') {
				$parsedData['files'][$fileOriginalName] = &$parsedFileData;
			} else {
				$parsedData['files'][] = &$parsedFileData;
			}
			if (isset($parsedFileData['translationUnits'])) {
				$parsedData['transUnitIdsInFiles'] = array_merge($parsedData['transUnitIdsInFiles'], array_fill_keys(array_keys($parsedFileData['translationUnits']), array('file' => &$parsedFileData)));
			}
				// unset in order to not modify the references anymore
			unset($parsedFileData);
		}

		return $parsedData;
	}

	/**
	 * Parses a <trans-unit> element
	 *
	 * @param \SimpleXMLElement $element a <trans-unit> element
	 * @param array $parsedData
	 *
	 * @return void
	 * @throws Exception\InvalidXliffDataException
	 */
	protected function parseTransUnitElement(\SimpleXMLElement $element, array &$parsedData) {
		if (!isset($element['id'])) {
			throw new Exception\InvalidXliffDataException('A trans-unit tag without id attribute was found, validate your XLIFF files.', 1329399257);
		}
		if (isset($element['approved'])) {
			if ((string)$element['approved'] === 'no') {
				return;
			} elseif ((string)$element['approved'] !== 'yes') {
				throw new Exception\InvalidXliffDataException(sprintf('An "approved" attribute with an unsupported value "%s" was found, validate your XLIFF files.', $element['approved']), 1371033971);
			}
		}
		$parsedData['translationUnits'][(string)$element['id']][0] = array(
			'source' => (string)$element->source,
			'target' => (string)$element->target,
		);
	}

	/**
	 * Parses a <group> element. If it has an attribute [restype="x-gettext-plurals"] then
	 * it's handled for plural forms; in any other case, it's followed its children recursively,
	 * looking for further <group> or <trans-unit> elements.
	 *
	 * @param \SimpleXMLElement $element a <group> element
	 * @param array $parsedData
	 *
	 * @return void
	 * @throws Exception\InvalidXliffDataException
	 */
	protected function parseGroupElement(\SimpleXMLElement $element, array &$parsedData) {
		if (isset($element['restype']) && (string)$element['restype'] === 'x-gettext-plurals') {
			$parsedTranslationElement = array();
			foreach ($element->children() as $translationPluralForm) {
				if ($translationPluralForm->getName() === 'trans-unit') {
						// When using plural forms, ID looks like this: 1[0], 1[1] etc
					$formIndex = substr((string)$translationPluralForm['id'], strpos((string)$translationPluralForm['id'], '[') + 1, -1);

					$parsedTranslationElement[(int)$formIndex] = array(
						'source' => (string)$translationPluralForm->source,
						'target' => (string)$translationPluralForm->target,
					);
				}
			}

			if (!empty($parsedTranslationElement)) {
				if (isset($element->{'trans-unit'}[0]['id'])) {
					$id = (string)$element->{'trans-unit'}[0]['id'];
					$id = substr($id, 0, strpos($id, '['));
				} else {
					throw new Exception\InvalidXliffDataException('A trans-unit tag without id attribute was found, validate your XLIFF files.', 1329399258);
				}

				$parsedData['translationUnits'][$id] = $parsedTranslationElement;
			}
		} else {
			foreach ($element->children() as $groupChild) {
				if (in_array($groupChild->getName(), array('group', 'trans-unit'))) {
					$handlerMethodName = $this->getHandlerMethodNameFromElementName($groupChild->getName());
					call_user_func_array(array($this, $handlerMethodName), array($groupChild, &$parsedData));
				}
			}
		}
	}

	/**
	 * Stub in order to silently pass by such elements.
	 *
	 * @param \SimpleXMLElement $element a <bin-unit> element
	 * @param array $parsedData
	 *
	 * @return void
	 */
	protected function parseBinUnitElement($element, array &$parsedData) {
	}

	/**
	 * Gets a method name depending of the given element name, for example, "trans-unit" would return parseTransUnitElement.
	 *
	 * @param string $elementName
	 * @return string
	 */
	protected function getHandlerMethodNameFromElementName($elementName) {
		$handlerMethodName = ucfirst(strtolower($elementName));
		$handlerMethodName = sprintf('parse%sElement', preg_replace_callback('/[^a-z0-9]([a-z0-9])/i', function ($matches) {
			return strtoupper($matches[1]);
		}, $handlerMethodName));
		return $handlerMethodName;
	}
}

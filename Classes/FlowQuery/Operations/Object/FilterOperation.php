<?php
namespace TYPO3\Eel\FlowQuery\Operations\Object;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Eel".                  *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\FLOW3\Annotations as FLOW3;

/**
 * Filter operation, limiting the set of objects
 */
class FilterOperation extends \TYPO3\Eel\FlowQuery\Operations\AbstractOperation {

	/**
	 * {@inheritdoc}
	 *
	 * @var string
	 */
	static protected $shortName = 'filter';

	/**
	 * {@inheritdoc}
	 *
	 * @param \TYPO3\Eel\FlowQuery\FlowQuery $flowQuery the FlowQuery object
	 * @param array $arguments the arguments for this operation
	 * @return mixed|null if the operation is final, the return value
	 */
	public function evaluate(\TYPO3\Eel\FlowQuery\FlowQuery $flowQuery, array $arguments) {
		if (!isset($arguments[0]) || empty($arguments[0])) {
			return;
		}
		if (!is_string($arguments[0])) {
			throw new \TYPO3\Eel\FlowQuery\FizzleException('filter operation expects string argument', 1332489625);
		}
		$filter = $arguments[0];

		$parsedFilter = \TYPO3\Eel\FlowQuery\FizzleParser::parseFilterGroup($filter);

		$filteredContext = array();
		$context = $flowQuery->getContext();
		foreach ($context as $element) {
			if ($this->matchesFilterGroup($element, $parsedFilter)) {
				$filteredContext[] = $element;
			}
		}
		$flowQuery->setContext($filteredContext);
	}

	/**
	 * Evaluate Filter Group. An element matches the filter group if it
	 * matches at least one part of the filter group.
	 *
	 * Filter Group is something like "[foo], [bar]"
	 *
	 * @param object $element
	 * @param array $parsedFilter
	 * @return boolean TRUE if $element matches filter group, FALSE otherwise
	 */
	protected function matchesFilterGroup($element, array $parsedFilter) {
		foreach ($parsedFilter['Filters'] as $filter) {
			if ($this->matchesFilter($element, $filter)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Match a single filter, i.e. [foo]. It matches only if all filter parts match.
	 *
	 * @param object $element
	 * @param string $filter
	 * @return boolean TRUE if $element matches filter, FALSE otherwise
	 */
	protected function matchesFilter($element, $filter) {
		if (isset($filter['PropertyNameFilter']) && !$this->matchesPropertyNameFilter($element, $filter['PropertyNameFilter'])) {
			return FALSE;
		}

		if (isset($filter['AttributeFilters'])) {
			foreach ($filter['AttributeFilters'] as $attributeFilter) {
				if (!$this->matchesAttributeFilter($element, $attributeFilter)) {
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	/**
	 * For generic objects, we do not support property name filters.
	 *
	 * @param object $element
	 * @param string $propertyNameFilter
	 * @throws \TYPO3\Eel\FlowQuery\FizzleException
	 */
	protected function matchesPropertyNameFilter($element, $propertyNameFilter) {
		throw new \TYPO3\Eel\FlowQuery\FizzleException('Property Name filter not supported for generic objects.', 1332489796);
	}

	/**
	 * Match a single attribute filter
	 *
	 * @param type $element
	 * @param type $attributeFilter
	 * @return type
	 */
	protected function matchesAttributeFilter($element, $attributeFilter) {
		if ($attributeFilter['Identifier'] !== NULL) {
			$value = $this->getPropertyPath($element, $attributeFilter['Identifier']);
		} else {
			$value = $element;
		}
		$operand = NULL;
		if (isset($attributeFilter['Operand'])) {
			$operand = $attributeFilter['Operand'];
		}

		return $this->evaluateOperator($value, $attributeFilter['Operator'], $operand);
	}

	/**
	 * Evaluate a property path. This is outsourced to a single method
	 * to make overriding this functionality easy.
	 *
	 * @param object $element
	 * @param string $propertyPath
	 * @return mixed
	 */
	protected function getPropertyPath($element, $propertyPath) {
		return \TYPO3\FLOW3\Reflection\ObjectAccess::getPropertyPath($element, $propertyPath);
	}

	/**
	 * Evaluate an operator
	 *
	 * @param mixed $value
	 * @param string $operator
	 * @param mixed $operand
	 * @return boolean
	 */
	protected function evaluateOperator($value, $operator, $operand) {
		switch ($operator) {
			case '=':
				return $value === $operand;
			case '$=':
				return strrpos($value, $operand) === strlen($value) - strlen($operand);
			case '^=':
				return strpos($value, $operand) === 0;
			case '*=':
				return strpos($value, $operand) !== FALSE;
			case 'instanceof':
				return ($value instanceof $operand);
			default:
				return ($value !== NULL);
		}
	}
}
?>
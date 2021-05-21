<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CustomerSegment\Model\Segment\Condition\Sales;

/**
 * Order numbers condition
 */
class Ordersnumber extends \Magento\CustomerSegment\Model\Segment\Condition\Sales\Combine
{
    /**
     * Name of condition for displaying as html
     *
     * @var string
     */
    protected $frontConditionName = 'Number of Orders';

    /**
     * @inheritDoc
     */
    protected function getConditionSql($operator, $value)
    {
        $condition = $this->getResource()
            ->getConnection()
            ->getCheckSql("COUNT(*) {$operator} {$value}", 1, 0);
        return new \Zend_Db_Expr($condition);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareConditionsSql($customer, $website, $isFiltered = true)
    {
        if ($this->includeCustomersWithZeroOrders()) {
            $aggregator = $this->getAggregator() == 'all' ? ' AND ' : ' OR ';
            $required = $this->_getRequiredValidation();
            $conditions = $this->processCombineSubFilters($website, $required, []);
            $operator = $operator = $this->getResource()->getSqlOperator($this->getOperator());
            $value = $this->getResource()->getConnection()->quote((double) $this->getValue());
            $select = $this->getResource()
                ->createSelect()
                ->from(
                    ['customer_entity' => $this->getResource()->getTable('customer_entity')],
                    []
                );
            $conditionSelect = $this->getResource()
                ->createSelect()
                ->from(
                    ['sales_order' => $this->getResource()->getTable('sales_order')],
                    [$this->getConditionSql($operator, $value)]
                )
                ->where('sales_order.customer_id = customer_entity.entity_id');

            $this->_limitByStoreWebsite($conditionSelect, $website, 'sales_order.store_id');

            if (!empty($conditions)) {
                $conditionSelect->where(implode($aggregator, $conditions));
            }

            if ($isFiltered) {
                $select->columns([$conditionSelect]);
                $select->where($this->_createCustomerFilter($customer, 'customer_entity.entity_id'));
            } else {
                $select->columns(['customer_entity.entity_id']);
                $select->having($conditionSelect);
            }
            return $select;
        }

        return parent::_prepareConditionsSql($customer, $website, $isFiltered);
    }

    /**
     * @inheritdoc
     */
    public function getConditionsSql($customer, $website, $isFiltered = true)
    {
        if ($this->includeCustomersWithZeroOrders()) {
            return $this->_prepareConditionsSql($customer, $website, $isFiltered);
        }

        return parent::getConditionsSql($customer, $website, $isFiltered);
    }

    /**
     * Checks if customers with zero orders match the condition
     *
     * Returns true if zero satisfies the condition. For instance:
     * - Total Number of Orders is equal or less than 2: should include customers with 0 or 1 or 2 orders
     * - Total Number of Orders is less than 2: should include customers with 0 or 1 order
     *
     * @return bool
     */
    private function includeCustomersWithZeroOrders(): bool
    {
        return $this->check(0);
    }

    /**
     * Checks if provided value satisfies the condition
     *
     * @param int $value
     * @return bool
     */
    private function check(int $value): bool
    {
        $operand = (int) $this->getValue();
        switch ($this->getOperator()) {
            case '==':
                return $value == $operand;
            case '!=':
                return $value != $operand;
            case '>=':
                return $value >= $operand;
            case '<=':
                return $value <= $operand;
            case '>':
                return $value > $operand;
            case '<':
                return $value < $operand;
            default:
                return false;
        }
    }
}

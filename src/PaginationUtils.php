<?php

namespace App;

use InvalidArgumentException;
use Pebble\URL;

class PaginationUtils
{

    /**
     * Var holding default ORDER BY
     */
    private $order_by_default = [];

    /**
     * Sets order by default, e.g. `['title' => 'ASC', 'updated' => 'DESC']`
     */
    public function __construct(array $order_by_default)
    {
        $this->order_by_default = $order_by_default;
    }


    private $should_change_field_order = true;

    /**
     * Set if changing of field order should happen
     * `['title' => 'ASC', 'updated' => 'DESC']`to `['updated' => 'ASC', 'title' => 'ASC']`
     * If false it is only the DIRECTION part of the ORDER BY that will change
     */
    public function setShouldChangeFieldOrder(bool $val)
    {
        $this->should_change_field_order = $val;
    }

    /**
     * Validate a field. Checks if it is set `$order_by_default` fields
     */
    private function validateField(string $order_by)
    {
        $fields = array_keys($this->order_by_default);
        if (!in_array($order_by, $fields)) {
            throw new InvalidArgumentException("$order_by is not a allowed order by field");
        }
    }

    /**
     * Checks diretion, it can only be 'ASC' or 'DESC'
     */
    private function validateDirection(string $direction)
    {
        $direction = mb_strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException("$direction is not an allowed order by direction");
        }
    }

    private function getNewOrderBy(array $order_by): array
    {

        // Check if ORDER BY should be altered
        $order_by_field = $_GET['alter'] ?? null;
        if (!$order_by_field) {
            return $order_by;
        }

        $this->validateField($order_by_field);

        // Change direction of field
        if ($order_by[$order_by_field] === 'ASC') {
            $direction = 'DESC';
        } else {
            $direction = 'ASC';
        }

        if (!$this->should_change_field_order) {
            $order_by[$order_by_field] = $direction;
            return $order_by;
        }

        $new_order_by = [];

        // Set altered field as first ORDER BY
        $new_order_by[$order_by_field] = $direction;
        unset($order_by[$order_by_field]);

        // Add the rest of the fields from current ORDER BY
        foreach ($order_by as $field => $direction) {
            $new_order_by[$field] = $direction;
        }

        return $new_order_by;
    }

    /**
     * Get the ORDER BY parameters from the URL or get the default ORDER BY
     * @return array $order_by , e.b. `['title' => 'ASC', 'updated' => 'DESC']`
     */
    public function getOrderByFromQuery()
    {
        $order_by = $_GET['order_by'] ?? null;
        if (!$order_by) {
            return $this->order_by_default;
        }

        // Validate
        foreach ($order_by as $field => $direction) {
            $this->validateField($field);
            $this->validateDirection($direction);
        }

        return $this->getNewOrderBy($order_by);
    }


    /**
     * Get a pagination URL pattern used with paginator where ORDER BY part is added to the URL query
     */
    public function getPaginationURLPattern(string $url)
    {
        $query['order_by'] = $this->getOrderByFromQuery();
        $query_str = http_build_query($query);
        return $url . '?' . $query_str . '&' . 'page=(:num)';
    }


    /**
     * Get a URL where a new ORDER BY is indicated using `$_GET['alter'] = 'field'`
     * @param string $field 
     */
    public function getAlterOrderUrl(string $field)
    {

        $query['order_by'] = $this->getOrderByFromQuery();
        $query['page'] = (int)URL::getQueryPart('page') ?? 1;

        $route = strtok($_SERVER["REQUEST_URI"], '?');
        return  $route . '?' . http_build_query($query) . "&alter=$field";
    }

    /**
     * Get a arrow showing current direction of a field
     */
    public function getCurrentDirectionArrow(string $field)
    {

        $order_by = $this->getOrderByFromQuery();
        $direction = $order_by[$field];

        if ($direction == 'ASC') {
            return "↑";
        } else {
            return "↓";
        }
    }
}

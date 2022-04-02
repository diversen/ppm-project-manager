<?php

namespace App;

use InvalidArgumentException;
use Pebble\URL;

class PaginationUtils {

    private $order_by_allowed = [];
    private $order_by_default_field;
    public function __construct(array $order_by_allowed, string $order_by_default_field ) {
        $this->order_by_allowed = $order_by_allowed;
        $this->order_by_default_field = $order_by_default_field;
    }

    public function getOrderByDefault() {
        return [$this->order_by_default_field => 'ASC'];
    }

    private function validateField(string $order_by) {
        if (!in_array($order_by, $this->order_by_allowed)) {
            throw new InvalidArgumentException("$order_by is not a allowed order by field"); 
        }
    }

    private function validateDirection(string $direction) {
        $direction = mb_strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException("$direction is not an allowed order by direction"); 
        }
    }



    /**
     * Get ORDER BY from $_GET['order_by'] and $_GET['direction']
     * e.g. ['table field' => 'DESC']
     * Return as an array that can be used in e.g DB::getOrderBySql($order_by)
     */
    public function getOrderByFromQuery() {
        $order_by = $_GET['order_by'] ?? null;
        if (!$order_by) {
            return $this->getOrderByDefault();
        }

        $direction = $_GET['direction'] ?? 'ASC';

        $this->validateField($order_by);
        $this->validateDirection($direction);

        return [$order_by => $direction];
    }


    /**
     * Get order by as URL query
     * e.g.: order_by=title&direction=DESC
     */
    public function getOrderByQueryPart() {
        
        $order_by = $this->getOrderByFromQuery();
        $order['order_by'] = array_key_first($order_by);
        $order['direction'] = reset($order_by); 
        $order_by_query = http_build_query($order);
        return $order_by_query;
    }

    /**
     * Get a pagination URL pattern used with paginator where order by is added to the query
     */
    public function getPaginationURLPattern(string $url) {
        return $url . '?' . $this->getOrderByQueryPart($this->order_by_allowed) . '&' . 'page=(:num)';
    }

    private function getOrderByDirection($field) {

        // Defaults
        $this->validateField($field);

        $query['order_by'] = $field;
        $query['direction'] = 'ASC';
        
        $order_by = $_GET['order_by'] ?? null;
        $direction = $_GET['direction'] ?? null;

        if ($order_by === $field) {
            if ($direction === 'ASC') {
                $query['direction'] = 'DESC';
            } else {
                $query['direction'] = 'ASC';
            }
        }

        return $query;
    }

    /**
     * Get a URL that can be used for ordering data. It is build on current $_GET data 
     * @param string $field 
     */
    public function getOrderByUrl(string $field) {
        
        
        $query = $this->getOrderByDirection($field);
        
        // Add current page
        $query['page'] = URL::getQueryPart('page') ?? '1';
        
        // Use current route
        $route = strtok($_SERVER["REQUEST_URI"], '?');
        return  $route . '?' . http_build_query($query);
    }

    public function getCurrentDirectionArrow(string $field) {

        $order_by = $_GET['order_by'] ?? null;
        if (!$order_by) {
            if ($field === $this->order_by_default_field) {
                return "↑";
            } else {
                return "";
            }
        }

        $this->validateField($field);
        
        if ($order_by === $field) {
            $query = $this->getOrderByDirection($field);
            if ($query['direction'] === 'ASC') {
                return "↓";
            } else {
                return "↑";                
            }
        }
    }
}
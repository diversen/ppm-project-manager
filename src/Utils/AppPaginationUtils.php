<?php

namespace App\Utils;


use Exception;
use Pebble\Pager;
use Pebble\Pagination\PaginationUtils;
use Pebble\Service\DBService;
use JasonGrimes\Paginator;

class AppPaginationUtils
{

    public function getPaginator(
        int $total_items, int $items_per_page, int $current_page, string $url, array $default_order = [], int $max_pages = 10, string $session_key = null) {

        $pagination_utils = new PaginationUtils($default_order, $session_key);
        $url_pattern = $pagination_utils->getPaginationURLPattern($url);

        $paginator = new Paginator($total_items, $items_per_page, $current_page, $url_pattern);
        $paginator->setMaxPagesToShow($max_pages);

        return $paginator;

    }
    /**
     * @param string $table_name table to paginate
     * @param string $url_pattern URL pattern to use for pagination, e.g. `/admin/users`
     * @param array $column_order Default `ORDER BY` parameters, e.g. `['title' => 'ASC', 'updated' => 'DESC']`
     * @param int $per_page Number of items per page
     * @param int $max_pages Maximum number of pages to show in pagination
     * @return array<array, JasonGrimes\Paginator>
     * @throws Exception
    
     */
    public static function getRowsAndPaginator($table_name, $primary_key, $url_pattern, $column_order = [], $per_page = 10, $max_pages = 10) {

        $db = (new DBService())->getDB();

        $order_by_session_key = 'order_by_' . $table_name;

        $pagination_utils = new PaginationUtils($column_order, $order_by_session_key);
        $order_by = $pagination_utils->getOrderByFromRequest($order_by_session_key);

        $url_pattern = $pagination_utils->getPaginationURLPattern("/admin/table/$table_name");
        $num_rows = $db->getTableNumRows($table_name, $primary_key);
        $pager = new Pager($num_rows, $per_page);

        $paginator = new Paginator($num_rows, $pager->limit, $pager->page, $url_pattern);
        $paginator->setMaxPagesToShow($max_pages);

        $rows = $db->getAll($table_name, [], $order_by, [$pager->offset, $pager->limit]);

        return [$rows, $paginator];
    }
}

<?php

namespace App\Utils;

use Pebble\Pager;
use Pebble\Pagination\PaginationUtils;
use Pebble\Service\DBService;
use JasonGrimes\Paginator;

class AppPaginationUtils
{

    public function getPaginator(
        int $total_items,
        int $items_per_page,
        int $current_page,
        string $url,
        array $default_order = [],
        int $max_pages = 10,
        string $session_key = null,
    ) {

        $pagination_utils = new PaginationUtils($default_order, $session_key);
        $url_pattern = $pagination_utils->getPaginationURLPattern($url);

        $paginator = new Paginator($total_items, $items_per_page, $current_page, $url_pattern);
        $paginator->setMaxPagesToShow($max_pages);

        return $paginator;
    }

    public static function getRowsAndPaginator(
        string $table_name,
        string $primary_key,
        string $url_pattern,
        array $column_order = [],
        int $per_page = 10,
        int $max_pages = 10
    ) {

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

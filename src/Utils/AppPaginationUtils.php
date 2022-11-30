<?php

declare(strict_types=1);

namespace App\Utils;

use Pebble\Pagination\PaginationUtils;
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
}

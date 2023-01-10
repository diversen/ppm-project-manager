<?php

declare(strict_types=1);

namespace App\Utils;

use Diversen\Lang;

/**
 * Some helper functions for the Template
 */

class HTMLUtils
{

    public static function getMenuSeparator() {
        return ' :: ';
    }


    public static function getTaskPriorityClass($task)
    {
        $priorities = [
            'low',
            'minor',
            'normal',
            'high',
            'urgent',
        ];

        if ($task['status'] == '0') {
            return 'priority-done';
        }

        return 'priority-' . $priorities[$task['priority']];
    }

    /**
     * Get is today as boolean from a unix timestamp
     */
    public static function isToday($ts)
    {
        $today_ts = strtotime('today');
        $is_today = false;
        if ($today_ts == $ts) {
            $is_today = true;
        }
        return $is_today;
    }

    public static function getIcon(string $icon)
    {
        if ($icon === 'edit') {
            return '<i class="fa-solid fa-edit"></i>';
        }
        if ($icon === 'add') {
            return '<i class="fa-solid fa-plus"></i>';
        }
        if ($icon === 'clock') {
            return '<i class="fa-solid fa-clock"></i>';
        }
        if ($icon === 'today') {
            return Lang::translate('Today');
        }
        if ($icon === 'delete') {
            return Lang::translate('Delete');
        }
    }
}

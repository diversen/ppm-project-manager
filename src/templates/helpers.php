<?php

use Diversen\Lang;

/**
 * Get a priority css class name from task
 */
function get_task_priority_class($task)
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
function is_today($ts)
{
    $today_ts = strtotime('today');
    $is_today = false;
    if ($today_ts == $ts) {
        $is_today = true;
    }
    return $is_today;
}

function get_sub_menu(array $parts)
{
    $str = implode(' :: ', $parts);
    return "<h4>$str</h4>";
}

function get_icon(string $icon) {
    if ($icon === 'edit') {
        return '<i class="fa-solid fa-edit"></i>';
    }
    if ($icon === 'add') {
        return '<i class="fa-solid fa-plus"></i>';
    }
    if($icon === 'clock') {
        return '<i class="fa-solid fa-clock"></i>';
    }
    if($icon === 'today') {
        return Lang::translate('Today');
    }
    if($icon === 'delete') {
        return Lang::translate('Delete');
    }

    // if ($icon === 'edit') {
    //     return Lang::translate('Edit');
    // }
    // if ($icon === 'add') {
    //     return Lang::translate('New');
    // }
    // if($icon === 'clock') {
    //     return Lang::translate('Time');
    // }
    // if($icon === 'today') {
    //     return Lang::translate('Today');
    // }
    // if($icon === 'delete') {
    //     return Lang::translate('Delete');
    // }

}

define('SUB_MENU_SEP', ' :: ');

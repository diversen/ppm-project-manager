<?php

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

define('SUB_MENU_SEP', ' :: ');

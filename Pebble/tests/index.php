<?php

require_once "autoload.php";


function display_error(string $message) {

    echo "<div style='color: red'>FAILED: $message</div>";
}

function display_passed(string $message) {
    echo "<div style='color: green'>PASSED $message</div>";
}


class Test {

    static function equal_type(string $test_message, $cond_1, $cond_2) {

        try {
            if ($cond_1 === $cond_2) {
                display_passed($test_message);
            } else {
                display_error($test_message);
            }
        } catch (Throwable $e) {
            display_error($e->getMessage());
            display_error($test_message);
        }
    }

    static function not_equal_type(string $test_message, $cond_1, $cond_2) {

        try {
            if ($cond_1 !== $cond_2) {
                display_passed($test_message);
            } else {
                display_error($test_message);
            }
        } catch (Throwable $e) {
            display_error($e->getMessage());
            display_error($test_message);
        }
    }
}

include_once "DB.php";


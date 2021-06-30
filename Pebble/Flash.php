<?php declare(strict_types=1);

namespace Pebble;

class Flash
{

    /**
     * Set SESSION flash message
     * @param string $message
     * @param string $type Type is one of ['info', 'success', 'warning', 'error']
     * @param array  $options ['flash_remove' => true] Options. E.g. set flash_remove in order to add a css class used to remove messages using js.
     */
    public static function setMessage(string $message, string $type, array $options = [])
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = ['message' => $message, 'type' => $type, 'options' => $options];
    }

    /**
     * Get all flash messages as an array
     * @return array $messages 
     */
    public static function getMessages() : array
    {
        $messages = [];

        if (isset($_SESSION['flash'])) {
            foreach ($_SESSION['flash'] as $key => $message) {
                $messages[] = $message;
            }
        }

        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }

        return $messages;
    }
}


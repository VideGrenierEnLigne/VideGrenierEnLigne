<?php 
namespace App\Utility;

class Flash
{
    public static function getMessages(): array
    {
        $messages = [];

        if (isset($_COOKIE['flash_success'])) {
            $messages['success'] = htmlspecialchars($_COOKIE['flash_success']);
            setcookie('flash_success', '', time() - 3600, '/');
        }

        if (isset($_COOKIE['flash_error'])) {
            $messages['error'] = htmlspecialchars($_COOKIE['flash_error']);
            setcookie('flash_error', '', time() - 3600, '/');
        }

        return $messages;
    }
}

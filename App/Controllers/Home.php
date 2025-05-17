<?php

namespace App\Controllers;

use App\Models\Articles;
use \Core\View;
use Exception;
use App\Utility\Flash;

/**
 * Home controller
 */
class Home extends \Core\Controller
{

    /**
     * Affiche la page d'accueil
     *
     * @return void
     * @throws \Exception
     */
    public function indexAction()
    {

    View::renderTemplate('Home/index.html', [
    'flash' => Flash::getMessages()
]);
    }
}

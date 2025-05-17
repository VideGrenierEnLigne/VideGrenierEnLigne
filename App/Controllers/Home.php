<?php

namespace App\Controllers;

use OpenApi\Attributes as OA;
use App\Models\Articles;
use App\Utility\Flash;
use \Core\View;
use Exception;

#[OA\Tag(
    name: "Pages",
    description: "Pages HTML classiques"
)]
class Home extends \Core\Controller
{
    #[OA\Get(
        path: "/",
        tags: ["Pages"],
        summary: "Page d'accueil",
        description: "Affiche la page d'accueil (HTML), utile pour tests manuels ou comme point d'entrée visuel",
        responses: [
            new OA\Response(
                response: 200,
                description: "Page HTML de l'accueil"
            )
        ]
    )]
    public function indexAction()
    {
        View::renderTemplate('Home/index.html', [
            'flash' => Flash::getMessages()
        ]);
    }
}

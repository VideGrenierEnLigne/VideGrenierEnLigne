<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Models\Cities;
use \Core\View;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Produits et Villes",
    version: "1.0.0"
)]
#[OA\Tag(
    name: "Produits",
    description: "Opérations liées aux articles / produits"
)]
#[OA\Tag(
    name: "Villes",
    description: "Recherche dans la base des villes"
)]
class Api extends \Core\Controller
{
    #[OA\Get(
        path: "/api/products",
        summary: "Liste des produits triés",
        description: "Récupère tous les produits avec un tri optionnel",
        tags: ["Produits"],
        parameters: [
            new OA\Parameter(
                name: "sort",
                description: "Critère de tri (ex: price, name...)",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des produits",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(type: "object")
                )
            )
        ]
    )]
    public function ProductsAction()
    {
        $query = $_GET['sort'] ?? null;

        $articles = Articles::getAll($query);

        header('Content-Type: application/json');
        echo json_encode($articles);
    }

    #[OA\Get(
        path: "/api/cities",
        summary: "Recherche de villes",
        description: "Recherche une ville par nom ou mot-clé",
        tags: ["Villes"],
        parameters: [
            new OA\Parameter(
                name: "query",
                description: "Terme de recherche pour les villes",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Résultats de la recherche",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(type: "object")
                )
            )
        ]
    )]
    public function CitiesAction()
    {
        $cities = Cities::search($_GET['query'] ?? '');

        header('Content-Type: application/json');
        echo json_encode($cities);
    }
}

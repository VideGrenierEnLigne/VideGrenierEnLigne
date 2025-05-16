<?php

namespace App\Models;

use Core\Model;
use OpenApi\Attributes as OA;

/**
 * Modèle City
 */
#[OA\Schema(
    schema: "City",
    type: "object",
    title: "City",
    required: ["ville_id"],
    properties: [
        new OA\Property(property: "ville_id", type: "integer", description: "Identifiant de la ville")
    ]
)]
class Cities extends Model
{
    /**
     * Recherche de villes par nom (préfixe)
     *
     * @param string $str Chaîne de recherche
     * @return int[] Liste des IDs des villes correspondantes
     * @throws Exception
     */
    #[OA\Get(
        path: "/cities/search",
        summary: "Recherche de villes par nom",
        description: "Retourne une liste d'identifiants des villes dont le nom commence par la chaîne donnée",
        tags: ["Cities"],
        parameters: [
            new OA\Parameter(
                name: "query",
                in: "query",
                required: true,
                description: "Début du nom de la ville recherché",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des IDs des villes",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(type: "integer"),
                )
            ),
            new OA\Response(
                response: 400,
                description: "Paramètre manquant ou invalide",
                content: new OA\JsonContent(ref: "#/components/schemas/Error")
            )
        ]
    )]
    public static function search(string $str): array
    {
        $db = static::getDB();

        $stmt = $db->prepare('SELECT ville_id FROM villes_france WHERE ville_nom_reel LIKE :query');

        $query = $str . '%';

        $stmt->bindParam(':query', $query);

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
    }
}

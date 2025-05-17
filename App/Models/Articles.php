<?php

namespace App\Models;

use Core\Model;
use DateTime;
use Exception;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Article",
    type: "object",
    title: "Article",
    required: ["id", "name", "description", "user_id", "published_date", "views"],
    properties: [
        new OA\Property(property: "id", type: "integer", description: "ID de l'article"),
        new OA\Property(property: "name", type: "string", description: "Titre de l'article"),
        new OA\Property(property: "description", type: "string", description: "Description de l'article"),
        new OA\Property(property: "user_id", type: "integer", description: "ID de l'utilisateur auteur"),
        new OA\Property(property: "published_date", type: "string", format: "date", description: "Date de publication"),
        new OA\Property(property: "views", type: "integer", description: "Nombre de vues"),
        new OA\Property(property: "picture", type: "string", nullable: true, description: "Nom du fichier image associé")
    ]
)]
class Articles extends Model
{
    #[OA\Get(
        path: "/articles",
        summary: "Lister les articles",
        description: "Retourne une liste d'articles avec un filtre optionnel",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(
                name: "filter",
                in: "query",
                required: false,
                description: "Filtre de tri (views, data)",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des articles",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Article")
                )
            )
        ]
    )]
    public static function getAll(string $filter): array
    {
        $db = static::getDB();

        $query = 'SELECT * FROM articles ';

        switch ($filter) {
            case 'views':
                $query .= ' ORDER BY articles.views DESC';
                break;
            case 'data':
                $query .= ' ORDER BY articles.published_date DESC';
                break;
            case '':
                break;
        }

        $stmt = $db->query($query);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    #[OA\Get(
        path: "/articles/{id}",
        summary: "Récupérer un article",
        description: "Retourne un article par ID",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'article",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Article retourné avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/Article")
            ),
            new OA\Response(
                response: 404,
                description: "Article non trouvé"
            )
        ]
    )]
    public static function getOne(int $id): ?array
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT * FROM articles
            INNER JOIN users ON articles.user_id = users.id
            WHERE articles.id = ? 
            LIMIT 1');

        $stmt->execute([$id]);

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    #[OA\Patch(
        path: "/articles/{id}/views",
        summary: "Incrémenter les vues d'un article",
        description: "Incrémente le compteur de vues d'un article",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'article",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Vues incrémentées avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Article non trouvé"
            )
        ]
    )]
    public static function addOneView(int $id): void
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            UPDATE articles 
            SET articles.views = articles.views + 1
            WHERE articles.id = ?');

        $stmt->execute([$id]);
    }

    #[OA\Get(
        path: "/users/{id}/articles",
        summary: "Récupérer les articles d'un utilisateur",
        description: "Retourne les articles associés à un utilisateur",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'utilisateur",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Articles de l'utilisateur",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Article")
                )
            ),
            new OA\Response(
                response: 404,
                description: "Utilisateur non trouvé"
            )
        ]
    )]
    public static function getByUser(int $id): array
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT *, articles.id as id FROM articles
            LEFT JOIN users ON articles.user_id = users.id
            WHERE articles.user_id = ?');

        $stmt->execute([$id]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    #[OA\Get(
        path: "/articles/suggestions",
        summary: "Récupérer des suggestions d'articles",
        description: "Retourne les 10 derniers articles publiés",
        tags: ["Articles"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Suggestions d'articles",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Article")
                )
            )
        ]
    )]
    public static function getSuggest(): array
    {
        $db = static::getDB();

        $stmt = $db->prepare('
            SELECT *, articles.id as id FROM articles
            INNER JOIN users ON articles.user_id = users.id
            ORDER BY published_date DESC LIMIT 10');

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    #[OA\Post(
        path: "/articles",
        summary: "Créer un nouvel article",
        description: "Ajoute un nouvel article à la base de données",
        tags: ["Articles"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["name", "description", "user_id"],
                properties: [
                    new OA\Property(property: "name", type: "string", description: "Titre de l'article"),
                    new OA\Property(property: "description", type: "string", description: "Description de l'article"),
                    new OA\Property(property: "user_id", type: "integer", description: "ID de l'utilisateur auteur")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Article créé avec succès",
                content: new OA\JsonContent(ref: "#/components/schemas/Article")
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides"
            )
        ]
    )]
    public static function save(array $data): int
    {
        $db = static::getDB();

        $stmt = $db->prepare('INSERT INTO articles(name, description, user_id, published_date) VALUES (:name, :description, :user_id, :published_date)');

        $published_date = (new DateTime())->format('Y-m-d');
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':published_date', $published_date);
        $stmt->bindParam(':user_id', $data['user_id']);

        $stmt->execute();

        return $db->lastInsertId();
    }

    #[OA\Put(
        path: "/articles/{id}/picture",
        summary: "Ajouter une image à un article",
        description: "Associe une image à un article existant",
        tags: ["Articles"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID de l'article",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    type: "object",
                    required: ["picture"],
                    properties: [
                        new OA\Property(
                            property: "picture",
                            type: "string",
                            format: "binary",
                            description: "Fichier image à associer"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Image ajoutée avec succès"
            ),
            new OA\Response(
                response: 400,
                description: "Données invalides"
            ),
            new OA\Response(
                response: 404,
                description: "Article non trouvé"
            )
        ]
    )]
    public static function attachPicture(int $articleId, string $pictureName): void
    {
        $db = static::getDB();

        $stmt = $db->prepare('UPDATE articles SET picture = :picture WHERE articles.id = :articleid');

        $stmt->bindParam(':picture', $pictureName);
        $stmt->bindParam(':articleid', $articleId);

        $stmt->execute();
    }
}

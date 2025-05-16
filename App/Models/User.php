<?php

namespace App\Models;

use Core\Model;
use OpenApi\Attributes as OA;
use Exception;

/**
 * Modèle User
 */
#[OA\Schema(
    schema: "User",
    type: "object",
    required: ["id", "username", "email"],
    properties: [
        new OA\Property(property: "id", type: "integer", description: "Identifiant unique de l'utilisateur"),
        new OA\Property(property: "username", type: "string", description: "Nom d'utilisateur"),
        new OA\Property(property: "email", type: "string", format: "email", description: "Adresse email"),
        new OA\Property(property: "password", type: "string", description: "Mot de passe hashé"),
        new OA\Property(property: "salt", type: "string", description: "Salt utilisé pour le hash du mot de passe"),
    ]
)]
class User extends Model
{
    /**
     * Crée un utilisateur
     * 
     * @param array $data Données utilisateur (username, email, password hashé, salt)
     * @return int ID du nouvel utilisateur
     * @throws Exception
     */
    #[OA\Post(
        path: "/users",
        summary: "Créer un nouvel utilisateur",
        description: "Crée un utilisateur avec username, email, password (hashé) et salt",
        tags: ["Users"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "email", "password", "salt"],
                properties: [
                    new OA\Property(property: "username", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "salt", type: "string"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Utilisateur créé avec succès", content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "id", type: "integer", description: "ID du nouvel utilisateur")
                ]
            )),
            new OA\Response(response: 400, description: "Erreur lors de la création de l'utilisateur")
        ]
    )]
    public static function createUser($data)
    {
        $db = static::getDB();
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare('INSERT INTO users(username, email, password, salt) VALUES (:username, :email, :password, :salt)');

        $stmt->bindValue(':username', $data['username']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':password', $data['password']);
        $stmt->bindValue(':salt', $data['salt']);

        try {
            $stmt->execute();
            return $db->lastInsertId();
        } catch (\PDOException $e) {
            error_log('Erreur insertion utilisateur: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la création de l\'utilisateur');
        }
    }

    /**
     * Récupère un utilisateur par son login (email)
     * 
     * @param string $login Email ou identifiant
     * @return array|null Données utilisateur ou null si non trouvé
     */
    #[OA\Get(
        path: "/users/login/{email}",
        summary: "Récupérer un utilisateur par email",
        description: "Retourne les informations utilisateur correspondant à l'email donné",
        tags: ["Users"],
        parameters: [
            new OA\Parameter(
                name: "email",
                in: "path",
                required: true,
                description: "Email de l'utilisateur",
                schema: new OA\Schema(type: "string", format: "email")
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Utilisateur trouvé", content: new OA\JsonContent(ref: "#/components/schemas/User")),
            new OA\Response(response: 404, description: "Utilisateur non trouvé")
        ]
    )]
    public static function getByLogin($login)
    {
        $db = static::getDB();

        $stmt = $db->prepare("SELECT * FROM users WHERE (users.email = :email) LIMIT 1");

        $stmt->bindValue(':email', $login);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un article par ID (pas lié à User directement, semble hors contexte)
     * 
     * @param int $id ID de l'article
     * @return array|null Données de l'article ou null si non trouvé
     */
    #[OA\Get(
        path: "/articles/{id}",
        summary: "Récupérer un article par ID",
        description: "Retourne les détails d'un article par son ID",
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
            new OA\Response(response: 200, description: "Article trouvé", content: new OA\JsonContent(type: "array", items: new OA\Items())),
            new OA\Response(response: 404, description: "Article non trouvé")
        ]
    )]
    public static function login($id)
    {
        $db = static::getDB();

        $stmt = $db->prepare('SELECT * FROM articles WHERE articles.id = ? LIMIT 1');

        $stmt->execute([$id]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

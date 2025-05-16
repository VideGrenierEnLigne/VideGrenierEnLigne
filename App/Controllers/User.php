<?php

namespace App\Controllers;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Model\UserRegister;
use App\Models\Articles;
use App\Utility\Flash;
use App\Utility\Hash;
use \Core\View;
use OpenApi\Attributes as OA;
use Exception;
use Dotenv;

#[OA\Tag(
    name: "Utilisateurs",
    description: "Authentification, inscription, compte, etc."
)]
class User extends \Core\Controller
{
    private $secretKey;

    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->secretKey = $_ENV["SECRET_KEY"] ?? "default_secret_key";
    }

    #[OA\Post(
        path: "/login",
        tags: ["Utilisateurs"],
        summary: "Connexion de l'utilisateur",
        description: "Affiche la page de connexion et traite les identifiants",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "remember", type: "string", enum: ["on", "off"])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 302, description: "Redirection vers /account si succès"),
            new OA\Response(response: 401, description: "Échec de connexion")
        ]
    )]
    public function loginAction()
    {
        if (isset($_POST['submit'])) {
            $data = $_POST;
            $this->login($data);
            header('Location: /account');
        }

        View::renderTemplate('User/login.html', [
            'flash' => Flash::getMessages()
        ]);
    }

    #[OA\Post(
        path: "/register",
        tags: ["Utilisateurs"],
        summary: "Inscription d'un nouvel utilisateur",
        description: "Affiche et traite la page d'inscription utilisateur",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["username", "email", "password", "password-check"],
                properties: [
                    new OA\Property(property: "username", type: "string"),
                    new OA\Property(property: "email", type: "string", format: "email"),
                    new OA\Property(property: "password", type: "string"),
                    new OA\Property(property: "password-check", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 302, description: "Redirection vers /account"),
            new OA\Response(response: 400, description: "Erreurs de validation")
        ]
    )]
    public function registerAction()
    {
        if (isset($_POST['submit'])) {
            $data = $_POST;

            if ($data['password'] !== $data['password-check']) {
                $error = "Les mots de passe ne correspondent pas.";
                View::renderTemplate('User/register.html', ['error' => $error, 'data' => $data,'flash' => Flash::getMessages()]);
                return;
            }

            try {
                $userId = $this->register($data);

                if ($userId) {
                    $this->login($data);
                    header('Location: /account');
                    exit;
                } else {
                    $error = "Erreur lors de la création de l'utilisateur.";
                    View::renderTemplate('User/register.html', ['error' => $error, 'data' => $data, 'flash' => Flash::getMessages()]);
                    return;
                }
            } catch (\Exception $ex) {
                $error = $ex->getMessage();
                View::renderTemplate('User/register.html', [
                    'error' => $error,
                    'data' => $data,
                    'flash' => Flash::getMessages()
                ]);
                return;
            }
        }

        View::renderTemplate('User/register.html', [
            'flash' => Flash::getMessages()
        ]);
    }

    #[OA\Get(
        path: "/account",
        tags: ["Utilisateurs"],
        summary: "Page du compte utilisateur",
        description: "Affiche la liste des articles de l'utilisateur connecté",
        responses: [
            new OA\Response(response: 200, description: "Page HTML du compte")
        ]
    )]
    public function accountAction()
    {
        $articles = Articles::getByUser($_SESSION['user']['id']);

        View::renderTemplate('User/account.html', [
            'articles' => $articles,
            'flash' => Flash::getMessages()
        ]);
    }

    #[OA\Get(
        path: "/logout",
        tags: ["Utilisateurs"],
        summary: "Déconnexion",
        description: "Supprime la session et les cookies de l'utilisateur",
        responses: [
            new OA\Response(response: 302, description: "Redirection vers la page d'accueil")
        ]
    )]
    public function logoutAction()
    {
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: /");
        return true;
    }

    private function register($data)
    {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new \Exception('Tous les champs sont obligatoires');
        }

        $salt = Hash::generateSalt(32);
        $passwordHash = Hash::generate($data['password'], $salt);

        $userID = \App\Models\User::createUser([
            "email" => $data['email'],
            "username" => $data['username'],
            "password" => $passwordHash,
            "salt" => $salt
        ]);

        if (!$userID) {
            throw new \Exception('Impossible de créer l\'utilisateur');
        }

        return $userID;
    }

    private function login($data)
    {
        try {
            if (!isset($data['email']) || !isset($data['password'])) {
                throw new Exception('Tous les champs sont obligatoires');
            }

            $user = \App\Models\User::getByLogin($data['email']);

            if (!$user || Hash::generate($data['password'], $user['salt']) !== $user['password']) {
                throw new Exception('Email ou mot de passe incorrect');
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
            ];

            if (!empty($data['remember']) && $data['remember'] === "on") {
                $userId = $user['id'];
                $expires = time() + 30 * 24 * 60 * 60;

                $dataString = $userId . '|' . $expires;
                $signature = hash_hmac('sha256', $dataString, $this->secretKey);
                $cookieValue = $dataString . '|' . $signature;

                setcookie('remember_me', $cookieValue, $expires, '/', '', false, true);
            }

            setcookie('flash_success', 'Connexion réussie', time() + 5, '/');

            return true;
        } catch (Exception $ex) {
            setcookie('flash_error', $ex->getMessage(), time() + 5, '/');
            return false;
        }
    }
}

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
use Exception;
use Dotenv;
use http\Env\Request;
use http\Exception\InvalidArgumentException;

/**
 * User controller
 */
class User extends \Core\Controller
{


    private $secretKey;

    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->secretKey = $_ENV["SECRET_KEY"] ?? "default_secret_key";
    }

    /**
     * Affiche la page de login
     */
    public function loginAction()
    {
        if (isset($_POST['submit'])) {
            $data = $_POST;

            // TODO: Validation

            $this->login($data);

            // Si login OK, redirige vers le compte
            header('Location: /account');
        }

        View::renderTemplate('User/login.html', [
            'flash' => Flash::getMessages()
        ]);
    }

    /**
     * Page de création de compte
     */
    public function registerAction()
    {
        if (isset($_POST['submit'])) {
            $data = $_POST;

            // Validation simple
            if ($data['password'] !== $data['password-check']) {
                // Exemple : stocker message d'erreur simple (à remplacer par flash ou autre)
                $error = "Les mots de passe ne correspondent pas.";
                View::renderTemplate('User/register.html', ['error' => $error, 'data' => $data,'flash' => Flash::getMessages()]);
                return;
            }


            try {
                $userId = $this->register($data);

                if ($userId) {
                    // Connecte automatiquement l’utilisateur
                    $this->login($data);

                    // Redirige vers le compte
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

        // Si pas de POST, juste afficher la page d'inscription
        View::renderTemplate('User/register.html', [
            'flash' => Flash::getMessages()
        ]);
    }
    /**
     * Affiche la page du compte
     */
    public function accountAction()
    {
        $articles = Articles::getByUser($_SESSION['user']['id']);

        View::renderTemplate('User/account.html', [
            'articles' => $articles,
            'flash' => Flash::getMessages()
        ]);
    }

    /*
     * Fonction privée pour enregister un utilisateur
     */
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

            $_SESSION['user'] = array(
                'id' => $user['id'],
                'username' => $user['username'],
            );
            if ($data['remember'] == "on") {
                $userId = $user['id'];
                $expires = time() + 30 * 24 * 60 * 60; // 30 jours

                // Format : userId|expires|signature
                $dataString = $userId . '|' . $expires;

                // Calcul de la signature
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



    /**
     * Logout: Delete cookie and session. Returns true if everything is okay,
     * otherwise turns false.
     * @access public
     * @return boolean
     * @since 1.0.2
     */
    public function logoutAction()
    {
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }
        $_SESSION = array();

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
}

<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Utility\Flash;
use App\Utility\Upload;
use \Core\View;

/**
 * Product controller
 */
class Product extends \Core\Controller
{

    /**
     * Affiche la page d'ajout
     * @return void
     */
    public function addAction()
    {
        if (isset($_POST['submit'])) {
            try {
                $errors = [];

                $f = $_POST;

                // Validation simple des champs
                if (empty(trim($f['name'] ?? ''))) {
                    $errors[] = 'Le nom est obligatoire.';
                }

                if (empty(trim($f['description'] ?? ''))) {
                    $errors[] = 'La description est obligatoire.';
                }

                // Validation du fichier 
                if (!isset($_FILES['picture']) || $_FILES['picture']['error'] == UPLOAD_ERR_NO_FILE) {
                    $errors[] = 'Une image doit être téléchargée.';
                } else {
                    // vérifier le mime type
                    $allowedTypes = ['image/jpeg', 'image/png'];
                    if (!in_array($_FILES['picture']['type'], $allowedTypes)) {
                        $errors[] = 'Type de fichier non autorisé. Seuls JPEG et PNG sont acceptés.';
                    }
                }

                if (!empty($errors)) {
                    // Ici tu peux passer les erreurs à la vue ou afficher directement (var_dump par exemple)
                    foreach ($errors as $error) {
                        echo '<p style="color:red;">' . htmlspecialchars($error) . '</p>';
                    }
                }

                // Pas d'erreur, on continue
                $f['user_id'] = $_SESSION['user']['id'];
                $id = Articles::save($f);

                $pictureName = Upload::uploadFile($_FILES['picture'], $id);
                Articles::attachPicture($id, $pictureName);

                header('Location: /product/' . $id);
                exit;
            } catch (\Exception $e) {
                echo '<p style="color:red;">Erreur : ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        }

        View::renderTemplate('Product/Add.html', [
            'flash' => Flash::getMessages()
        ]);
    }

    /**
     * Affiche la page d'un produit
     * @return void
     */
    public function showAction()
    {
        $id = $this->route_params['id'];
        if (!empty($_POST['mail-message'])) {
            setcookie('flash_success', 'Votre email a bien été envoyé.', time() + 5, '/');
        }
        try {
            Articles::addOneView($id);
            $suggestions = Articles::getSuggest();
            $article = Articles::getOne($id);
        } catch (\Exception $e) {
            var_dump($e);
        }

        View::renderTemplate('Product/Show.html', [
            'article' => $article[0],
            'suggestions' => $suggestions,
            'flash' => Flash::getMessages()
        ]);
    }
}

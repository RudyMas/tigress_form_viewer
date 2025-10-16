<?php

namespace Controller\form_viewer;

use JetBrains\PhpStorm\NoReturn;
use Repository\FormsAnswersRepo;
use Repository\FormViewerFormAccessRepo;
use Service\FormViewerService;
use Tigress\Controller;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class FormViewerCrudController
 *
 * @author Rudy Mas <rudy.mas@go-next.be>
 * @copyright 2025 GO! Next (https://www.go-next.be)
 * @license Proprietary
 * @version 2025.10.16.0
 * @package Controller\form_viewer
 */
class FormViewerCrudController extends Controller
{
    /**
     * @throws LoaderError
     */
    public function __construct()
    {
        TWIG->addPath('vendor/tigress/form-viewer/src/views');
        TRANSLATIONS->load(SYSTEM_ROOT . '/vendor/tigress/form-viewer/translations/translations.json');
    }

    /**
     * Add user access to a specific form
     *
     * @return void
     */
    #[NoReturn]
    public function addFormAccess(): void
    {
        FormViewerService::checkAccess();

        if (isset($_POST['user_id'], $_POST['form_id']) && is_numeric($_POST['user_id']) && is_numeric($_POST['form_id'])) {
            $formViewerFormAccess = new FormViewerFormAccessRepo();
            $formViewerFormAccess->loadByWhere([
                'user_id' => (int)$_POST['user_id'],
                'form_id' => (int)$_POST['form_id']
            ]);

            if ($formViewerFormAccess->isEmpty()) {
                $formViewerFormAccess->new();
                $formViewerFormAccessData = $formViewerFormAccess->current();
                $formViewerFormAccessData->updateByPost($_POST);
                $formViewerFormAccess->save($formViewerFormAccessData);
                $_SESSION['success'] = __('Form access added successfully.');
            } else {
                $_SESSION['error'] = __('User already has access to this form.');
            }
        } else {
            $_SESSION['error'] = __('Invalid user ID or form ID.');
        }

        TWIG->redirect('/form-viewer/settings?tab=forms');
    }

    /**
     * Add user access to form viewer settings
     *
     * @return void
     */
    #[NoReturn]
    public function addUserAccess(): void
    {
        $formViewerSettings = FormViewerService::checkAccess();

        if (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
            $accessArray = json_decode($formViewerSettings->_get('admin_access'), true);
            if (!in_array($_POST['user_id'], $accessArray)) {
                $accessArray[] = (int)$_POST['user_id'];
                $formViewerSettings->_set('admin_access', json_encode($accessArray));
                $_SESSION['success'] = __('User access added successfully.');
            } else {
                $_SESSION['error'] = __('User already has access.');
            }
        } else {
            $_SESSION['error'] = __('Invalid user ID.');
        }

        TWIG->redirect('/form-viewer/settings');
    }

    /**
     * Delete answers by uniq_code
     *
     * @return void
     */
    #[NoReturn]
    public function deleteAnswers(): void
    {
        $formsAnswers = new FormsAnswersRepo();
        $formsAnswers->deleteByField('uniq_code', $_POST['id']);
        TWIG->redirect("/form-viewer/forms/{$_POST['form_id']}");
    }

    /**
     * Delete answers by database ID
     *
     * @return void
     */
    #[NoReturn]
    public function deleteAnswersDatabase(): void
    {
        $repositoryClass = 'Repository\\' . $this->tableNameToClass($_POST['db_table']);
        if (class_exists($repositoryClass)) {
            $formsAnswers = new $repositoryClass();
            $formsAnswers->deleteById((int)$_POST['id']);
        }
        TWIG->redirect("/form-viewer/forms/{$_POST['form_id']}");
    }

    /**
     * Get form access list for DataTables
     *
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getFormAccess(): void
    {
        $formViewerFormAccess = new FormViewerFormAccessRepo();
        $data = $formViewerFormAccess->loadAllData();

        TWIG->render(null, $data, 'DT');
    }

    /**
     * Remove form access to a user
     *
     * @return void
     */
    #[NoReturn]
    public function removeFormAccess(): void
    {
        if (isset($_POST['RemoveFormAccess']) && is_numeric($_POST['RemoveFormAccess'])) {
            $formViewerFormAccess = new FormViewerFormAccessRepo();
            $formViewerFormAccess->deleteById($_POST['RemoveFormAccess']);
            $_SESSION['success'] = __('Form access removed successfully.');
        } else {
            $_SESSION['error'] = __('Invalid ID.');
        }

        TWIG->redirect('/form-viewer/settings?tab=forms');
    }

    /**
     * Remove user access from form viewer settings
     *
     * @return void
     */
    #[NoReturn]
    public function removeUserAccess(): void
    {
        $formViewerSettings = FormViewerService::checkAccess();

        if (isset($_POST['RemoveUser']) && is_numeric($_POST['RemoveUser'])) {
            $accessArray = json_decode($formViewerSettings->_get('admin_access'), true);
            if (in_array($_POST['RemoveUser'], $accessArray)) {
                $accessArray = array_filter($accessArray, function ($value) {
                    return $value !== (int)$_POST['RemoveUser'];
                });
                $formViewerSettings->_set('admin_access', json_encode(array_values($accessArray)));
                $_SESSION['success'] = __('User access removed successfully.');
            } else {
                $_SESSION['error'] = __('User does not have access.');
            }
        } else {
            $_SESSION['error'] = __('Invalid user ID.');
        }

        TWIG->redirect('/form-viewer/settings');
    }
}
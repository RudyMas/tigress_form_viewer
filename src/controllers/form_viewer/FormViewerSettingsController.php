<?php

namespace Controller\form_viewer;

use JetBrains\PhpStorm\NoReturn;
use Repository\FormsRepo;
use Repository\UsersRepo;
use Service\FormViewerService;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class FormViewerController
 *
 * @author Rudy Mas <rudy.mas@go-next.be>
 * @copyright 2025 GO! Next (https://www.go-next.be)
 * @license Proprietary
 * @version 2025.09.19.1
 * @package Controller\form_viewer
 */
class FormViewerSettingsController
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

    /**
     * Render the settings view
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function settings(): void
    {
        $formViewerSettings = FormViewerService::checkAccess();

        $accessArray = json_decode($formViewerSettings->_get('admin_access'), true);

        $users = new UsersRepo();
        $users->loadAllActive('first_name, last_name');
        $usersAccess = [];
        foreach ($accessArray as $value) {
            $usersAccess[] = $users->find(['id' => $value])[0];
        }

        $forms = new FormsRepo();
        $forms->loadAllActive('name');

        TWIG->render('form_viewer/settings.twig', [
            'accessArray' => $accessArray ?: [],
            'adminAccess' => $formViewerSettings->_hasAccess('admin_access', $_SESSION['user']['id']),
            'allForms' => $forms,
            "allUsers" => $users,
            'usersAccess' => $usersAccess,
        ]);
    }
}
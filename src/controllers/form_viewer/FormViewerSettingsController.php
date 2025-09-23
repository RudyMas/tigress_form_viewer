<?php

namespace Controller\form_viewer;

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
 * @version 2025.09.23.0
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
     * Render the settings view
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function settings(): void
    {
        $formViewerSettings = FormViewerService::checkAdminAccess();

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
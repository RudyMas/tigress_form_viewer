<?php

namespace Service;

use Repository\FormViewerSettingsRepo;

class FormViewerService
{
    public static function checkAccess(): FormViewerSettingsRepo
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = __('You do not have the necessary rights to view this page.');
            TWIG->redirect('/login');
        }

        $formViewerSettings = new FormViewerSettingsRepo();
        $formViewerSettings->_loadAll();

        if (!$formViewerSettings->_hasAccess('admin_access', $_SESSION['user']['id'])) {
            $_SESSION['error'] = __('You do not have the necessary rights to view this page.');
            TWIG->redirect('/login');
        };
        return $formViewerSettings;
    }
}
<?php

namespace Service;

use Repository\FormViewerSettingsRepo;

/**
 * Service class for Form Viewer module.
 */
class FormViewerService
{
    /**
     * Check if the user has access to the form viewer module.
     *
     * @return FormViewerSettingsRepo
     */
    public static function checkAccess(): FormViewerSettingsRepo
    {
        if (RIGHTS->checkRights() === false) {
            $_SESSION['error'] = __('You do not have the necessary rights to view this page.');
            TWIG->redirect('/login');
        }

        $formViewerSettings = new FormViewerSettingsRepo();
        $formViewerSettings->_loadAll();
        return $formViewerSettings;
    }

    /**
     * Check if the user has admin access to the form viewer module & admin rights.
     * @return FormViewerSettingsRepo
     */
    public static function checkAdminAccess(): FormViewerSettingsRepo
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
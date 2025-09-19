<?php

namespace Controller\form_viewer;

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
 * @version 2025.09.19.0
 * @package Controller\form_viewer
 */
class FormViewerController
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
     * Render the menu view
     *
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function menu(): void
    {
        $formViewerSettings = FormViewerService::checkAccess();

        TWIG->render('form_viewer/menu.twig', [
            'adminAccess' => $formViewerSettings->_hasAccess('admin_access', $_SESSION['user']['id']),
        ]);
    }
}
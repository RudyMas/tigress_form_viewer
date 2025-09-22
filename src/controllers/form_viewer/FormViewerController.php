<?php

namespace Controller\form_viewer;

use Controller\TilesOnly;
use Repository\FormsRepo;
use Repository\FormViewerFormAccessRepo;
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

        $formViewerFromAccess = new FormViewerFormAccessRepo();
        $formViewerFromAccess->loadByWhere(['user_id' => $_SESSION['user']['id']]);
        $formIds = array_map(fn($item) => $item['form_id'], $formViewerFromAccess->toArray());

        $forms = new FormsRepo();
        $forms->loadByWhereQuery('id IN (' . implode(',', $formIds) . ')');

        $menu['tiles'] = [];
        foreach ($forms as $form) {
            $temp = [];
            $temp[$form->name]['url'] = '/form-viewer/form/' . $form->id;
            $temp[$form->name]['target'] = '_self';
            $temp[$form->name]['button'] = 'btn-big';
            $temp[$form->name]['buttonColorClass'] = 'light-purple-1';
            $temp[$form->name]['icon'] = 'fa-solid fa-file-lines';
            $temp[$form->name]['iconColor'] = '#A885CC';
            $menu['tiles'] = array_merge($menu['tiles'], $temp);
        }

        $tiles = new TilesOnly();

        TWIG->render('form_viewer/menu.twig', [
            'adminAccess' => $formViewerSettings->_hasAccess('admin_access', $_SESSION['user']['id']),
            'content' => $tiles->createTiles(json_encode($menu)),
        ]);
    }
}
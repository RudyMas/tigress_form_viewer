<?php

namespace Controller\form_viewer;

use Controller\TilesOnly;
use Repository\FormsAnswersRepo;
use Repository\FormsQuestionsRepo;
use Repository\FormsRepo;
use Repository\FormsSectionsRepo;
use Repository\FormViewerFormAccessRepo;
use Service\FormViewerService;
use stdClass;
use Tigress\Controller;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class FormViewerController
 *
 * @author Rudy Mas <rudy.mas@go-next.be>
 * @copyright 2025 GO! Next (https://www.go-next.be)
 * @license Proprietary
 * @version 2025.09.29.0
 * @package Controller\form_viewer
 */
class FormViewerController extends Controller
{
    /**
     * @throws LoaderError
     */
    public function __construct()
    {
        TWIG->addPath('vendor/tigress/form-viewer/src/views');
        TWIG->addPath('vendor/tigress/form-builder/src/views');
        TRANSLATIONS->load(SYSTEM_ROOT . '/vendor/tigress/form-viewer/translations/translations.json');
    }

    public function index(array $args): void
    {
        $formViewerSettings = FormViewerService::checkAccess();
        $adminAccess = $formViewerSettings->_hasAccess('admin_access', $_SESSION['user']['id']);

        $formViewerFromAccess = new FormViewerFormAccessRepo();
        $formViewerFromAccess->loadByWhere(['user_id' => $_SESSION['user']['id']]);
        $formIds = array_map(fn($item) => $item['form_id'], $formViewerFromAccess->toArray());

        if (in_array($args['form_id'], $formIds) === false && !$adminAccess) {
            $_SESSION['error'] = __('You do not have access to this form');
            TWIG->redirect('/form-viewer');
        }

        $forms = new FormsRepo();
        $forms->loadById($args['form_id']);
        $form = $forms->current();

        if (!empty($form->db_table)) {
            $repositoryClass = 'Repository\\' . $this->tableNameToClass($form->db_table);
            if (class_exists($repositoryClass)) {
                $formsAnswers = new $repositoryClass();
                $formsAnswers->loadAll();

                $fields = $formsAnswers->getFields();
                unset($fields['created_user_id']);
                unset($fields['modified']);
                unset($fields['modified_user_id']);
                unset($fields['deleted']);
                unset($fields['deleted_user_id']);
                unset($fields['active']);

                TWIG->render('form_viewer/answers_index_database.twig', [
                    'form' => $form,
                    'fields' => $fields,
                    'answers' => $formsAnswers,
                ]);
            } else {
                $_SESSION['error'] = __('The answers for this form are not available.');
                TWIG->redirect('/forms');
            }
        } else {
            TWIG->render('form_viewer/answers_index.twig', [
                'form' => $form,
            ]);
        }
    }

    /**
     * Create a PDF of the answers
     *
     * @param array $args
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createPDF(array $args): void
    {
        $data = $this->getFormAnswers($args);

        TWIG->render('form_viewer/createPDF.tpdf', $data, 'PDF', 200, [
            'attachment' => SYSTEM->debug,
            'filename' => $data['form']->name . '_' . $args['uniq_code'] . '.pdf',
        ]);
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
        $adminAccess = $formViewerSettings->_hasAccess('admin_access', $_SESSION['user']['id']);

        if ($adminAccess) {
            $forms = new FormsRepo();
            $forms->loadAllActive();
        } else {
            $formViewerFromAccess = new FormViewerFormAccessRepo();
            $formViewerFromAccess->loadByWhere(['user_id' => $_SESSION['user']['id']]);
            $formIds = array_map(fn($item) => $item['form_id'], $formViewerFromAccess->toArray());

            if (empty($formIds)) {
                $_SESSION['warning'] = __('No forms available');
                TWIG->redirect('/home');
            }

            $forms = new FormsRepo();
            $forms->loadByWhereQuery('id IN (' . implode(',', $formIds) . ')');
        }

        $menu['tiles'] = [];
        foreach ($forms as $form) {
            $temp = [];
            $temp[$form->name]['url'] = '/form-viewer/forms/' . $form->id;
            $temp[$form->name]['target'] = '_self';
            $temp[$form->name]['button'] = 'btn-big';
            $temp[$form->name]['buttonColorClass'] = 'light-purple-1';
            $temp[$form->name]['icon'] = 'fa-solid fa-file-lines';
            $temp[$form->name]['iconColor'] = '#A885CC';
            $menu['tiles'] = array_merge($menu['tiles'], $temp);
        }

        $tiles = new TilesOnly();

        TWIG->render('form_viewer/menu.twig', [
            'adminAccess' => $adminAccess,
            'content' => $tiles->createTiles(json_encode($menu)),
        ]);
    }

    /**
     * Render the answer view
     *
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function viewAnswer(array $args): void
    {
        $data = $this->getFormAnswers($args);

        TWIG->render('form_viewer/answers_show.twig', [
            'form' => $data['form'],
            'formsAnswers' => $data['formsAnswers'],
            'formsSections' => $data['formsSections'],
            'uniq_code' => $args['uniq_code'],
        ]);
    }

    /**
     * Get the form answers
     *
     * @param array $args
     * @return array
     */
    public function getFormAnswers(array $args): array
    {
        $formViewerSettings = FormViewerService::checkAccess();
        $adminAccess = $formViewerSettings->_hasAccess('admin_access', $_SESSION['user']['id']);

        $formViewerFromAccess = new FormViewerFormAccessRepo();
        $formViewerFromAccess->loadByWhere(['user_id' => $_SESSION['user']['id']]);
        $formIds = array_map(fn($item) => $item['form_id'], $formViewerFromAccess->toArray());

        if (in_array($args['form_id'], $formIds) === false && !$adminAccess) {
            $_SESSION['error'] = __('You do not have access to this form');
            TWIG->redirect('/form-viewer');
        }

        $forms = new FormsRepo();
        $forms->loadById($args['form_id']);
        $form = $forms->current();

        $formsSections = new FormsSectionsRepo();
        $formsSections->loadByWhere([
            'form_id' => $form->id,
            'active' => 1,
        ], 'sort');

        if (!empty($form->db_table)) {
            $repositoryClass = 'Repository\\' . $this->tableNameToClass($form->db_table);
            $formsAnswers = new $repositoryClass();
            $formsAnswers->loadById($args['uniq_code']);
            $formsAnswer = $formsAnswers->current();

            $allAnswers = [];
            if (class_exists($repositoryClass)) {
                foreach ($formsSections as $formsSection) {
                    $formsQuestions = new FormsQuestionsRepo();
                    $formsQuestions->loadByWhere([
                        'forms_section_id' => $formsSection->id,
                        'active' => 1,
                    ], 'sort');

                    foreach ($formsQuestions as $formsQuestion) {
                        $answer = new stdClass();
                        $answer->answer = $formsAnswer->{$formsQuestion->db_field} ?? '';
                        $answer->question__question = $formsQuestion->question;
                        $answer->question__field_type_id = $formsQuestion->field_type_id;
                        $answer->section__id = $formsSection->id;

                        $allAnswers[] = $answer;
                    }
                }
            } else {
                $_SESSION['error'] = __('The answers for this form are not available.');
                TWIG->redirect('/form-viewer/' . $form->id . '/answers/');
            }
        } else {
            $formsAnswers = new FormsAnswersRepo();
            $allAnswers = $formsAnswers->getAnswersByUniqCode($args['uniq_code']);
        }

        return [
            'form' => $form,
            'formsAnswers' => $allAnswers,
            'formsSections' => $formsSections,
        ];
    }
}
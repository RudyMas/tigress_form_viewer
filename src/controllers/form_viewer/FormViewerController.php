<?php

namespace Controller\form_viewer;

/**
 * Class FormViewerController
 *
 * @author Rudy Mas <rudy.mas@go-next.be>
 * @copyright 2025 GO! Next (https://www.go-next.be)
 * @license Proprietary
 * @version 2025.07.01.0
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
     * Overview of the forms based on the provided form reference.
     *
     * @param array $args
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function index(array $args): void
    {
        $forms = new FormsRepo();
        $forms->loadByWhere([
            'form_reference' => $args['form_reference'],
            'active' => 1,
        ]);

        if ($forms->isEmpty()) {
            \Controller\forms\TWIG->redirect('/form/closed');
        }

        $form = $forms->current();

        $formsSections = new FormsSectionsRepo();
        $formsSections->loadByWhere([
            'form_id' => $form->id,
            'active' => 1,
        ], 'sort');

        $formsQuestions = new FormsQuestionsRepo();
        $formsQuestions->loadByWhereQuery("forms_section_id IN (SELECT id FROM forms_sections WHERE form_id = :form_id AND active = 1) AND active = 1", [
            'form_id' => $form->id,
        ], 'sort');

        $formBuilderFieldTypes = new FormBuilderFieldTypesRepo();
        $formBuilderFieldTypes->load();

        \Controller\forms\TWIG->render('forms/form_index.twig', [
            'numberOfSections' => $formsSections->count(),
            'form' => $form,
            'formsSections' => $formsSections->toArray(),
            'formsQuestions' => $formsQuestions->toArray(),
            'formBuilderFieldTypes' => $formBuilderFieldTypes,
            'loadMenu' => 'forms/form_menu.twig',
        ]);
    }

    /**
     * Render the closed page for forms.
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function closed(): void
    {
        $message = \Controller\forms\__('The form is already closed or not recognized.<br>Please contact the form administrator.');

        \Controller\forms\TWIG->render('forms/message.twig', [
            'message' => $message,
            'loadMenu' => 'forms/form_menu.twig',
        ]);
    }

    /**
     * Renders the success page after saving the form.
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function success(): void
    {
        $message = \Controller\forms\__('The form has been successfully saved.<br>You do not need to do anything further – you may now close your browser.');

        \Controller\forms\TWIG->render('forms/message.twig', [
            'message' => $message,
            'loadMenu' => 'forms/form_menu.twig',
        ]);
    }
}
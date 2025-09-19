document.addEventListener('DOMContentLoaded', function () {
    window.tigress = window.tigress || {};

    window.tigress.loadTranslations(language.translations)
        .then(function () {

            let url = '/form-viewer/get/form-access/active';
            if (variables.show === 'archive') {
                url = '/form-viewer/get/form-access/inactive';
            }

            const tableFormAccess = new DataTable('#dataTableFormAccess', {
                processing: true,
                ajax: {
                    url: url,
                    dataType: 'json'
                },
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'Alle']
                ],
                responsive: true,
                columns: [
                    {
                        title: __('ID'),
                        data: 'id',
                        className: 'text-middle'
                    },
                    {
                        title: __('Name'),
                        data: null,
                        className: 'text-nowrap text-middle',
                        render: function (data, type, row) {
                            return row.first_name + ' ' + row.last_name;
                        }
                    },
                    {
                        title: __('Form name'),
                        data: 'form_name',
                        className: 'text-middle',
                    },
                    {
                        title: __('Actions'),
                        data: null,
                        className: 'text-nowrap text-center text-middle',
                        render: function (data, type, row) {
                            let output = '';
                            output += `<button type="button" class="btn btn-sm btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#ModalRemoveFormAccess" data-id="{{ row.id }}" data-toggle="tooltip" title="{{ __('Deleting') }}"><i class="fa fa-fw fa-trash" aria-hidden="true"></i></button>`
                            return output;
                        }
                    }
                ],
                stateSave: true,
                order: [[0, 'desc']],
                language: tigress.languageOption,
                drawCallback: function () {
                    initTooltips();
                }
            });

            const modalDelete = document.getElementById('ModalFormsDelete');
            modalDelete.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                modalDelete.querySelector('#DeleteForm').value = button.getAttribute('data-id');
            });

            const modalRestore = document.getElementById('ModalFormsRestore');
            modalRestore.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                modalRestore.querySelector('#RestoreForm').value = button.getAttribute('data-id');
            });
        })
});

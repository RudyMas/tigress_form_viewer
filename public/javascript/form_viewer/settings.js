document.addEventListener('DOMContentLoaded', function () {
    window.tigress = window.tigress || {};

    window.tigress.loadTranslations(language.translations)
        .then(function () {

            let url = '/form-viewer/get/form-access';

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
                            return row.user_first_name + ' ' + row.user_last_name;
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
                            output += `<button type="button" class="btn btn-sm btn-danger open-modal" data-bs-toggle="modal" data-bs-target="#ModalRemoveFormAccess" data-id="${ row.id }" data-toggle="tooltip" title="${ __('Deleting') }"><i class="fa fa-fw fa-trash" aria-hidden="true"></i></button>`
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

            const modalAddFormAccess = document.getElementById('ModalAddFormAccess');
            modalAddFormAccess.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                modalAddFormAccess.querySelector('#NewFormAccess').value = button.getAttribute('data-id');
            });

            const modalRemoveFormAccess = document.getElementById('ModalRemoveFormAccess');
            modalRemoveFormAccess.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                modalRemoveFormAccess.querySelector('#RemoveFormAccess').value = button.getAttribute('data-id');
            });
        })
});

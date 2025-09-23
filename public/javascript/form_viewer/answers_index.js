document.addEventListener('DOMContentLoaded', function () {
    window.tigress = window.tigress || {};

    window.tigress.loadTranslations(language.translations).then(function () {

        const tableAnswers = new DataTable('#dataTableAnswers', {
            processing: true,
            ajax: {
                url: `/forms/${variables.formId}/answers/get`,
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
                    data: 'uniq_code',
                    width: '99%',
                },
                {
                    title: __('Date'),
                    data: 'created',
                    className: 'text-nowrap text-middle',
                },
                {
                    title: __('Submitted by'),
                    data: 'last_name',
                    className: 'text-center text-middle text-nowrap',
                    render: function (data, type, row) {
                        if (row.created_user_id > 0) {
                            return `${data} ${row.first_name}`;
                        } else {
                            return `<span class="text-muted"><i class="fa fa-question"></i></span>`;
                        }
                    }
                },
                {
                    title: __('Actions'),
                    data: null,
                    className: 'text-nowrap text-center text-middle',
                    render: function (data, type, row) {
                        let output = '';
                        if (variables.read) {
                            output += ` <a data-bs-toggle="tooltip" title="${__('View')}" href="/form-viewer/forms/${variables.formId}/answers/${row.uniq_code}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>`;
                        }
                        return output;
                    }
                }
            ],
            columnDefs: [
                {
                    targets: [1],
                    type: 'datetime-moment',
                    render: function (data, type) {
                        if (!data) {
                            return '';
                        }
                        return type === 'display'
                            ? moment(data, 'YYYY-MM-DD HH:mm:ss').format('DD-MM-YYYY, HH:mm')
                            : moment(data, 'YYYY-MM-DD HH:mm:ss').valueOf();
                    }
                }
            ],
            stateSave: true,
            order: [[1, 'desc']],
            language: tigress.languageOption,
            drawCallback: function () {
                initTooltips();
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    window.tigress = window.tigress || {};

    const tableAnswers = new DataTable('#dataTableAnswersDatabase', {
        processing: true,
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'Alle']
        ],
        responsive: true,
        scrollX: true,
        stateSave: true,
        order: [[1, 'desc']],
        language: tigress.languageDatatables,
        drawCallback: function (settings) {
            initTooltips();
        }
    });

    const modalDelete = document.getElementById('modalDelete');
    if (modalDelete) {
        modalDelete.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            modalDelete.querySelector('#id').value = button.getAttribute('data-id');
        });
    }
});

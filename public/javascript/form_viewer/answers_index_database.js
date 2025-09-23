document.addEventListener('DOMContentLoaded', function () {
    window.tigress = window.tigress || {};

    const tableAnswers = new DataTable('#dataTableAnswersDatabase', {
        processing: true,
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, 'Alle']
        ],
        responsive: true,
        stateSave: true,
        order: [[1, 'desc']],
        language: tigress.languageOption,
        drawCallback: function (settings) {
            initTooltips();
        }
    });
});

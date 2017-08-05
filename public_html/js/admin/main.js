;(function ($, document, undefinedType) {

    var showFormInModal = function (modal, data) {
        modal.find('.modal-content')
            .html(data);
        prepareEditForm(modal.find('form'), modal);
    };

    var prepareEditForm = function (form, modal) {
        form.on('submit', function (event) {
            var submitPath = form.attr('action');
            showModalWaiting(modal);
            $.ajax({
                url: submitPath,
                data: form.serialize(),
                method: 'POST',
                success: function (data, textStatus, request) {
                    var xLocation = request.getResponseHeader('X-Location');
                    if (xLocation) {
                        document.location = xLocation;
                    } else {
                        showFormInModal(modal, data);
                    }
                },
                error: function () {
                    modal.modal('hide');
                }
            });
            event.preventDefault();
        });
    };

    var showModalWaiting = function (modal) {
        var content = modal.find('.modal-content');
        content.html(content.data('waiting'));
    };

    $(document).ready(function() {
        var modal = $('#edit-form');

        $('[data-edit-path]').on('click', function () {
            var editPath = $(this).data('edit-path');
            $.ajax({
                url: editPath,
                method: 'GET',
                success: function (data) {
                    showFormInModal(modal, data);
                },
                error: function () {
                    modal.modal('hide');
                }
            });
        });

        modal.on('show.bs.modal', function () {
            showModalWaiting(modal);
        });
    });

})(jQuery, document, 'undefined');

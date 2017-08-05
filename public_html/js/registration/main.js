;(function ($, document, undefinedType) {

    /**
     * Manage subforms
     *
     * @param {jQuery}   form     form
     * @param {function} callback callback
     */
    var manageSubforms = function (form, callback) {
        $('[data-prototype]').each(function () {
            var colsInRow = 2,
                rowClassName = 'row',
                collection = $(this),
                itemName = collection.data('item-name'),
                maxSize = collection.data('max-size'),
                prototype = collection.data('prototype'),
                items = collection.find('.' + itemName);

            if (items.length >= maxSize) {
                return;
            }
            form.find('#add-' + itemName).on('click', function () {
                if (items.length < maxSize) {
                    var row;
                    if (items.length % colsInRow) {
                        row = items.last().closest('.' + rowClassName);
                    } else {
                        row = $('<div class="' + rowClassName + '">');
                        collection.append(row);
                    }
                    var newItem = $(prototype.replace(/__name__/g, items.length).replace(/__no__/g, items.length + 1));
                    row.append(newItem);
                    items = collection.find('.' + itemName);
                    form.trigger('enlarge');
                    callback(newItem);
                    if (items.length === maxSize) {
                        $(this).parent().hide();
                    }
                }
            }).parent().show();
        });
    };
    
    var addTools = function (range) {
        range.find('[data-toggle="tooltip"]').tooltip();
        range.find('.input-group.date').datepicker({
            endDate: '0d',
            format: 'yyyy-mm-dd',
            language: $('html').attr('lang')
        });
    };

    $(document).ready(function() {
        var form = $('.registration-form').first();
        if (form.length === 1) {
            manageSubforms(form, function (item) {
                addTools(item);
            });
            addTools(form);
        }
    });

})(jQuery, document, 'undefined');

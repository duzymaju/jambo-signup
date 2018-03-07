;(function ($, document, undefinedType) {

    /**
     * Switch districts
     *
     * @param {jQuery} mainSelect main select
     * @param {jQuery} selects    selects
     */
    var switchDistricts = function (mainSelect, selects) {
        var groups = selects.closest('.form-group');
        selects.find('[value=""]').remove();
        if (mainSelect.val() === '0') {
            selects.prop('disabled', false);
            groups.show();
        } else {
            selects.prop('disabled', true);
            groups.hide();
        }
    };

    /**
     * Manage subforms
     *
     * @param {jQuery}   form       form
     * @param {function} [callback] callback
     */
    var manageSubforms = function (form, callback) {
        $('[data-prototype]').each(function () {
            var collection = $(this);
            var maxSize = collection.data('max-size');
            var prototype = collection.data('prototype');
            var items = collection.children();
            var addedItems = collection.data('added-items') || 0;

            if (items.length + addedItems >= maxSize) {
                return;
            }
            form.find('#add-member').on('click', function () {
                if (items.length + addedItems < maxSize) {
                    var newItem = $(prototype.replace(/__name__/g, items.length)
                        .replace(/__no__/g, items.length + addedItems + 1));
                    collection.append(newItem);
                    items = collection.children();
                    form.trigger('enlarge');
                    if (typeof callback !== undefinedType) {
                        callback(newItem);
                    }
                    if (items.length + addedItems === maxSize) {
                        $(this).parent().hide();
                    }
                }
            }).parent().show();
        });
    };

    $(document).ready(function() {
        var form = $('.registration-form').first();
        if (form.length === 1) {
            var districtsSelect = form.find('#patrol_districtId');
            districtsSelect.on('change', function () {
                switchDistricts(districtsSelect, form.find('.patrol-members-districtId'));
            }).trigger('change');
            manageSubforms(form, function (item) {
                switchDistricts(districtsSelect, item.find('.patrol-members-districtId'));
            });
        }
    });

})(jQuery, document, 'undefined');

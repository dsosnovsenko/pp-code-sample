/**
 * Frontend form.
 *
 * @constructor
 */
var Frontend = function () {
    var _self = Frontend;

    _self.init();

};

/**
 * Initialization of object.
 */
Frontend.init = function () {
    $('input:checkbox').checkboxradio();
    $('.js-pass-recovery').on('click', function () {
        $('.pass-recovery-container').show();
    });
};

$().ready(function () {
    Frontend();
});
define([
    'jquery', 'core/modal_factory'
], function ($, ModalFactory) {
    return {
        init: function () {
            var trigger = $('.p_locked');
            var descriptionText = trigger.attr("data-description");

            ModalFactory
                .create({
                    title: 'test title',
                    body: descriptionText,
                    footer: 'test footer content',
                    type: ModalFactory.types.DEFAULT
                }, trigger)
                .done(function (modal) {
                    modal.getRoot().addClass('descriptionPopup');
                });
            


            function closePopup() {
                $(".modal").removeClass('show');
                $(".modal").addClass('hide');
                $(".modal-backdrop").removeClass('show');
                $(".modal-backdrop").addClass('hide');
                $("body").removeClass('modal-open');
            }
            $(document).keyup(function (e) {
                if (e.keyCode == 27) { // escape key maps to keycode `27`
                    closePopup();
                }
            });

            $(document).click(function (e) {
                let targ = e.target;
                if(targ.classList.contains("modal")){
                    closePopup();
                }
            });
        }
    };
});

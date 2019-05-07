define(['jquery', 'core/str'], function ($, str) {

    var titlerequests = [{
        key: 'activity_is_limited',
        component: 'format_picturelink'
    }];
    let titlePromise;

    str.get_strings(titlerequests).then(function (titles) {
        titlePromise = M.util.get_string('activity_is_limited', 'format_picturelink');
    });

    function handlerIn() {
        let description = $(this).attr('data-description');
        let x = $(this).position();
        let modal = $(`<div class = "tooltipmodal" style = "top: ${x.top + 40}px; left: ${x.left - 120}px"><div class = "header">${titlePromise}</div><div class = "content">${description}</div></div>`);
        $('div.picturelink').append(modal);
    }

    function handlerOut() {
        $('div.tooltipmodal').remove();
    }

    return {
        init: function () {
            $('.p_locked, .p_hide').hover(handlerIn, handlerOut);
        }
    };

});

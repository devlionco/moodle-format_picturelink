define(['core/ajax'], function (ajaxcall){
    `use strict`;

    let ajax = {
        data: {},
        method: '',
        sesskey: M.cfg.sesskey,
        courseid: document.querySelector(`.picturelink`).dataset.courseid,

        send: function () {

            this.data.courseid = this.courseid;

            ajaxcall.call([{
                methodname: 'format_picturelink_' + this.method,
                args: this.data,
                done: {},
                fail: {}
            }]);
        }
    };

    return ajax

});

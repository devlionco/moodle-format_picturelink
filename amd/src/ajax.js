define([
  'core/ajax',
  'core/notification'
], function (Ajax, Notification){
    `use strict`;

    return {
        data: {},
        method: '',
        sesskey: M.cfg.sesskey,
        courseid: document.querySelector(`.picturelink`).dataset.courseid,

        send: function () {

            this.data.courseid = this.courseid;

            Ajax.call([{
                methodname: 'format_picturelink_' + this.method,
                args: this.data,
                done: {},
                fail: Notification.exception
            }]);
        }
    };
});

define(['core/yui'], function(Y) {
`use strict`;

  let ajax = {

    url: '/filter/teamwork/ajax/ajax.php',

    data: '',

    sesskey: M.cfg.sesskey,

    send: function(){

      this.data.sesskey = this.sesskey;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'rewriteactivitiescoods',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
              },
              failure: function () {
                // popup.error();
              }
          }
      });

    }


  }

  return ajax

});

define([
  'jquery',
  'format_picturelink/drag'
], function($, dragBall) {

  const mainBlock = document.querySelector(`body`);

  return {
    init: function() {


      mainBlock.addEventListener('mousedown', function(e){
        let target = e.target;

        while (target != mainBlock) {
          if (target.classList.contains(`drag`)) {
            dragBall(e, target);
            return;
          }
          target = target.parentNode;
        }
      });

    }
  };

});

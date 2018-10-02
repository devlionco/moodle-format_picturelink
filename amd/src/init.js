define([
  'jquery',
  'format_picturelink/drag',
  'format_picturelink/ajax'

], function($, dragBall, ajax) {

  const PARENTCLASS = `picturelink`;
  const mainBlock = document.querySelector(`.${PARENTCLASS}`);
  const pinnedBlock = mainBlock.querySelector(`.picturelink_pinned`);
  let dragIsOn = 0;

  const setCoordsToItems = () => {
    const items = Array.from(mainBlock.querySelectorAll(`.picturelink_item`));
    const maxWidth = mainBlock.offsetWidth;
    const maxHeight = mainBlock.offsetHeight;

    function getRandomInt(min, max) {
      min = Math.ceil(min);
      max = Math.floor(max);
      return Math.floor(Math.random() * (max - min)) + min;
    }

    items.forEach((item)=>{
      // set random coords to new items
      // let coordx = item.dataset.coordx ? item.dataset.coordx : getRandomInt(0,maxWidth);
      // let coordy = item.dataset.coordy ? item.dataset.coordy : getRandomInt(0,maxHeight);

      // put new items on the right top angkle of the window
      let coordx = item.dataset.coordx ? item.dataset.coordx : 10;
      let coordy = item.dataset.coordy ? item.dataset.coordy : 10;
      if (!item.dataset.coordx || !item.dataset.coordy) {
        item.style.background = `green`;
        item.style.color = `green`;
      }

      item.dataset.coordx = coordx;
      item.dataset.coordy = coordy;

      item.style.left = coordx + 'px';
      item.style.top = coordy + 'px';
      // item.style.opacity = 1;
    });
    mainBlock.classList.remove(`picturelink_hide`);
  }

  const getAllVisibleItems = () => {
    const items = Array.from(document.querySelectorAll(`.picturelink_item`));
    let visible = [];
    let visibleItem = {};
    items.forEach((item)=>{
      // if (Number(item.dataset.visibility)) {
        visibleItem = [
          item.dataset.id,
          item.dataset.visibility
        ]
        // visible.push(item.dataset.id);
        visible.push(visibleItem);
      // }
    });
    visible = JSON.stringify(visible);
    return visible;
  }

  const getAllPinnedItems = () => {
    const items = Array.from(document.querySelectorAll(`.picturelink_item[data-pinned]`));
    let pinned = [];
    let pinnedItem = [];
    items.forEach((item)=>{
      // if (Number(item.dataset.pinned)) {
      pinnedItem = [
        item.dataset.id,
        item.dataset.pinned
      ]
        pinned.push(pinnedItem);
      // }
    });
    pinned = JSON.stringify(pinned);
    return pinned;
  }

  // const setCoordsPinned = (targetActivity) => {
  //   let allPinned = Array.from(mainBlock.querySelectorAll(`a[data-pinned="1"]`));
  //
  //   if (targetActivity.dataset.pinned) {
  //     let coordx = 100 + Number(allPinned.length -1) * 240;
  //     let coordy = -90;
  //     targetActivity.style.left = coordx + 'px';
  //     targetActivity.dataset.coordx = coordx;
  //     targetActivity.dataset.coordy = coordy;
  //   }else {
  //     allPinned.forEach((item)=>{
  //       item.style.left = item.style.left.replace(\/D+\, '') - 170 +'px';
  //       item.dataset.coordx = Number(item.dataset.coordx) - 170;
  //       item.dataset.coordy = -90;
  //     });
  //   }
  // }




  return {
    init: function() {

      setCoordsToItems();

      mainBlock.addEventListener('click', function(e){
        let target = e.target;
        while (!target.classList.contains(PARENTCLASS)) {

          if (target.classList.contains(`drag`)) {
            e.preventDefault();
            if (!dragIsOn) window.open(target.href,'_blank');
            return;
          }
          if (target.id === `activities` || target.id === `sections`) return

          if (target.id === `allactivities`) {
            $('#sections').slideUp();
            $('#activities').slideToggle();
            return;
          }

          if (target.id === `allsections`) {
            $('#activities').slideUp();
            $('#sections').slideToggle();
            return;
          }

          if (target.id === `visibility`) {
            if (target.classList.contains(`fa-eye`)) {
              target.classList.remove(`fa-eye`);
              target.classList.add(`fa-eye-slash`);
            } else {
              target.classList.add(`fa-eye`);
              target.classList.remove(`fa-eye-slash`);
            }

            targetid = target.parentNode.dataset.topid;
            targetActivity = mainBlock.querySelector(`[data-id="${targetid}"]`);
            targetActivity.dataset.visibility = Number(targetActivity.dataset.visibility) ? 0 : 1;

            ajax.data.visibleitems = getAllVisibleItems();
            ajax.data.method = `rewritevisibleitems`;
            ajax.send();
            return;
          }

          if (target.id === `pinned`) {
            if (target.classList.contains(`fa-lock`)) {
              target.classList.remove(`fa-lock`);
              target.classList.add(`fa-unlock`);
            } else {
              target.classList.add(`fa-lock`);
              target.classList.remove(`fa-unlock`);
            }

            targetid = target.parentNode.dataset.topid;
            targetActivity = mainBlock.querySelector(`[data-id="${targetid}"]`);
            targetActivity.dataset.pinned = Number(targetActivity.dataset.pinned) ? 0 : 1;
            if (Number(targetActivity.dataset.pinned)) {
              pinnedBlock.appendChild(targetActivity);
            }else {
              mainBlock.appendChild(targetActivity);
            }

            // setCoordsPinned(targetActivity);

            // ajax.data.coords = '';
            ajax.data.pinnedsections = getAllPinnedItems();
            ajax.data.method = `rewritepinnedsections`;
            ajax.send();
            return;
          }


          if (target.id === `picturelink_admin`) {
            target.classList.toggle('active');
            dragIsOn = dragIsOn ? 0 : 1;
            return;
          }

          target = target.parentNode;
        }
      });

      mainBlock.addEventListener('mousedown', function(e){
        // check if drag option is turned on
        if (!dragIsOn) return

        let target = e.target;
        while (target != mainBlock) {
          if (target.classList.contains(`drag`)) {
            if (Number(target.dataset.pinned) || !Number(target.dataset.visibility)) return
            dragBall(e, target)
            return;
          }
          target = target.parentNode;
        }
      });

      // by hover on admin menu light items
      mainBlock.addEventListener('mouseover', function(e){

        let target = e.target;
        while (!target.classList.contains(PARENTCLASS)) {
          if (target.classList.contains(`section-item`)) {
            targetid = target.dataset.topid;
            targetActivity = mainBlock.querySelector(`[data-id="${targetid}"]`);
            targetActivity.style.border = '2px solid red';
            target.addEventListener('mouseleave', function(e){
              targetActivity.style.border = '';
            });
            return;
          }
          target = target.parentNode;
        }
      });

    }
  };

});

define(['format_picturelink/ajax'], function (ajax) {
    `use strict`

    const getAllCoords = () => {

        const items = Array.from(document.querySelectorAll(`.picturelink_item`));
        let allCoords = [];
        let itemCoords = {};

        items.forEach((item) => {
            itemCoords = {
                id: item.dataset.id,
                coordx: item.dataset.coordx,
                coordy: item.dataset.coordy
            };
            allCoords.push(itemCoords);
        });
        allCoords = JSON.stringify(allCoords);
        return allCoords;
    }

    function getCoords(elem) {
        return {
            top: elem.offsetTop,
            left: elem.offsetLeft
        };
    }

    const dragBall = (e, ball) => {

        ball.style.transition = 0 + 's';

        const coords = getCoords(ball);
        let shiftX = e.pageX - coords.left;
        let shiftY = e.pageY - coords.top;

        const moveAt = (e) => {

            ball.style.left = (getCoords(ball).left < 0) ? 0 + 'px' : e.pageX - shiftX + 'px';
            ball.style.top = (getCoords(ball).top < 0) ? 0 + 'px' : e.pageY - shiftY + 'px';

            if (getCoords(ball).left > ball.parentNode.offsetWidth) {
                ball.style.left = ball.parentNode.offsetWidth + 'px';
            }
            if (getCoords(ball).top > ball.parentNode.offsetHeight) {
                ball.style.top = ball.parentNode.offsetHeight + 'px';
            }
        }

        ball.ondragstart = function () {
            return false;
        };

        ball.parentNode.onmousemove = function (e) {
            moveAt(e);
        };

        ball.onmouseup = function () {
            ball.parentNode.onmousemove = null;
            ball.onmouseup = null;

            // Old coords
            // ball.dataset.coordx = ball.style.left.replace(/\D+/, '');
            // convert coordinates to percents.
            let coordxByAbsolute = ball.style.left.replace(/\D+/, '');

            ball.dataset.coordx = ball.style.left.replace(/\D+/, '');
            ball.dataset.coordy = ball.style.top.replace(/\D+/, '');

            ajax.method = `rewriteactivitiescoords`;
            ajax.data.coords = getAllCoords();
            ajax.send();
        };
    }

    return dragBall;

});

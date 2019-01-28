define([ 'jquery', 'core/str', 'core/modal_factory' ], function($, str, ModalFactory) {
	let title;
	return {
		init: function() {
			let bodyText;
			$('.p_locked').mouseover(function(e) {
				var offset = $(this).offset();
				var relativeX = e.pageX /* - offset.left */;

				var relativeY = $('.p_locked').offset().top - $(window).scrollTop();

				var target = e.target;
				bodyText = target.getAttribute('data-description');
				var trigger = $('.p_locked');
				var titlerequests = [
					{
						key: 'activity_is_limited',
						component: 'format_picturelink'
					}
				];
				var titlePromise = str.get_strings(titlerequests).then(function(titles) {
					return M.util.get_string('activity_is_limited', 'format_picturelink');
				});
				ModalFactory.create(
					{
						title: titlePromise,
						body: '',
						footer: '',
						type: ModalFactory.types.DEFAULT
					},
					trigger
				).done(function(modal) {
					modal.hide();
					console.log(modal.attachToDOM($('.p_locked')));
					modal.show();
					modal.getRoot().addClass('descriptionPopup');
					modal.setBody(bodyText);
					$('.modal-dialog').css({ left: relativeX, top: relativeY });
					$(document).keyup(function(e) {
						if (e.keyCode == 27) {
							modal.hide();
							modal.destroy();
						}
					});
					$(document).mouseout(function(e) {
						let targ = e.target;
						let close = $('.close').find('span');
						if (targ.classList.contains('modal') || targ.classList.contains('close') || close) {
							setTimeout(function() {
								modal.hide();
								modal.destroy();
							}, 1000);
						}
					});
				});
			});
		}
	};
});

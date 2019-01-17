define([ 'jquery', 'core/str', 'core/modal_factory' ], function($, str, ModalFactory) {
	let title;

	return {
		init: function() {
			$('.p_locked').click(function(e) {
				var trigger = $(e.target);
				var descriptionText = e.target.getAttribute('data-description');
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
						body: descriptionText,
						footer: '',
						type: ModalFactory.types.DEFAULT
					},
					trigger
				).done(function(modal) {
					modal.getRoot().addClass('descriptionPopup');

					$(document).keyup(function(e) {
						if (e.keyCode == 27) {
							modal.hide();
							/*modal.destroy(); */
						}
					});
					$(document).click(function(e) {
						let targ = e.target;
						if (targ.classList.contains('modal') || targ.classList.contains('close')) {
							modal.hide();
							/*modal.destroy(); */
						}
					});
				});
			});
		}
	};
});

(function($) {
	'use strict';

	app.viewModels.titles.show.showTrailerModal = function() {
     	var modal  = $('#video-modal'),
            body   = modal.find('#video-container'),
            height = $(window).height() - 50;

        modal.off('hide.bs.modal').on('hide.bs.modal', function (e) {
            body.html('');

            if (document.getElementById('trailer')) {
                videojs('trailer').dispose();
            }
        });

     	if (vars.trailersPlayer == 'default') {
     		body.html('<iframe src="'+modal.data('src')+'?autoplay=1&iv_load_policy=3&modestbranding=1&rel=0" height="'+height+'px" width="100%" wmode="opaque" allowfullscreen="true"></iframe></div>');
     	} else {
             //set up either to play from youtube or mp4 file
            body.html('<video id="trailer" class="video-js vjs-default-skin vjs-big-play-centered" controls preload="auto" width="100%" height="'+height+'px"> </video>');

             if (modal.data('src').indexOf('youtube') != -1) {
             	videojs('trailer', { "techOrder": ["youtube"]}).src(modal.data('src')).play();
             }
             else {
             	videojs('trailer', { "techOrder": ["html5", "flash"]}).src(modal.data('src')).play();
             }
        }

        modal.modal('show');
    }

})(jQuery);

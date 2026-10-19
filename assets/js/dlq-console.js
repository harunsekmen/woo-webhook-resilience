/**
 * WooCommerce Webhook Dead-Letter Dispatcher Admin JS
 */

(function ($) {
  'use strict';

  if (typeof window.wwrData === 'undefined') {
    return;
  }

  var data = window.wwrData;

  $(document).ready(function () {
    $('.wwr-replay-btn').on('click', function (e) {
      e.preventDefault();

      var $btn = $(this);
      var eventId = $btn.data('event-id');

      $btn.prop('disabled', true).text(data.i18n.replaying);

      $.ajax({
        url: data.ajaxUrl,
        type: 'POST',
        data: {
          action: 'wwr_replay_webhook',
          nonce: data.nonce,
          event_id: eventId
        },
        dataType: 'json',
        success: function (res) {
          $btn.prop('disabled', false).text('Replay');
          if (res.success) {
            alert(res.data.message || data.i18n.success);
            window.location.reload();
          } else {
            alert(res.data.message || 'Replay error');
          }
        },
        error: function () {
          $btn.prop('disabled', false).text('Replay');
          alert('Server communication error.');
        }
      });
    });
  });
})(jQuery);


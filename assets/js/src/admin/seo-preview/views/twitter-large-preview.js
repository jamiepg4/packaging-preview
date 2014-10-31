var PreviewView = require('seo-views/preview'),
    wp = require('wp'),
    $ = require('jquery'),
	truncateString = require('seo-utils/truncate-util');

var TwitterPreviewLarge = PreviewView.extend({
    template: wp.template( 'seo-preview-twitter-large' ),
    className: 'seo-preview-twitter seo-preview-twitter-large',

    render: function() {
        var data = this.model.toJSON();

        data.twitter_card_title = truncateString( data.twitter_card_title, 100 );
        data.twitter_card_desc  = truncateString( data.twitter_card_desc, 300 );

        data.twitter_share_text  = truncateString( data.twitter_share_text, data.twitter_char_limit, ' ', '' );
        data.twitter_share_text  = data.twitter_share_text + ' ' + data.shortlink;
        data.twitter_share_text  = truncateString( data.twitter_share_text, 140, ' ', '' );

        // Remove @ from twitter handle (@FusionNews etc)
        data.twitter_user_name  = data.twitter_user_name.replace(/^@/, '');

		var spanify = function( match, p1 ) {
			return $('<span class="a" />').text( p1 ).prop('outerHTML');
		};
        var regExp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
        data.twitter_share_text = data.twitter_share_text.replace( regExp, spanify );

        regExp = /([#|@]\S+)/ig;
        data.twitter_share_text = data.twitter_share_text.replace( regExp, spanify );

        this.$el.html( this.template( data ) );

        return this;
    }
});

module.exports = TwitterPreviewLarge;

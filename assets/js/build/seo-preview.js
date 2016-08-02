(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
(function (global){
var Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

var Preview = Backbone.Model.extend({

    defaults: {
        title: '',
        desc: '',
        url: '',
        shortlink: '',
        image: '',
        seo_desc: '',
        seo_title: '',
        twitter_share_text: '',
        twitter_card_title: '',
        twitter_card_url: '',
        twitter_card_desc: '',
        twitter_card_image: '',
        twitter_char_limit: '140',
        facebook_share_text: '',
        open_graph_title: '',
        open_graph_url: '',
        open_graph_desc: '',
        open_graph_image: '',
        open_graph_site_name: '',
        twitter_user_name: '',
    },

    /**
     * Some attributes can default to the value
     * of another if nothing is provided.
     */
    defaultMap: {
        twitter_card_title:           'title',
        twitter_card_desc:            'open_graph_desc',
        twitter_share_text:           'title',
        twitter_card_image:           'open_graph_image',
        open_graph_title:             'title',
        open_graph_desc:              '',
        open_graph_image:             'image',
        seo_title:                    'title',
        seo_desc:                     'open_graph_desc',
    },

    /**
     * Custom toJSON.
     * Make sure all defaults are set before returning.
     *
     * @param  string attr
     * @return value
     */
    toJSON: function(options) {

        var json = Backbone.Model.prototype.toJSON.call( this, options );

        for ( var key in json ) {
            if ( ( key in this.defaultMap ) && ( ! json[key] || json[key].length < 1 ) ) {
                json[ key ] = this.get( this.defaultMap[ key ] );
            }
        }

        return json;
    },

    /**
     * Custom Get.
     * Check if empty, and this field should have a default value.
     *
     * @param  string attr
     * @return value
     */
    get: function(attr) {

            var val = this.attributes[attr];

            if ( val && val.length < 1 && ( attr in this.defaultMap ) ) {
                val = this.get( this.defaultMap[ attr ] );
            }

            return val;
    },
});

module.exports = Preview;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],2:[function(require,module,exports){
var TruncateString = function( string, limit, rightPad ) {
	var breakPoint, substr;
	var breakChar = ' ';

	if ( ! rightPad ) {
		rightPad = '...';
	}

	if ( ! string || string.length <= limit ) {
		return string;
	}

	substr = string.substr(0, limit);
	if ( ( breakPoint = substr.lastIndexOf( breakChar )  ) >= 0 ) {
		if ( breakPoint < string.length -1 ) {
			return string.substr( 0, breakPoint ) + rightPad;
		}
	} else {
		return substr + rightPad;
	}
	return string;
};

module.exports = TruncateString;

},{}],3:[function(require,module,exports){
(function (global){
var Preview = require('seo-views/preview'),
    truncateString = require('seo-utils/truncate-util'),
    wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

var FacebookPreview = Preview.extend({
    template: wp.template( 'seo-preview-facebook' ),
    className: 'seo-preview-facebook',
    render: function() {
        var data = this.model.toJSON();

        data.open_graph_title = truncateString( data.open_graph_title, 100 );
        data.open_graph_desc  = truncateString( data.open_graph_desc, 160 );

        this.$el.html( this.template( data ) );

        return this;
    }
});

module.exports = FacebookPreview;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"seo-utils/truncate-util":2,"seo-views/preview":5}],4:[function(require,module,exports){
(function (global){
var PreviewView = require('seo-views/preview'),
    truncateString = require('seo-utils/truncate-util'),
    wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null);

var GooglePreview = PreviewView.extend({
    template: wp.template( 'seo-preview-google' ),
    className: 'seo-preview-google',

    render: function() {
        var data = this.model.toJSON();

        data.title = truncateString( data.title, 60 );
        data.seo_desc  = truncateString( data.seo_desc, 150 );

        this.$el.html( this.template( data ) );

        return this;
    }
});

module.exports = GooglePreview;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"seo-utils/truncate-util":2,"seo-views/preview":5}],5:[function(require,module,exports){
(function (global){
var Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
    SeoPreviewModel = require('seo-models/preview');

var Preview = Backbone.View.extend({
    model: SeoPreviewModel,
    tagName: 'div',
    initialize: function( props ) {
        this.model = props.model;
    },
});

module.exports = Preview;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"seo-models/preview":1}],6:[function(require,module,exports){
(function (global){
var Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
    $ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null);

var SeoPreviewFacebookView = Backbone.View.extend( {
    initialize : function(options) {
        this.options = options;
    },
    events : {
        'click .fm-media-remove' : 'replaceFacebookPreview'
    },
    render : function() {
        var self = this;

        this.$el.on('DOMNodeInserted', function(e) {
            var target = e.target;
            if($(target).find('img').length > 0) {
                self.updateFacebookPreview($(target).find('img').first());
            }

        });
        return this;
    },
    updateFacebookPreview: function(img) {
        this.model.set( 'open_graph_image', img.attr('src') );
        this.options.seoPreview.render();
    },
    replaceFacebookPreview: function() {
        this.model.set( 'open_graph_image', this.options.featuredImage.attr('src') );
        this.options.seoPreview.render();
    }
});


module.exports = SeoPreviewFacebookView;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],7:[function(require,module,exports){
(function (global){
var Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
    $ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
    SeoPreviewModel = require('seo-models/preview'),
    SeoGooglePreviewView = require('seo-views/google-preview'),
    SeoTwitterPreviewLargeView = require('seo-views/twitter-large-preview'),
    SeoFacebookPreviewView = require('seo-views/facebook-preview');

var SeoPreview = Backbone.View.extend({

    template: wp.template( 'seo-preview-main' ),
    id: 'seo-preview' ,

    initialize: function( props ) {

        var t = this;
        this.props = new Backbone.Model( { defaults: { active: 'google', model: SeoPreviewModel } } );
        this.props.set( props );

        this.props.on( 'change:active', this.render, this );
        this.props.on( 'change:model', this.render, this );

    },
    events : {
        'change .fm-twitter_card_preview_type' : 'showSelectedPreviewType',
        'click .link-show-more-text' : 'ShowMoreText'
    },
    render: function() {

        var previewView;

        switch ( this.props.get('active') ) {
            case 'google':
            case 'seo':
                this.$el.html( new SeoGooglePreviewView( { model: this.props.get('model') } ).render().el );
                break;
            case 'twitter':
                this.$el.html( new SeoTwitterPreviewLargeView( { model: this.props.get('model') } ).render().el );
                break;
            case 'facebook':
                this.$el.html( new SeoFacebookPreviewView( { model: this.props.get('model') } ).render().el );
                break;
            default:
                this.$el.html('');
                break;
        }
        return this;
    },
    showSelectedPreviewType: function(e) {
        var $target = $( e.target ),
            target_val = $target.val();
        $.each([ 'wp-published-tweet', 'user-shared-tweet' ], function( index, accepted_val ) {
            if (accepted_val === target_val) {
                $('.twitter-card-preview-type.' + accepted_val).show();
            }else {
                $('.twitter-card-preview-type.' + accepted_val).hide();
            }
        });
    },
    ShowMoreText: function(e) {
		$( e.target ).parent().next().show();
		$( e.target ).empty();
    }

} );

module.exports = SeoPreview;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"seo-models/preview":1,"seo-views/facebook-preview":3,"seo-views/google-preview":4,"seo-views/twitter-large-preview":9}],8:[function(require,module,exports){
(function (global){
var Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
    $ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null);

var SeoPreviewTwiterView = Backbone.View.extend( {
    initialize: function(options) {
        this.options = options;
    },
    events : {
        'click .fm-media-remove' : 'replaceTwitterPreview'
    },
    render : function() {
        var self = this;

        this.$el.on('DOMNodeInserted', function(e) {
            var target = e.target;
            if($(target).find('img').length > 0) {
                self.updateTwitterPreview($(target).find('img').first());
            }

        });

        return this;
    },
    updateTwitterPreview: function(img) {
        this.model.set( 'twitter_card_image', img.attr('src') );
        this.options.seoPreview.render();
    },
    replaceTwitterPreview: function() {
        this.model.set( 'twitter_card_image', this.options.featuredImage.attr('src') );
        this.options.seoPreview.render();
    }
});


module.exports = SeoPreviewTwiterView;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],9:[function(require,module,exports){
(function (global){
var PreviewView = require('seo-views/preview'),
    wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null),
    $ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
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

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"seo-utils/truncate-util":2,"seo-views/preview":5}],10:[function(require,module,exports){
(function (global){
var jQuery = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	wp = (typeof window !== "undefined" ? window['wp'] : typeof global !== "undefined" ? global['wp'] : null),
	SeoPreviewTwiterView = require('seo-views/seo-twitter-main'),
	SeoPreviewFacebookView = require('seo-views/seo-facebook-main'),
	SeoPreviewView = require('seo-views/seo-preview'),
	SeoPreviewModel = require('seo-models/preview'),
	TruncateString = require('seo-utils/truncate-util');

( function( $ ) {

	var getCurrentTab = function( $metaBox ) {
		var currentTab = $metaBox.find( '.fm-fusion_distribution .wp-tab-active a' );
		if(currentTab.length > 0 ) {
			return currentTab.attr( 'href' ).match( /#fm-fusion_distribution-0-(.+?)-0-tab/ )[1];
		}
		return '';
	};

	$(document).ready(function(){

		var model, $metaBox, seoPreview, currentTab;

		model      = new SeoPreviewModel( fusionSeoPreviewData.model );
		seoPreview = new SeoPreviewView( { active: currentTab, model: model } );

		switch ( fusionSeoPreviewData.context ) {
			case 'term':
				$metaBox = $('.fm-fusion_distribution-wrapper').closest('td');
				$('.fm-fusion_distribution-wrapper').wrap('<div class="inside packaging-wrapper"></div>');
				break;
			case 'post':
				$metaBox   = $('#fm_meta_box_fusion_distribution');
				break;
			default:
				return;
		}

		currentTab = getCurrentTab( $metaBox );

		// Add the seo preview view to the metabox.
		$metaBox.find('.inside').append( seoPreview.render().el );

		// Trigger change view when switching tabs.
		$metaBox.find( '.fm-fusion_distribution .fm-tab' ).click( function(e) {
			var tab = getCurrentTab( $metaBox );
			if ( tab !== seoPreview.props.get( 'active' ) ) {
				seoPreview.props.set( 'active', tab );
			}
		} );

		// Prime the default tab's preview by triggering it's change:active event
		seoPreview.props.set( 'active', currentTab );

		// Input fields to attribute map
		// Used to update when these are changed.
		var inputFieldToValueMapping = {
			title:                  '[name="post_title"]',
			twitter_share_text:     '[name="fusion_distribution[twitter][share_text]"]',
			twitter_card_title:     '[name="fusion_distribution[twitter][title]"]',
			twitter_card_desc:      '[name="fusion_distribution[twitter][description]"]',
			twitter_card_image:     '[name="fusion_distribution[twitter][image]"]',
			facebook_share_text:    '[name="fusion_distribution[facebook][share_text]"]',
			open_graph_title:       '[name="fusion_distribution[facebook][title]"]',
			open_graph_desc:        '[name="fusion_distribution[facebook][description]"]',
			open_graph_image:       '[name="fusion_distribution[facebook][image]"]',
			seo_title:              '[name="fusion_distribution[seo][title]"]',
			seo_desc:               '[name="fusion_distribution[seo][description]"]',
		};

		// Callback to handle updating model when input changes.
		// Throttled to prevent rendering more than once every 1/2 seconds.
		var updatePreview = _.throttle( function() {
			var attr = $(this).attr('data-seo-preview-field');
			if ( attr in inputFieldToValueMapping ) {
				model.set( attr, $(this).val() );
				seoPreview.render();
			}
		}, 500 );

		// For each of the input/field pairings stored in inputFieldToValueMapping
		// Update the model value whenever this data changes.
		for ( var field in inputFieldToValueMapping ) {

			$( inputFieldToValueMapping[ field ] ).attr( 'data-seo-preview-field', field );

			// Note multiple events to catch everything.
			$( inputFieldToValueMapping[ field ] ).on( 'keyup change blur', updatePreview );

		}

		var updateDistributionTitle = _.throttle(function () {
			var title = $('#title');

			var twitter_share_text = $('[name="fusion_distribution[twitter][share_text]"]');
			var twitter_card_title = $('[name="fusion_distribution[twitter][title]"]');
			var open_graph_title   = $('[name="fusion_distribution[facebook][title]"]');
			var seo_title          = $('[name="fusion_distribution[seo][title]"]');

			var twitter_share_title = TruncateString( title.val(), 70 );
			var seo_share_title = TruncateString( title.val(), 60 );

			twitter_share_text.attr("placeholder", twitter_share_title);
			twitter_card_title.attr("placeholder", twitter_share_title);
			open_graph_title.attr("placeholder", title.val());
			seo_title.attr("placeholder", seo_share_title);

		}, 100);

		var updateOtherDescriptions = _.throttle( function(){
			var content = $('[name="fusion_distribution[facebook][description]"]');

			var twitter_card_desc    = $('[name="fusion_distribution[twitter][description]"]');
			var seo_desc             = $('[name="fusion_distribution[seo][description]"]');

			var twitter_share_content = TruncateString( content.val(), 200 );
			var seo_share_content = TruncateString( content.val(), 160 );

			twitter_card_desc.attr("placeholder", twitter_share_content);
			seo_desc.attr("placeholder", seo_share_content);
		}, 100);

		$('#title').on('keyup change blur', updateDistributionTitle);
		$('[name="fusion_distribution[facebook][description]"]').on('keyup change blur', updateOtherDescriptions);

		// Update featured image function.
		// We have to listen for the frame to be closed, then grab the value.
		// @todo - convert this ID to URL...
		var updateFeaturedImage = function() {
			wp.media.featuredImage.frame().state('featured-image').on( 'select', function() {

				var data = {
					'action': 'seo_preview_get_image_src',
					'image_id': wp.media.featuredImage.get(),
					'nonce': fusionSeoPreviewData.imagePreviewNonce
				};

				$.post(ajaxurl, data, function( response ) {
					if ( response.success ) {

						model.set( 'image', response.data.image_src );

						if ( $('input[name="fusion_distribution[facebook][image]"]').val().length < 1 ) {
							model.set( 'open_graph_image', response.data.image_src );
						}

						if ( $('input[name="fusion_distribution[twitter][image]"]').val().length < 1 ) {
							model.set( 'twitter_card_image', response.data.image_src );
						}

						seoPreview.render();

					}
				});

			} );
		};

		$('#postimagediv a').click( updateFeaturedImage );

		// Update desc with ex - unless excerpt is set...
		var $excerptField = $('textarea[name=excerpt]');

		if ( $excerptField.length ) {
			$excerptField.on( 'change keyup', function() {
				model.set( 'desc', $excerptField.val() );
				seoPreview.render();
			} );

			/**
			 * If no excerpt is set manually this should fall back
			 * to a trunkated version of the content.
			 *
			 * @return null
			 */
			window.addEventListener('load', function () {
				var tinymce = (typeof window !== "undefined" ? window['tinymce'] : typeof global !== "undefined" ? global['tinymce'] : null);
				if(tinymce.get('content')) {
					tinymce.get('content').on('keyup',function(e){
						if ( $excerptField.val().length < 1 ) {
							var content = window.switchEditors.pre_wpautop( this.getContent() );

							content = TruncateString( content, 500 );
							model.set( 'desc', content );
						}
					});
				}
			}, false);
		}

		var seoPreviewTwitterView = new SeoPreviewTwiterView({
			el : $('.fm-twitter-wrapper'),
			model : model,
			seoPreview : seoPreview,
			featuredImage : $('#postimagediv img')
			});
		seoPreviewTwitterView.render();

		var seoPreviewFacebookView = new SeoPreviewFacebookView({
			el : $('.fm-facebook-wrapper'),
			model : model,
			seoPreview : seoPreview,
			featuredImage : $('#postimagediv img')
			});
		seoPreviewFacebookView.render();

	});

} )( jQuery );

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"seo-models/preview":1,"seo-utils/truncate-util":2,"seo-views/seo-facebook-main":6,"seo-views/seo-preview":7,"seo-views/seo-twitter-main":8}]},{},[10]);

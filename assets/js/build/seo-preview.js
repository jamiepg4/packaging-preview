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
},{"seo-utils/truncate-util":2,"seo-views/preview":5}]},{},[1,2,3,4,5,6,7,8,9]);

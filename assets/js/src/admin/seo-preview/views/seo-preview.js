var Backbone = require('backbone'),
    $ = require('jquery'),
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

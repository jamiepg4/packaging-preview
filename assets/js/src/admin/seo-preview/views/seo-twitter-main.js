var Backbone = require('backbone'),
    $ = require('jquery');

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

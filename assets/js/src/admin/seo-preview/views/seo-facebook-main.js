var Backbone = require('backbone'),
    $ = require('jquery');

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

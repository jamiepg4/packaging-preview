var PreviewView = require('seo-views/preview'),
    truncateString = require('seo-utils/truncate-util'),
    wp = require('wp');

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

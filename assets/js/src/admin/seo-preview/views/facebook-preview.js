var Preview = require('seo-views/preview'),
    truncateString = require('seo-utils/truncate-util'),
    wp = require('wp');

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

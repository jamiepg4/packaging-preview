var Backbone = require('backbone'),
    SeoPreviewModel = require('seo-models/preview');

var Preview = Backbone.View.extend({
    model: SeoPreviewModel,
    tagName: 'div',
    initialize: function( props ) {
        this.model = props.model;
    },
});

module.exports = Preview;

var Backbone = require('backbone');

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

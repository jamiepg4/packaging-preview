var SeoPreviewModel = require('seo-models/preview.js'),
    _ = require('underscore');

describe('When calling seo preview model', function() {
    var model = new SeoPreviewModel();

    it('should have all defaults value', function() {
        var defaults = model.defaults;

        _.each(defaults, function (value, prop) {
            if( 'twitter_char_limit' === prop ) {
                expect(value).toBe('140');
            } else {
                expect(value).toBe('');
            }
        });

    });
});

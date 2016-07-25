var SeoFacebookMainPreview = require('seo-views/seo-facebook-main'),
    SeoPreviewModel = require('seo-models/preview'),
    SeoPreviewView = require('seo-views/seo-preview');

describe('When calling SEO Facebook Preview model', function() {
    var testDiv, removeButton, featuredImage;
    beforeEach(function() {
        testDiv = $('<div>', { class : 'fm-facebook-wrapper'});
        var mediaWrapper = $('<div>', { class : 'media-wrapper' });
        removeButton = $('<a>', { class: 'fm-media-remove' } );
        var image = $('<img>');

        featuredImage = $('<div>', { id : 'postimagediv'} );
        featuredImage.append(image);

        mediaWrapper.append(image);
        mediaWrapper.append(removeButton);
        testDiv.append(mediaWrapper);

    });

    describe('When uploading preview image for facebook', function() {

        it('should change the preview image, given preview image is uploaded', function() {
            var view = new SeoFacebookMainPreview({el : testDiv});
            view.render();

            spyOn(view, 'updateFacebookPreview');
            testDiv.trigger('DOMNodeInserted');

            view.delegateEvents();
            expect(view.updateFacebookPreview).toHaveBeenCalled();

        });

        it('should not change the preview image, given preview image is not uploaded', function() {
            var view = new SeoFacebookMainPreview({el : testDiv});
            view.render();

            spyOn(view, 'updateFacebookPreview');

            view.delegateEvents();
            expect(view.updateFacebookPreview).not.toHaveBeenCalled();

        });

    });

    describe('When deleting preview image', function() {
        it('should fall back to featured image, given it exists', function() {
            var model = new SeoPreviewModel();
            spyOn(model, 'set');

            var seoPreview = new SeoPreviewView();
            spyOn(seoPreview, 'render');

            var view = new SeoFacebookMainPreview({ el : testDiv, model : model, seoPreview : seoPreview, featuredImage : featuredImage });
            view.render();

            removeButton.trigger('click');

            view.delegateEvents();
            expect(model.set).toHaveBeenCalled();
            expect(seoPreview.render).toHaveBeenCalled();
        });
    });

});

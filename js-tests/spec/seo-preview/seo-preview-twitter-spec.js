var SeoTwitterMainPreview = require('seo-views/seo-twitter-main'),
    SeoPreviewModel = require('seo-models/preview'),
    SeoPreviewView = require('seo-views/seo-preview');

describe('When calling SEO Twitter Preview model', function() {
    var testDiv, removeButton, featuredImage;
    beforeEach(function() {
        testDiv = $('<div>', { class : 'fm-twitter-wrapper'});
        var mediaWrapper = $('<div>', { class : 'media-wrapper' });
        removeButton = $('<a>', { class: 'fm-media-remove' } );
        var image = $('<img>', { src : 'image.jpg'} );

        featuredImage = $('<div>', { id : 'postimagediv'} );
        featuredImage.append(image);

        mediaWrapper.append(image);
        mediaWrapper.append(removeButton);
        testDiv.append(mediaWrapper);

    });

    describe('When uploading preview image for twitter', function() {

        it('should change the preview image, given preview image is uploaded', function() {
            var view = new SeoTwitterMainPreview({el : testDiv});
            view.render();

            spyOn(view, 'updateTwitterPreview');
            testDiv.trigger('DOMNodeInserted');

            view.delegateEvents();
            expect(view.updateTwitterPreview).toHaveBeenCalled();

        });

        it('should not change the preview image, given preview image is not uploaded', function() {
            var view = new SeoTwitterMainPreview({ el : testDiv });
            view.render();

            spyOn(view, 'updateTwitterPreview');

            view.delegateEvents();
            expect(view.updateTwitterPreview).not.toHaveBeenCalled();

        });

    });

    describe('When deleting preview image', function() {
        it('should fall back to featured image, given it exists', function() {
            var model = new SeoPreviewModel();
            spyOn(model, 'set');

            var seoPreview = new SeoPreviewView();
            spyOn(seoPreview, 'render');

            var view = new SeoTwitterMainPreview({ el : testDiv, model : model, seoPreview : seoPreview, featuredImage : featuredImage });
            view.render();

            removeButton.trigger('click');

            view.delegateEvents();
            expect(model.set).toHaveBeenCalled();
            expect(seoPreview.render).toHaveBeenCalled();
        });
    });

});

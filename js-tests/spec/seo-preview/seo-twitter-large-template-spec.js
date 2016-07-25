var SeoTwitterLargePreviewView = require('seo-views/twitter-large-preview'),
    SeoPreviewModel = require('seo-models/preview');

describe('When calling twitter large template', function() {
    var model;
    beforeEach(function() {
        jasmine.getFixtures().fixturesPath = 'parts';
        loadFixtures('admin/seo-preview/twitter.php');
        model = new SeoPreviewModel();
    });

    describe('When viewing given no image', function() {
        it('should not show twitter image', function() {
            model.set({
                twitter_card_image : 'http://www.example.org/image.jpg'
            });

            var seoTwitterPreviewView = new SeoTwitterLargePreviewView({model : model});
            seoTwitterPreviewView.render();

            expect(seoTwitterPreviewView.$el.html()).not.toContainHtml('<img src="http://www.example.org/image.jpg" alt="" />');
        });

    });

    describe('When viewing given image', function() {
        it('should show twitter image', function() {
            model.set({
                twitter_card_image : 'http://www.example.org/image.jpg'
            });

            var seoTwitterPreviewView = new SeoTwitterLargePreviewView({model : model});
            seoTwitterPreviewView.render();

            expect(seoTwitterPreviewView.$el).toContainHtml('<img src="http://www.example.org/image.jpg" alt="" />');
        });

    });

    describe('When viewing twitter other attributes', function() {
        it('should show twitter title', function() {
            model.set({
                twitter_card_title : 'twitter_card_title'
            });

            var seoTwitterPreviewView = new SeoTwitterLargePreviewView({model : model});
            seoTwitterPreviewView.render();

            expect(seoTwitterPreviewView.$el.html()).toMatch('twitter_card_title');
        });

        it('should show twitter description', function() {
            model.set({
                twitter_card_desc : 'twitter_card_desc'
            });

            var seoTwitterPreviewView = new SeoTwitterLargePreviewView({model : model});
            seoTwitterPreviewView.render();

            expect(seoTwitterPreviewView.$el.html()).toMatch('twitter_card_desc');
        });

        it('should show twitter user name', function() {
            model.set({
                twitter_user_name : 'twitter_user_name'
            });

            var seoTwitterPreviewView = new SeoTwitterLargePreviewView({model : model});
            seoTwitterPreviewView.render();

            expect(seoTwitterPreviewView.$el.html()).toMatch('twitter_user_name');
        });

    });
});

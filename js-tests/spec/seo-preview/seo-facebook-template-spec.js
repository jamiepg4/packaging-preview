var SeoFacebookPreviewView = require('seo-views/facebook-preview'),
    SeoPreviewModel = require('seo-models/preview');


describe('When viewing Facebook preview', function() {

    var model;
    beforeEach(function() {
        jasmine.getFixtures().fixturesPath = 'parts';
        loadFixtures('admin/seo-preview/facebook.php');
        model = new SeoPreviewModel();
    });


    describe('When viewing given no image', function() {
        it('should show facebook open graph image', function() {

            model.set({
                open_graph_image : 'http://www.example.org/image.jpg'
            });

            var seoFacebookPreviewView = new SeoFacebookPreviewView({model : model});
            seoFacebookPreviewView.render();

            expect(seoFacebookPreviewView.$el).toContainHtml('<img src="http://www.example.org/image.jpg" alt="" />');
        });

    });

    describe('When viewing with image', function() {
        it('should not show facebook open graph image', function() {
            model.set({

            });

            var seoFacebookPreviewView = new SeoFacebookPreviewView({model : model});
            seoFacebookPreviewView.render();

            expect(seoFacebookPreviewView.$el).not.toContainHtml('<img src="http://www.example.org/image.jpg" alt="" />');
        });

    });

    describe('When viewing the facebook preview', function() {
        it('should have correct open graph title', function(){
            model.set({
                open_graph_title : "open graph title"
            });

            var seoFacebookPreviewView = new SeoFacebookPreviewView({model : model});
            seoFacebookPreviewView.render();

            expect(seoFacebookPreviewView.$el.html()).toMatch('open graph title');
        });

        it('should have correct open graph description', function() {
            model.set({
                open_graph_desc : "open graph description"
            });

            var seoFacebookPreviewView = new SeoFacebookPreviewView({model : model});
            seoFacebookPreviewView.render();

            expect(seoFacebookPreviewView.$el.html()).toMatch('open graph description');
        });

        it('should have correct open graph site name', function() {
            model.set({
                open_graph_site_name : "open graph site name"
            });

            var seoFacebookPreviewView = new SeoFacebookPreviewView({model : model});
            seoFacebookPreviewView.render();

            expect(seoFacebookPreviewView.$el.html()).toMatch('open graph site name');
        });


    });


});

var SeoGooglePreviewView = require('seo-views/google-preview'),
    SeoPreviewModel = require('seo-models/preview');

describe('When calling Google preview', function() {

    var model;
    beforeEach(function() {
        jasmine.getFixtures().fixturesPath = 'parts';
        loadFixtures('admin/seo-preview/google.php');

        model = new SeoPreviewModel();
    });

    it('should render google preview title', function() {
        model.set({seo_title : 'title'});

        var seoGooglePreviewView = new SeoGooglePreviewView({model : model});
        seoGooglePreviewView.render();

        expect(seoGooglePreviewView.$el).toContainHtml('<span class="a">title</span>');
    });

    it('should render google preview url', function() {
        model.set({url : 'http://www.example.org'});

        var seoGooglePreviewView = new SeoGooglePreviewView({model : model});
        seoGooglePreviewView.render();

        expect(seoGooglePreviewView.$el.html()).toMatch('http://www.example.org');
    });

    it('should render google preview description', function() {
        model.set({seo_desc : 'seo description'});

        var seoGooglePreviewView = new SeoGooglePreviewView({model : model});
        seoGooglePreviewView.render();

        expect(seoGooglePreviewView.$el.html()).toMatch('seo description');
    });
});

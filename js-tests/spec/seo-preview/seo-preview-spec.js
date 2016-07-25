var SeoPreviewView = require('seo-views/seo-preview'),
    SeoPreviewModel = require('seo-models/preview');

describe('When calling seo preview', function() {
    beforeEach(function() {
        jasmine.getFixtures().fixturesPath = 'parts';
    });

    describe('Google preview', function() {
        beforeEach(function() {
            loadFixtures('admin/seo-preview/google.php');
        });

        var seoPreviewView = new SeoPreviewView({active: 'google', model : new SeoPreviewModel()});

        it('should have id seo-preview', function() {
            expect(seoPreviewView.id).toBe('seo-preview');
        });

        it('should display google preview', function() {
            seoPreviewView.render();

            expect(seoPreviewView.$el.html()).toMatch('seo-preview-google');
        });
    });

    describe('Seo preview', function() {
        var seoPreviewView = new SeoPreviewView({active: 'seo', model : new SeoPreviewModel()});

        beforeEach(function() {
            loadFixtures('admin/seo-preview/google.php');
        });

        it('should have id seo-preview', function() {
            expect(seoPreviewView.id).toBe('seo-preview');
        });

        it('should display google preview', function() {
            seoPreviewView.render();

            expect(seoPreviewView.$el.html()).toMatch('seo-preview-google');
        });
    });

    describe('Twitter preview', function() {
        var seoPreviewView = new SeoPreviewView({active: 'twitter', model : new SeoPreviewModel()});

        beforeEach(function() {
            loadFixtures('admin/seo-preview/twitter.php');
        });

        it('should have id seo-preview', function() {
            expect(seoPreviewView.id).toBe('seo-preview');
        });

        it('should display twitter large and small', function() {
            seoPreviewView.render();

            expect(seoPreviewView.$el.html()).toMatch('seo-preview-twitter seo-preview-twitter-large');
        });
    });

    describe('Facebook preview', function() {
        var seoPreviewView = new SeoPreviewView({active: 'facebook', model : new SeoPreviewModel()});

        beforeEach(function() {
            loadFixtures('admin/seo-preview/facebook.php');
        });


        it('should have id seo-preview', function() {
            expect(seoPreviewView.id).toBe('seo-preview');
        });

        it('should display facebook', function() {
            seoPreviewView.render();

            expect(seoPreviewView.$el.html()).toMatch('seo-preview-facebook');
        });
    });

    describe('Default preview', function() {
        var seoPreviewView = new SeoPreviewView({active: '', model : new SeoPreviewModel()});

        it('should have id seo-preview', function() {
            expect(seoPreviewView.id).toBe('seo-preview');
        });

        it('should display default mode', function() {
            seoPreviewView.render();

            expect(seoPreviewView.$el.html()).toBe('');
        });
    });
});

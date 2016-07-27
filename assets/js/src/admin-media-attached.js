(function(){
    /**
     * Extend and override wp.media.view.AttachmentsBrowser to add existing All Filter
     */
    var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
    wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
        createToolbar: function() {
            // Make sure to load the original toolbar
            AttachmentsBrowser.prototype.createToolbar.call( this );
            this.toolbar.set( 'filters', new wp.media.view.AttachmentFilters.All({
                controller: this.controller,
                model:      this.collection.props,
                priority:   -80
            }).render() );
        }
    });
})();
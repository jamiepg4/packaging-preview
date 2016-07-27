var jQuery = require('jquery'),
	wp = require('wp'),
	SeoPreviewTwiterView = require('seo-views/seo-twitter-main'),
	SeoPreviewFacebookView = require('seo-views/seo-facebook-main'),
	SeoPreviewView = require('seo-views/seo-preview'),
	SeoPreviewModel = require('seo-models/preview'),
	TruncateString = require('seo-utils/truncate-util');

( function( $ ) {

	var getCurrentTab = function( $metaBox ) {
		var currentTab = $metaBox.find( '.fm-fusion_distribution .wp-tab-active a' );
		if(currentTab.length > 0 ) {
			return currentTab.attr( 'href' ).match( /#fm-fusion_distribution-0-(.+?)-0-tab/ )[1];
		}
		return '';
	};

	$(document).ready(function(){

		var model, $metaBox, seoPreview, currentTab;

		model      = new SeoPreviewModel( fusionSeoPreviewData.model );
		seoPreview = new SeoPreviewView( { active: currentTab, model: model } );

        console.log( fusionSeoPreviewData );
		switch ( fusionSeoPreviewData.context ) {
			case 'term':
				$metaBox = $('.fm-fusion_distribution-wrapper').closest('td');
				$('.fm-fusion_distribution-wrapper').wrap('<div class="inside packaging-wrapper"></div>');
				break;
			case 'post':
				$metaBox   = $('#fm_meta_box_fusion_distribution');
				break;
			default:
				return;
		}

		currentTab = getCurrentTab( $metaBox );

		// Add the seo preview view to the metabox.
		$metaBox.find('.inside').append( seoPreview.render().el );

		// Trigger change view when switching tabs.
		$metaBox.find( '.fm-fusion_distribution .fm-tab' ).click( function(e) {
			var tab = getCurrentTab( $metaBox );
			if ( tab !== seoPreview.props.get( 'active' ) ) {
				seoPreview.props.set( 'active', tab );
			}
		} );

		// Prime the default tab's preview by triggering it's change:active event
		seoPreview.props.set( 'active', currentTab );

		// Input fields to attribute map
		// Used to update when these are changed.
		var inputFieldToValueMapping = {
			title:                  '[name="post_title"]',
			twitter_share_text:     '[name="fusion_distribution[twitter][share_text]"]',
			twitter_card_title:     '[name="fusion_distribution[twitter][title]"]',
			twitter_card_desc:      '[name="fusion_distribution[twitter][description]"]',
			twitter_card_image:     '[name="fusion_distribution[twitter][image]"]',
			facebook_share_text:    '[name="fusion_distribution[facebook][share_text]"]',
			open_graph_title:       '[name="fusion_distribution[facebook][title]"]',
			open_graph_desc:        '[name="fusion_distribution[facebook][description]"]',
			open_graph_image:       '[name="fusion_distribution[facebook][image]"]',
			seo_title:              '[name="fusion_distribution[seo][title]"]',
			seo_desc:               '[name="fusion_distribution[seo][description]"]',
		};

		// Callback to handle updating model when input changes.
		// Throttled to prevent rendering more than once every 1/2 seconds.
		var updatePreview = _.throttle( function() {
			var attr = $(this).attr('data-seo-preview-field');
			if ( attr in inputFieldToValueMapping ) {
				model.set( attr, $(this).val() );
				seoPreview.render();
			}
		}, 500 );

		// For each of the input/field pairings stored in inputFieldToValueMapping
		// Update the model value whenever this data changes.
		for ( var field in inputFieldToValueMapping ) {

			$( inputFieldToValueMapping[ field ] ).attr( 'data-seo-preview-field', field );

			// Note multiple events to catch everything.
			$( inputFieldToValueMapping[ field ] ).on( 'keyup change blur', updatePreview );

		}

		var updateDistributionTitle = _.throttle(function () {
			var title = $('#title');

			var twitter_share_text = $('[name="fusion_distribution[twitter][share_text]"]');
			var twitter_card_title = $('[name="fusion_distribution[twitter][title]"]');
			var open_graph_title   = $('[name="fusion_distribution[facebook][title]"]');
			var seo_title          = $('[name="fusion_distribution[seo][title]"]');

			var twitter_share_title = TruncateString( title.val(), 70 );
			var seo_share_title = TruncateString( title.val(), 60 );

			twitter_share_text.attr("placeholder", twitter_share_title);
			twitter_card_title.attr("placeholder", twitter_share_title);
			open_graph_title.attr("placeholder", title.val());
			seo_title.attr("placeholder", seo_share_title);

		}, 100);

		var updateOtherDescriptions = _.throttle( function(){
			var content = $('[name="fusion_distribution[facebook][description]"]');

			var twitter_card_desc    = $('[name="fusion_distribution[twitter][description]"]');
			var seo_desc             = $('[name="fusion_distribution[seo][description]"]');

			var twitter_share_content = TruncateString( content.val(), 200 );
			var seo_share_content = TruncateString( content.val(), 160 );

			twitter_card_desc.attr("placeholder", twitter_share_content);
			seo_desc.attr("placeholder", seo_share_content);
		}, 100);

		$('#title').on('keyup change blur', updateDistributionTitle);
		$('[name="fusion_distribution[facebook][description]"]').on('keyup change blur', updateOtherDescriptions);

		// Update featured image function.
		// We have to listen for the frame to be closed, then grab the value.
		// @todo - convert this ID to URL...
		var updateFeaturedImage = function() {
			wp.media.featuredImage.frame().state('featured-image').on( 'select', function() {

				var data = {
					'action': 'seo_preview_get_image_src',
					'image_id': wp.media.featuredImage.get(),
					'nonce': fusionSeoPreviewData.imagePreviewNonce
				};

				$.post(ajaxurl, data, function( response ) {
					if ( response.success ) {

						model.set( 'image', response.data.image_src );

						if ( $('input[name="fusion_distribution[facebook][image]"]').val().length < 1 ) {
							model.set( 'open_graph_image', response.data.image_src );
						}

						if ( $('input[name="fusion_distribution[twitter][image]"]').val().length < 1 ) {
							model.set( 'twitter_card_image', response.data.image_src );
						}

						seoPreview.render();

					}
				});

			} );
		};

		$('#postimagediv a').click( updateFeaturedImage );

		// Update desc with ex - unless excerpt is set...
		var $excerptField = $('textarea[name=excerpt]');

		if ( $excerptField.length ) {
			$excerptField.on( 'change keyup', function() {
				model.set( 'desc', $excerptField.val() );
				seoPreview.render();
			} );

			/**
			 * If no excerpt is set manually this should fall back
			 * to a trunkated version of the content.
			 *
			 * @return null
			 */
			window.addEventListener('load', function () {
				var tinymce = require('tinymce');
				if(tinymce.get('content')) {
					tinymce.get('content').on('keyup',function(e){
						if ( $excerptField.val().length < 1 ) {
							var content = window.switchEditors.pre_wpautop( this.getContent() );

							content = TruncateString( content, 500 );
							model.set( 'desc', content );
						}
					});
				}
			}, false);
		}

		var seoPreviewTwitterView = new SeoPreviewTwiterView({
			el : $('.fm-twitter-wrapper'),
			model : model,
			seoPreview : seoPreview,
			featuredImage : $('#postimagediv img')
			});
		seoPreviewTwitterView.render();

		var seoPreviewFacebookView = new SeoPreviewFacebookView({
			el : $('.fm-facebook-wrapper'),
			model : model,
			seoPreview : seoPreview,
			featuredImage : $('#postimagediv img')
			});
		seoPreviewFacebookView.render();

	});

} )( jQuery );

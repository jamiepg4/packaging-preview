<script type="text/html" id="tmpl-seo-preview-facebook">

	<h4><?php esc_html_e( 'Facebook Share Preview', 'fusion' ); ?></h4>

	<div class="seo-preview-view">

		<div class="seo-preview-facebook-share-text-wrap">
			<img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . '/assets/images/facebook-head-s50.png' ); ?>" />

			<div class="seo-preview-facebook-share-text">
				<# if ( data.facebook_share_text !== '' ) { #>
					{{ data.facebook_share_text }}
				<# } else { #>
					<em><?php esc_html_e( "What's on your mind?", 'fusion' ); ?></em>
				<# } #>
			</div>
		</div>

		<# if ( data.open_graph_image !== '' ) { #>
		<div class="seo-preview-image" style="background-image: url( '{{ data.open_graph_image }}' );">
			<img src="{{ data.open_graph_image }}" alt="" />
		</div>
		<# } #>

		<div class="seo-preview-info-container">

			<div class="seo-preview-title">
				<span class="a">{{ data.open_graph_title }}</span>
			</div>

			<div class="seo-preview-desc">{{ data.open_graph_desc }}</div>
			<div class="seo-preview-url">{{ data.open_graph_site_name }}</div>

		</div>

	</div>

	<p class="fm-item-description">Note: Facebook images do not animate.</p>

</script>

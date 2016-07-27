<script type="text/html" id="tmpl-seo-preview-google">

	<h4><?php esc_html_e( 'Search Preview', 'fusion' ); ?></h4>

	<div class="seo-preview-view">

		<div class="seo-preview-title">
			<span class="a">{{ data.seo_title }}</span>
		</div>

		<div class="seo-preview-url">
			{{ data.url }}
		</div>

		<span class="seo-preview-desc">
			{{ data.seo_desc }}
		</span>

	</div>

</script>
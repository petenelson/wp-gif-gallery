<div id="wp-backbone-gallery-container"></div>

<script type="text/template" id="tmpl-wp-backbone-gallery">

	<div class="gallery gallery-columns-{{ data.columns }} gallery-size-thumbnail">

		<# _.each( data.images, function( image ) { #>

			<figure class="gallery-item">
				<div class="gallery-icon landscape">
					<a href="{{ image.src }}">
						<img width="{{ image.thumbnail.width }}" height="{{ image.thumbnail.height }}" src="{{ image.thumbnail.src }}" class="attachment-thumbnail size-thumbnail" alt="{{ image.alt }}" />
					</a>
				</div>
				<figcaption class="wp-caption-text gallery-caption">
					{{ image.title }}
					<# if ( '' !== image.gifv_url ) { #>
						- <a href="{{ image.gifv_url }}"><?php esc_html_e( 'GIFV', 'wp-backbone-gallery' ); ?></a>
					<# } #>
				</figcaption>
			</figure>

		<# } ); // _.each #>
	</div>

</script>

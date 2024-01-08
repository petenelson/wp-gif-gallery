<div id="wp-gif-gallery-container">

	<p>
		<input type="text" class="regular-text wp-gif-gallery-search" placeholder="<?php echo esc_attr_e( 'Search' ); ?>" />
	</p>

	<div class="gallery <?php echo sanitize_html_class( 'gallery-columns-' . $data['columns'] ); ?> gallery-size-thumbnail">

		<?php foreach( $data['images'] as $image ): ?>

			<figure class="gallery-item" data-title="<?php echo esc_attr( $image['title'] ); ?>" data-caption="<?php echo esc_attr( $image['caption'] ); ?>" data-slug="<?php echo esc_attr( $image['slug'] ); ?>" data-alt="<?php echo esc_attr( $image['alt'] ); ?>">
				<div class="gallery-icon landscape">
					<a href="<?php echo esc_url( $image['src'] ); ?>" class="image-link">
						<img
							width="<?php echo esc_attr( $image['thumbnail']['width'] ); ?>"
							height="<?php echo esc_attr( $image['thumbnail']['height'] ); ?>"
							src="<?php echo esc_url( $image['thumbnail']['src'] ); ?>"
							class="attachment-thumbnail size-thumbnail"
							alt="<?php echo esc_attr( $image['alt'] ); ?>"
							data-long-press-delay="500"
						/>
					</a>
				</div>
				<figcaption class="wp-caption-text gallery-caption">
					<?php echo esc_html( $image['title'] ); ?>
					<?php if ( ! empty( $image['gifv_url'] ) ): ?>
						- <a href="<?php echo esc_url( $image['gifv_url'] ); ?>"><?php esc_html_e( 'GIFV', 'wp-gif-gallery' ); ?></a>
					<?php endif; ?>
				</figcaption>
			</figure>

		<?php endforeach; ?>

	</div>
</div>

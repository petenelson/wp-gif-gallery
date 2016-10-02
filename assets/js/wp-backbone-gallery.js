( function( $ ) {

	'use strict';


	var WP_Backbone_Gallery = {

		container: null,
		search: null,

		init: function() {

			this.container = $( document.getElementById( 'wp-backbone-gallery-container' ) );
			if ( this.container.length === 0 ) {
				return;
			}

			this.createModel();
			this.createView();

			this.model = new this.backbone_model( WP_Backbone_Gallery_Data );

			this.view = new this.backbone_view( {
				model: this.model,
				el: this.container
			} );

			this.view.render();

			this.search = $( '.wp-backbone-gallery-search' );
			this.search.on( 'keyup', this.searchImages.bind( this ) ).focus();
		},

		searchImages: function() {
			var search = this.search.val();
			var images = this.model.get( 'images' );

			var filtered_images = [];

			_.each( images, function( image ) {

				if (
					   this.stringMatch( image.title, search )
					|| this.stringMatch( image.caption, search )
					|| this.stringMatch( image.slug, search )
					) {

					filtered_images.push( image );

				}

			}, this );

			this.model.set( 'filtered_images', filtered_images );

		},

		stringMatch: function( string, search ) {
			return string.toLowerCase().includes( search.toLowerCase() );
		},

		createModel: function() {

			this.backbone_model = Backbone.Model.extend( {
				initialize: function( options ) {
					this.set( 'filtered_images', options.images );
				}
			} );

		},

		createView: function() {

			this.backbone_view = Backbone.View.extend( {
					template: wp.template( 'wp-backbone-gallery' ),
					events: {

					},

					initialize: function( options ) {
						this.listenTo( this.model, 'change', this.render )
					},

					// Used to create new components
					render: function() {
						this.$el.html( this.template( this.model.toJSON() ) );
						return this;
					},

				} );

		}

	};

	WP_Backbone_Gallery.init();

} ) ( jQuery );

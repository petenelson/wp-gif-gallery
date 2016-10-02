( function( $ ) {

	'use strict';


	var WP_Backbone_Gallery = {

		container: null,

		init: function() {

			this.container = $( document.getElementById( 'wp-backbone-gallery-container' ) );
			if ( this.container.length === 0 ) {
				return;
			}

			this.view = new WP_Backbone_Gallery_View( {
				model: new WP_Backbone_Gallery_Model( WP_Backbone_Gallery_Data ),
				el: this.container
			} );

			this.view.render();

		}

	};

	var WP_Backbone_Gallery_View = Backbone.View.extend( {

		template: wp.template( 'wp-backbone-gallery' ),

		events: {

		},

		// Used to create new components
		render: function() {

			this.$el.html( this.template( this.model.toJSON() ) );

			return this;
		},

	} );

	var WP_Backbone_Gallery_Model = Backbone.Model.extend( {

	} );


	WP_Backbone_Gallery.init();


} ) ( jQuery );

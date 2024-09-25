/**
 * File editor.js
 */

 wp.domReady( function() {

	/**
	 * Set the featured image meta box size.
	 */
	wp.hooks.addFilter( 'editor.PostFeaturedImage.imageSize', 'pol/editor-featured-image-size', function ( size, mediaId, postId ) {
		return 'medium';
	} );


	/**
   * Remove block editor discussion panel.
   */
	 wp.data.dispatch( 'core/edit-post' ).removeEditorPanel( 'discussion-panel' );

} );
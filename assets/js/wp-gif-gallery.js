window.addEventListener('DOMContentLoaded', () => {

	const container = document.getElementById('wp-gif-gallery-container');
	if (!container) {
		return;
	}

	const search = container.querySelector('.wp-gif-gallery-search');
	search.focus();

	search.addEventListener('keyup', function(event) {

		if (event.key && 'Escape' === event.key) {
			search.value = '';
		}

		const value = search.value.trim().toLowerCase();

		// Show all of the GIFs.
		const images = container.querySelectorAll('.gallery-item');
		images.forEach( image => {
			image.style.display = '';
		});

		if ('' === value) {
			return;
		}

		// Hide all of the GIFs that don't match the search value.
		images.forEach( image => {
			const allFields = (image.dataset.title + ' ' + image.dataset.caption + ' ' + image.dataset.slug).toLowerCase();
			if (!allFields.includes(value)) {
				image.style.display = 'none';
			}
		});
	});


	document.addEventListener('long-press', function(e) {
		const target = e.target;
		if (target.classList.contains('attachment-thumbnail') && target.parentElement.classList.contains('image-link')) {
			e.preventDefault();
			navigator.clipboard.writeText(target.parentElement.href);
		}
	});
});

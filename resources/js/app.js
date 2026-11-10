import './bootstrap';

document.querySelectorAll('img[src]:not([src=""])').forEach((image) => {
	if (image.complete) return;

	image.classList.add('is-loading');
	const clearLoadingState = () => image.classList.remove('is-loading');

	image.addEventListener('load', clearLoadingState, { once: true });
	image.addEventListener('error', clearLoadingState, { once: true });
});

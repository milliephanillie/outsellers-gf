function InitOutsellersForms(formId, currentPage) {
	if (formId !== 4) return;

	console.log("✅ InitOutsellersForms triggered via gform/post_render");

	const workerField = document.getElementById('input_4_11');
	const hourField   = document.getElementById('input_4_12');

	console.log("workerField:", workerField);
	console.log("hourField:", hourField);

	const spanBooking = document.querySelector('.otslr-html-wrapper span.booking');
	const spanLater   = document.querySelector('.otslr-html-wrapper span.later');
	const spanTotal   = document.querySelector('.otslr-html-wrapper span.total');

	if (!workerField || !hourField || !spanBooking || !spanLater || !spanTotal) {
		console.warn("Missing fields or span targets");
		return;
	}

	const updatePrices = () => {
		const workers = workerField.value;
		const hours   = hourField.value;

		if (!workers || !hours) return;

		fetch(`/wp-json/otslr/v1/get-pricing?workers=${encodeURIComponent(workers)}&hours=${encodeURIComponent(hours)}`)
			.then(res => res.json())
			.then(data => {
				if (data.due_now && data.due_later && data.total_price) {
					spanBooking.textContent = `$${data.due_now}`;
					spanLater.textContent   = `$${data.due_later}`;
					spanTotal.textContent   = `$${data.total_price}`;
				}

				if (data.redirect_url) {
					window.outsellersForms = window.outsellersForms || {};
					window.outsellersForms.redirect_url = data.redirect_url;
				}
			})
			.catch(err => console.error('Error fetching pricing:', err));
	};

	workerField.addEventListener('change', updatePrices);
	hourField.addEventListener('change', updatePrices);

	updatePrices();
}

document.addEventListener('DOMContentLoaded', function () {
	console.log("🚀 Outsellers JS loaded");

	document.addEventListener('gform/post_render', function (event) {
		const { formId, currentPage } = event.detail || {};
		InitOutsellersForms(formId, currentPage);
	});
});


const makeAlert = (msg = 'no message provided') => {
    alert(msg); 
}

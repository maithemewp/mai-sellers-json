(function() {
	// Get the input and output text fields.
	const inputField     = document.querySelector('#acf-maisj_encode_decode_input');
	const outputField    = document.querySelector('#acf-maisj_encode_decode_output');
	const toggleCheckbox = document.querySelector('#acf-maisj_encode_decode_toggle');
	const alphabet       = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	const encode = () => {
		// Convert the input string to a Base64 string using the custom alphabet.
		return btoa( inputField.value, alphabet ).replace( /=+$/g, '' );
	};

	const decode = () => {
		return atob( inputField.value, alphabet );
	};

	const updateOutputField = () => {
		outputField.value = toggleCheckbox.checked ? decode() : encode();
	};

	// Add an event listener to the input field to detect when the user types.
	inputField.addEventListener( 'input', () => {
		updateOutputField();
	});

	// Add a change event listener to the toggle checkbox to update the output value if the checkbox is toggled again.
	toggleCheckbox.addEventListener( 'change', () => {

		updateOutputField();
	});
})();

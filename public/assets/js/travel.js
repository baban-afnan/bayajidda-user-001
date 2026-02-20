document.addEventListener('DOMContentLoaded', function () {
    const fieldSelect = document.getElementById('service_field');
    const fieldDescription = document.getElementById('field-description');
    const fieldPriceDisplay = document.getElementById('field-price');
    const totalAmountDisplay = document.getElementById('total-amount');

    if (fieldSelect) {
        const passportWrapper = document.getElementById('passport_upload_wrapper');
        const passportInput = document.getElementById('passport_file');

        fieldSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const price = parseFloat(selected.dataset.price);
                const description = selected.dataset.description || '';
                const selectedText = selected.text || '';

                if (fieldDescription) fieldDescription.textContent = description;
                if (fieldPriceDisplay) fieldPriceDisplay.textContent = '₦' + new Intl.NumberFormat().format(price);
                if (totalAmountDisplay) totalAmountDisplay.textContent = '₦' + new Intl.NumberFormat().format(price);

                // Show passport upload for international travel
                if (selectedText.includes('Outside')) {
                    if (passportWrapper) passportWrapper.style.display = 'block';
                    if (passportInput) passportInput.required = true;
                } else {
                    if (passportWrapper) passportWrapper.style.display = 'none';
                    if (passportInput) passportInput.required = false;
                }
            } else {
                if (fieldDescription) fieldDescription.textContent = '';
                if (fieldPriceDisplay) fieldPriceDisplay.textContent = '₦0.00';
                if (totalAmountDisplay) totalAmountDisplay.textContent = '₦0.00';
                if (passportWrapper) passportWrapper.style.display = 'none';
            }
        });
    }

    // Trip Type Logic
    const tripTypeRadios = document.querySelectorAll('input[name="trip_type"]');
    const returnDateWrapper = document.getElementById('return_date_wrapper');
    const returnDateInput = document.getElementById('return_date');

    if (tripTypeRadios && returnDateWrapper) {
        tripTypeRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'round_trip') {
                    returnDateWrapper.style.display = 'block';
                    if (returnDateInput) returnDateInput.required = true;
                } else {
                    returnDateWrapper.style.display = 'none';
                    if (returnDateInput) returnDateInput.required = false;
                }
            });
        });
    }
});


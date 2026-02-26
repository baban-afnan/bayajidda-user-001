document.addEventListener('DOMContentLoaded', function () {
    const fieldSelect = document.getElementById('service_field');
    const fieldDescription = document.getElementById('field-description');
    const fieldPriceDisplay = document.getElementById('field-price');
    const totalAmountDisplay = document.getElementById('total-amount');

    if (fieldSelect) {
        fieldSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const price = parseFloat(selected.dataset.price);
                const description = selected.dataset.description || '';

                if (fieldDescription) fieldDescription.textContent = description;
                if (fieldPriceDisplay) fieldPriceDisplay.textContent = '₦' + new Intl.NumberFormat().format(price);
                if (totalAmountDisplay) totalAmountDisplay.textContent = '₦' + new Intl.NumberFormat().format(price);
            } else {
                if (fieldDescription) fieldDescription.textContent = '';
                if (fieldPriceDisplay) fieldPriceDisplay.textContent = '₦0.00';
                if (totalAmountDisplay) totalAmountDisplay.textContent = '₦0.00';
            }
        });
    }
});

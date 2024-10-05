function updateSummaryItem(identifier, price, currency) {
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    });
    const element = document.getElementById(identifier);
    if (element) {
        element.innerText = formatter.format(price);
    }
}
function updateSummary(pricing) {
    updateSummaryItem('subtotal', pricing.price + pricing.setup, pricing.currency);
    updateSummaryItem('fees', pricing.setup, pricing.currency);
    updateSummaryItem('taxes', pricing.tax, pricing.currency);
    updateSummaryItem('total', pricing.price + pricing.setup + pricing.tax, pricing.currency);
    updateSummaryItem('recurring', pricing.recurringPayment, pricing.currency);
    document.querySelector("#currency").value = pricing.currency;
}

document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll("#basket-billing-section input[type=radio]");
    checkboxes.forEach(function (checkbox) {
        if (checkbox.checked) {
            const pricing = JSON.parse(checkbox.dataset.pricing);
            updateSummary(pricing);
        }
        checkbox.addEventListener('change', function () {
            const pricing = JSON.parse(checkbox.dataset.pricing);
            updateSummary(pricing);
        });
    });
});

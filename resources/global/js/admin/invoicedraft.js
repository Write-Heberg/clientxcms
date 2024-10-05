import {HSOverlay} from "preline/preline.js";

const addItemBtn = document.getElementById('add-item-btn');
if (addItemBtn) {
    addItemBtn.addEventListener('click', function () {
        const selectedValue = document.getElementById('product').value;
        const parts = selectedValue.split('-');
        const related = parts[0];
        const relatedId = parts[1];
        addItemBtn.ariaDisabled = true;
        addItemBtn.setAttribute('disabled', 'disabled');
        fetch(addItemBtn.dataset.fetch + '?related_id=' + relatedId + '&related='  + related).then(response => response.text()).then(data => {

            const contentElement = document.getElementById('item-content');
            contentElement.innerHTML = data;
            HSOverlay.open(document.getElementById('btn-draftitem'));
            tryBillingSection();
        }).catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message)
        }).finally(() => {
            addItemBtn.ariaDisabled = false;
            addItemBtn.removeAttribute('disabled');
        })
    });
    tryBillingSection();

    function tryBillingSection() {
        const checkboxes = document.querySelectorAll(".basket-billing-section input[type=radio]");
        console.log(checkboxes);
        checkboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                const pricing = JSON.parse(checkbox.dataset.pricing);
                updateSummary(pricing, checkbox.form);
            }
            checkbox.addEventListener('change', function () {
                const pricing = JSON.parse(checkbox.dataset.pricing);
                updateSummary(pricing, checkbox.form);
            });
        });
    }

    function updateSummary(pricing, form) {
        const unitPrice = form.querySelector('input[name="unit_price"]');
        const unitSetup = form.querySelector('input[name="unit_setupfees"]');
        unitPrice.value = pricing.price;
        unitSetup.value = pricing.setup;
    }
}

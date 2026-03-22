/**
 * J2Commerce Currency Form JavaScript
 * Ensures currency value field maintains proper decimal precision
 */

document.addEventListener('DOMContentLoaded', function() {
    const currencyValueField = document.querySelector('input[name="jform[currency_value]"]');
    
    if (currencyValueField) {
        // Ensure the field shows proper decimal places when focused/blurred
        currencyValueField.addEventListener('blur', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value) && value > 0) {
                // Format to 8 decimal places
                this.value = value.toFixed(8);
            }
        });
        
        // Prevent scientific notation input
        currencyValueField.addEventListener('input', function() {
            // Remove any non-numeric characters except decimal point
            let value = this.value.replace(/[^0-9.]/g, '');
            
            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            this.value = value;
        });
        
        // Initialize with proper formatting if value exists
        if (currencyValueField.value) {
            const initialValue = parseFloat(currencyValueField.value);
            if (!isNaN(initialValue) && initialValue > 0) {
                currencyValueField.value = initialValue.toFixed(8);
            }
        }
    }
});
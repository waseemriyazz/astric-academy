import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Toggle password input visibility and update the eye icon.
 * @param {string} inputId - The ID of the password input element
 * @param {HTMLElement} button - The toggle button element
 */
window.togglePasswordVisibility = function (inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (!input || !icon) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
};
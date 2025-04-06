// Password validation script
document.addEventListener('DOMContentLoaded', function() {
    const accountForm = document.getElementById('accountform');
    const password = document.getElementById('password');
    const password2 = document.getElementById('password2');
    
    // Validate passwords match on form submission
    accountForm.addEventListener('submit', function(event) {
        if (password.value !== password2.value) {
            event.preventDefault();
            
            // Create error message if it doesn't exist
            let errorMessage = document.querySelector('.error-message');
            if (!errorMessage) {
                errorMessage = document.createElement('p');
                errorMessage.className = 'error-message';
                document.querySelector('.title').appendChild(errorMessage);
            }
            
            errorMessage.textContent = 'Passwords do not match';
            
            // Highlight password fields
            password.style.borderColor = '#dc3545';
            password2.style.borderColor = '#dc3545';
            
            // Scroll to top of form
            window.scrollTo(0, 0);
        }
    });
    
    // Reset error styling when typing
    [password, password2].forEach(input => {
        input.addEventListener('input', function() {
            if (password.value === password2.value) {
                password.style.borderColor = '#4BB543';
                password2.style.borderColor = '#4BB543';
                
                // Hide error message if it exists
                const errorMessage = document.querySelector('.error-message');
                if (errorMessage) {
                    errorMessage.textContent = '';
                }
            } else {
                password.style.borderColor = '';
                password2.style.borderColor = '';
            }
        });
    });
});
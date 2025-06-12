// ? Show/hide password functionality
document.querySelectorAll('.password-wrapper').forEach(wrapper => {
    const passwordInput = wrapper.querySelector('input[type="password"], input[type="text"]');
    const showPasswordIcon = wrapper.querySelector('.show-password');
    if (passwordInput && showPasswordIcon) {
        showPasswordIcon.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                showPasswordIcon.src = showPasswordIcon.src.includes('eye-close')
                    ? showPasswordIcon.src.replace('eye-close', 'eye-open')
                    : showPasswordIcon.src.replace('eye-close', 'eye-open');
            } else {
                passwordInput.type = 'password';
                showPasswordIcon.src = showPasswordIcon.src.includes('eye-open')
                    ? showPasswordIcon.src.replace('eye-open', 'eye-close')
                    : showPasswordIcon.src.replace('eye-open', 'eye-close');
            }
        });
    }
});
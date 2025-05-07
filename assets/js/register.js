const part1 = document.querySelector('.signin-part-1');
const part2 = document.querySelector('.signin-part-2');
const btn = document.querySelector('.next-back');
const signup = document.querySelector('.signup-btn');
const radioChoices = document.querySelectorAll('.radio-group .choice');
const one = document.querySelector(".one");
const checkmark = document.querySelector(".checkmark");
const inputsPart1 = part1.querySelectorAll('input[required]');
let page = 1;

// Function to toggle visibility based on the page
function updatePage() {
    if (page === 1) {
        part1.classList.remove('hidden');
        part2.classList.add('hidden');
        btn.innerHTML = 'Next';
        signup.classList.remove('active');
        signup.classList.add('hidden');
        checkmark.classList.add('hidden');
        one.classList.remove('hidden');
        validatePart1(); // Ensure button state is updated
    } else if (page === 2) {
        part1.classList.add('hidden');
        part2.classList.remove('hidden');
        btn.innerHTML = 'Back';
        signup.classList.add('active');
        signup.classList.remove('hidden');
        checkmark.classList.remove('hidden');
        one.classList.add('hidden');
    }
}

// Function to validate if all required inputs in part 1 are filled
function validatePart1() {
    let allFilled = true;
    inputsPart1.forEach(input => {
        if (!input.value.trim()) {
            allFilled = false;
        }
    });

    if (allFilled) {
        btn.disabled = false; // Enable the button
        btn.classList.remove('disable-btn'); // Remove the disable-btn class
        btn.classList.add('black-btn'); // Remove the disable-btn class

    } else {
        btn.disabled = true; // Disable the button
        btn.classList.add('disable-btn'); // Add the disable-btn class
        btn.classList.remove('black-btn'); // Remove the disable-btn clas
    }
}

// Add event listeners to inputs in part 1 to validate on input
inputsPart1.forEach(input => {
    input.addEventListener('input', validatePart1);
});

// Function to handle the selection of a radio button
function handleRadioSelection(event) {
    // Remove the "selected" class from all choices
    radioChoices.forEach(choice => choice.classList.remove('selected'));

    // Add the "selected" class to the parent div of the clicked radio button
    const selectedChoice = event.target.closest('.choice');
    if (selectedChoice) {
        selectedChoice.classList.add('selected');
    }
}

// Function to validate email format
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Basic email regex
    return emailRegex.test(email);
}

// Function to validate email input and show feedback
function validateEmailInput() {
    const emailInput = document.querySelector('input[name="email"]');
    const emailError = document.querySelector('.email-error'); 

    if (!validateEmail(emailInput.value.trim())) {
        emailError.textContent = 'Please enter a valid email address.';
        emailInput.classList.add('error'); 
        btn.disabled = true;
        btn.classList.add('disable-btn'); 
        btn.classList.remove('black-btn');
    } else {
        emailError.textContent = '';
        emailInput.classList.remove('error'); 
        btn.disabled = false;
        btn.classList.remove('disable-btn'); 
        btn.classList.add('black-btn');
    }
}

// Function to validate password format
function validatePassword(password) {
    const passwordRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return passwordRegex.test(password);
}

// Function to validate password input and show feedback
function validatePasswordInput() {
    const passwordInput = document.querySelector('input[name="password"]');
    const passwordError = document.querySelector('.password-error'); // Add a span for error messages

    if (!validatePassword(passwordInput.value.trim())) {
        passwordError.textContent = 'Password must be at least 8 characters long, include an uppercase letter, a number, and a special character.';
        passwordInput.classList.add('error'); // Add error styling
        signup.disabled = true;
        signup.classList.add('disable-btn');
    } else {
        signup.disabled = false;
        passwordError.textContent = '';
        passwordInput.classList.remove('error'); // Remove error styling
        signup.classList.remove('disable-btn');
        validatePart1(); // Revalidate the form
    }
}


// Initial page setup
updatePage();

// Event listener for the button click
btn.addEventListener('click', (e) => {
    e.preventDefault();
    if (page === 1) {
        page = 2;
    } else {
        page = 1;
    }
    updatePage();
});

// Add event listeners to all radio buttons
radioChoices.forEach(choice => {
    const radioInput = choice.querySelector('input[type="radio"]');
    if (radioInput) {
        radioInput.addEventListener('change', handleRadioSelection);
    }
});

// Add event listener to validate email on input
const emailInput = document.querySelector('input[name="email"]');
emailInput.addEventListener('input', validateEmailInput);

// Add event listener to validate password on input
const passwordInput = document.querySelector('input[name="password"]');
passwordInput.addEventListener('input', validatePasswordInput);
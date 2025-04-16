const part1 = document.querySelector('.signin-part-1');
const part2 = document.querySelector('.signin-part-2');
const btn = document.querySelector('.next-back');
const signup = document.querySelector('.signup-btn');
let page = 1;

// Function to toggle visibility based on the page
function updatePage() {
    if (page === 1) {
        part1.classList.remove('hidden');
        part2.classList.add('hidden');
        btn.innerHTML = 'Next';
        signup.classList.add('hidden'); // Hide the signup button on page 1
    } else if (page === 2) {
        part1.classList.add('hidden');
        part2.classList.remove('hidden');
        btn.innerHTML = 'Back';
        signup.classList.remove('hidden'); // Show the signup button on page 2
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
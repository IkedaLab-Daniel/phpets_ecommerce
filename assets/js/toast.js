console.log('toast.js loaded');
console.log(typeof showToast);
function showToast(message, type = "") {
    console.log('TOAST TRIGGERED: ', message, type)
    const toastContainer = document.getElementById("toast-container");
    if (!toastContainer) return;

    const toast = document.createElement("div");
    toast.className = "toast " + type;
    toast.textContent = message;
    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}


// ? Toaster stuff
document.addEventListener("DOMContentLoaded", () => {
    const toastData = document.getElementById("toast-data");

    if (toastData) {
        const message = toastData.getAttribute("data-message");
        const type = toastData.getAttribute("data-type");
        const redirect = toastData.getAttribute("data-redirect");

        if (message) {
            showToast(message, type);

            if (redirect) {
                setTimeout(() => {
                    window.location.href = redirect;
                }, 3000); 
            }
        }
        toastData.remove(); 
    }
});



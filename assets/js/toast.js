console.log('toast.js loaded');
console.log(typeof showToast);

function showToast(message, type = "", imgSrc = "") {
    console.log('TOAST TRIGGERED: ', message, type, imgSrc);
    const toastContainer = document.getElementById("toast-container");
    if (!toastContainer) return;

    const toast = document.createElement("div");
    toast.className = "toast " + type;
    // Add image if imgSrc is provided
    if (imgSrc) {
        const img = document.createElement("img");
        img.src = imgSrc;
        img.alt = "Toast Icon";
        img.className = "toast-img"; // Add a class for styling
        toast.appendChild(img);
        console.log('Hell Nah PNG')
    }

    // Add the message
    const text = document.createElement("span");
    text.textContent = message;
    toast.appendChild(text);

    toastContainer.appendChild(toast);

    // Play sound if the toast type is "error"
    if (type === "error") {
        const audio = new Audio("/phpets/assets/js/hellnah.mp3");
        audio.play();
        console.log('Hell Nah');
    }

    setTimeout(() => {
        toast.remove();
    }, 6000);
}

// ? Toaster stuff
document.addEventListener("DOMContentLoaded", () => {
    const toastData = document.getElementById("toast-data");

    if (toastData) {
        const message = toastData.getAttribute("data-message");
        const type = toastData.getAttribute("data-type");
        const redirect = toastData.getAttribute("data-redirect");
        const imgSrc = toastData.getAttribute("data-img"); // Get image source if provided

        if (message) {
            showToast(message, type, imgSrc);

            if (redirect) {
                setTimeout(() => {
                    window.location.href = redirect;
                }, 6000);
            }
        }
        toastData.remove();
    }
});
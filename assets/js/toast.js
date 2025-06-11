console.log('toast.js loaded');
console.log(typeof showToast);

function showToast(message, type = "", imgSrc = "") {
    console.log('TOAST TRIGGERED: ', message, type, imgSrc);
    const toastContainer = document.getElementById("toast-container");
    if (!toastContainer) return;

    const toast = document.createElement("div");
    toast.className = "toast " + type;

    if (imgSrc) {
        const img = document.createElement("img");
        img.src = imgSrc;
        img.alt = "Toast Icon";
        if (type === 'error'){
            img.className = "toast-img";
        } else{
            img.className = "toast-img-2";
        }
        
         
        toast.appendChild(img);
        console.log('Hell Nah PNG');
    }

    // Add the message
    const text = document.createElement("span");
    text.textContent = message;
    toast.appendChild(text);

    toastContainer.appendChild(toast);

    // Play sound if the toast type is "error" or "banned"
    if (type === "error") {
        const audio = new Audio("/phpets/assets/js/hellnah.mp3");
        audio.play();
        console.log('Hell Nah!');
    } else if (type === "banned") {
        const audio = new Audio("/phpets/assets/js/getout.mp3");
        audio.play();
        console.log('GET OUT!');
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
        const imgSrc = toastData.getAttribute("data-img");

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
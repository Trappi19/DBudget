onload = () => {
    document.getElementById("loading-gif").style.display = "none";

    // Save + Reload = Popup info
    if (localStorage.getItem("settings_saved") === "1") {
        localStorage.removeItem("settings_saved");
        new_toast(trans("settings.save_success"), "success");
    }

    const contactOverlay = document.getElementById("contact-overlay");
    let pressStartedOnBackdrop = false;
    contactOverlay.addEventListener("mousedown", (e) => {
        pressStartedOnBackdrop = e.target === contactOverlay;
    });
    contactOverlay.addEventListener("click", (e) => {
        if (pressStartedOnBackdrop && e.target === contactOverlay) {
            closeContactForm();
        }
    });

    const contactMessage = document.getElementById("contact-message");
    document.getElementById("contact-message-max").textContent = contactMessage.maxLength;
    contactMessage.addEventListener("input", contactUpdateCounter);
    contactUpdateCounter();
}

function saveSettings() {
    const username = document.getElementById("input-username").value.trim();
    const lang = document.getElementById("opt-langue").value;

    if (!username) {
        new_toast(trans("settings.username_empty"), "warn");
        return;
    }

    fetch("/api/v1/settings", {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username: username, lang: lang })
    })
    .then(res => res.json())
    .then(data => {
        if (Math.floor(data.code / 100) === 2) {
            localStorage.setItem("settings_saved", "1");
            location.reload();
        } else {
            new_toast(trans("settings.error_prefix") + data.message, "error");
        }
    })
    .catch(() => new_toast(trans("settings.error_network"), "error"));
}

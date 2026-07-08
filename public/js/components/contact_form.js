function openContactForm() {
    document.getElementById("contact-overlay").classList.add("contact-overlay--visible");
    contactSetState("fields");
    contactUpdateCounter();
}

function closeContactForm() {
    document.getElementById("contact-overlay").classList.remove("contact-overlay--visible");
}

function contactResetFields() {
    document.getElementById("contact-theme").selectedIndex = 0;
    document.getElementById("contact-subject").value = "";
    document.getElementById("contact-message").value = "";
    contactUpdateCounter();
}

function contactUpdateCounter() {
    const message = document.getElementById("contact-message");
    const counter = document.getElementById("contact-message-count");
    counter.textContent = message.value.length;
    counter.parentElement.classList.toggle("contact-form__counter--limit", message.value.length >= message.maxLength);
}

function sendContactForm() {
    const theme = document.getElementById("contact-theme").value.trim();
    const subject = document.getElementById("contact-subject").value.trim();
    const message = document.getElementById("contact-message").value.trim();

    if (!subject) {
        new_toast(trans("settings.contact_form.subject_empty"), "warn");
        return;
    }
    if (!message) {
        new_toast(trans("settings.contact_form.message_empty"), "warn");
        return;
    }

    contactSetState("loading");

    fetch("/api/v1/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ theme, subject, message })
    })
    .then(res => res.json())
    .then(data => {
        if (Math.floor(data.code / 100) === 2) {
            contactResetFields();
            contactShowFeedback("/assets/images/check.png", trans("settings.contact_form.success"));
        } else {
            contactShowFeedback("/assets/images/error.png", contactErrorMsg());
        }
    })
    .catch(() => {
        contactShowFeedback("/assets/images/error.png", contactErrorMsg());
    });
}

function contactSetState(state) {
    document.getElementById("contact-form-fields").style.display = state === "fields" ? "" : "none";
    document.getElementById("contact-loading").classList.toggle("contact-form__loading--visible", state === "loading");
    document.getElementById("contact-feedback").classList.toggle("contact-form__feedback--visible", state === "feedback");
}

function contactErrorMsg() {
    const base = trans("settings.contact_form.error_server");
    const mail = window.MAIL_CONTACT || '';
    return mail ? base + ' ' + mail : base;
}

function contactShowFeedback(iconSrc, text) {
    document.getElementById("contact-feedback-icon").src = iconSrc;
    document.getElementById("contact-feedback-text").textContent = text;
    contactSetState("feedback");
}
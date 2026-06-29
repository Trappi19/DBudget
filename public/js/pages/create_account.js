// Multi-step account creation: language → account info → email code.

// The blurred dashboard rendered behind this page (home.js) fires API calls
// that 401 for a not-yet-logged-in visitor, which would raise error toasts.
// Swallow any toast until the user starts interacting with the form — by then
// the background's 401s have already fired. Our own registration toasts work
// normally afterwards.
(function () {
    if (typeof window.new_toast !== "function") return;
    const realToast = window.new_toast;
    let backgroundPhase = true;
    window.new_toast = function (text, type) {
        if (backgroundPhase) return;
        return realToast(text, type);
    };
    document.addEventListener("focusin", () => { backgroundPhase = false; }, { once: true });
})();

let registeredEmail = "";

/**
 * Show one step of the registration form, hide the others (kept in the DOM
 * so typed values survive going back and forth).
 * @param {number} step
 * @param {"forward"|"back"} [direction] Slide direction for the animation.
 */
function goToStep(step, direction = "forward") {
    document.querySelectorAll(".auth-step").forEach((el) => {
        const isActive = Number(el.dataset.step) === step;
        el.classList.toggle("auth-step--active", isActive);
        el.classList.toggle("auth-step--back", isActive && direction === "back");
    });

    // No back arrow on step 1.
    const backBtn = document.getElementById("register-back");
    if (backBtn) backBtn.hidden = step === 1;
}

/**
 * Go back one step. No cleanup needed from step 3: resubmitting step 2 makes
 * ask_validation replace the pending code automatically.
 */
function goBack() {
    const active = document.querySelector(".auth-step--active");
    const step   = Number(active?.dataset.step);

    if (step === 2)      goToStep(1, "back");
    else if (step === 3) goToStep(2, "back");
}

/**
 * Route the form submission (button click or Enter key) to the action of the
 * step currently displayed.
 */
function submitActiveStep() {
    const active = document.querySelector(".auth-step--active");
    const step   = Number(active?.dataset.step);

    if (step === 1)      goToStep(2);
    else if (step === 2) submitRegistration();
    else if (step === 3) validateCode();
}

/**
 * Reload the page in the chosen language so every label (and server-side
 * message) is translated. The language also travels with the registration.
 * @param {string} value
 */
function changeLanguage(value) {
    window.location.href = "/app/create-account?lang=" + encodeURIComponent(value);
}

function _showError(el, message) {
    el.textContent = message;
    el.classList.add("error--visible");
}

function _hideError(el) {
    el.classList.remove("error--visible");
}

/**
 * Step 2 → ask the back for a verification code, then move to step 3.
 * Waits for the response so a duplicate email can be reported inline.
 */
function submitRegistration() {
    const email    = document.getElementById("register-email").value.trim();
    const username = document.getElementById("register-username").value.trim();
    const password = document.getElementById("register-password").value;
    const confirm  = document.getElementById("register-password-confirm").value;
    const lang     = document.getElementById("register-lang").value;
    const errorBox = document.getElementById("register-error");

    _hideError(errorBox);

    if (!email || !username || !password || !confirm) {
        _showError(errorBox, trans("auth.register.fill_fields"));
        return;
    }
    if (password !== confirm) {
        _showError(errorBox, trans("auth.register.password_mismatch"));
        return;
    }

    const submitBtn = document.getElementById("register-submit");
    submitBtn.disabled = true;

    fetch("/api/v1/account/ask_validation", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email, username, password, lang })
    })
    .then((res) => res.json())
    .then((data) => {
        if (Math.floor(data.code / 100) === 2) {
            registeredEmail = email;
            document.getElementById("register-email-display").textContent = email;
            _hideError(errorBox);
            goToStep(3);
        } else if (data.code === 409) {
            _showError(errorBox, trans("auth.register.email_used"));
        } else {
            new_toast(trans("auth.register.create_failed"), "error");
        }
    })
    .catch(() => {
        new_toast(trans("auth.register.create_failed"), "error");
    })
    .finally(() => {
        submitBtn.disabled = false;
    });
}

/**
 * Step 3 → submit the verification code. On success the user is logged in and
 * redirected home; otherwise the inline "wrong code" message is shown.
 */
function validateCode() {
    const code     = document.getElementById("register-code").value.trim();
    const errorBox = document.getElementById("code-error");

    _hideError(errorBox);

    if (!code) {
        _showError(errorBox, trans("auth.register.code_incorrect"));
        return;
    }

    const submitBtn = document.getElementById("code-submit");
    submitBtn.disabled = true;

    fetch("/api/v1/account/validation", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: registeredEmail, code })
    })
    .then((res) => res.json())
    .then((data) => {
        if (Math.floor(data.code / 100) === 2) {
            window.location.href = data.data?.redirect ?? "/app/home";
        } else {
            _showError(errorBox, trans("auth.register.code_incorrect"));
        }
    })
    .catch(() => {
        new_toast(trans("auth.register.network_error"), "error");
    })
    .finally(() => {
        submitBtn.disabled = false;
    });
}

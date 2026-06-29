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

// Show one step and hide the others (kept in the DOM to preserve input values).
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

// Go back one step (resubmitting step 2 replaces the pending code).
function goBack() {
    const active = document.querySelector(".auth-step--active");
    const step = Number(active?.dataset.step);

    if (step === 2)      goToStep(1, "back");
    else if (step === 3) goToStep(2, "back");
}

// Route submission (click or Enter) to the action of the active step.
function submitActiveStep() {
    const active = document.querySelector(".auth-step--active");
    const step = Number(active?.dataset.step);

    if (step === 1)      goToStep(2);
    else if (step === 2) submitRegistration();
    else if (step === 3) validateCode();
}

// Swap the UI language in place: no navigation, no step re-animation, just new text.
function changeLanguage(value) {
    const select = document.getElementById("register-lang");
    const previous = select ? select.value : null;

    fetch("/api/v1/lang", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ lang: value })
    })
    .then((res) => res.json())
    .then((data) => {
        if (Math.floor(data.code / 100) !== 2) throw new Error("lang switch failed");
        window.APP_LANG = data.data.translations;
        document.documentElement.lang = value;
        applyTranslations();
    })
    .catch(() => {
        if (select && previous !== null) select.value = previous;
        new_toast(trans("auth.register.network_error"), "error");
    });
}

// Refresh every static string on the page (all steps) after a language swap.
function applyTranslations() {
    document.querySelectorAll("[data-i18n]").forEach((el) => {
        el.textContent = trans(el.dataset.i18n);
    });
    document.querySelectorAll("[data-i18n-value]").forEach((el) => {
        el.value = trans(el.dataset.i18nValue);
    });
    document.querySelectorAll("[data-i18n-aria]").forEach((el) => {
        el.setAttribute("aria-label", trans(el.dataset.i18nAria));
    });
}

function _showError(el, message) {
    el.textContent = message;
    el.classList.add("error--visible");
}

function _hideError(el) {
    el.classList.remove("error--visible");
}

// Step 2 -> request a verification code, then move to step 3.
function submitRegistration() {
    const email = document.getElementById("register-email").value.trim();
    const username = document.getElementById("register-username").value.trim();
    const password = document.getElementById("register-password").value;
    const confirm = document.getElementById("register-password-confirm").value;
    const lang = document.getElementById("register-lang").value;
    const errorBox = document.getElementById("register-error");

    _hideError(errorBox);

    if (!email || !username || !password || !confirm) {
        _showError(errorBox, trans("auth.register.fill_fields"));
        return;
    }
    // Basic email shape check (the form uses novalidate, so nothing else guards this).
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        _showError(errorBox, trans("auth.register.invalid_email"));
        return;
    }
    if (password !== confirm) {
        _showError(errorBox, trans("auth.register.password_mismatch"));
        return;
    }

    const submitBtn = document.getElementById("register-submit");
    submitBtn.disabled = true;

    fetch("/api/v1/userAccount/ask_validation", {
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

// Step 3 -> submit the code. Success: login and redirect. Otherwise: inline error.
function validateCode() {
    const code = document.getElementById("register-code").value.trim();
    const errorBox = document.getElementById("code-error");

    _hideError(errorBox);

    if (!code) {
        _showError(errorBox, trans("auth.register.code_incorrect"));
        return;
    }

    const submitBtn = document.getElementById("code-submit");
    submitBtn.disabled = true;

    fetch("/api/v1/userAccount/validation", {
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

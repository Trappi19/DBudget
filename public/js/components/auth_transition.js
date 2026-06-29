// Fades the auth card out before leaving it (register link or login submit),
// so the transition feels continuous. Skipped under prefers-reduced-motion.
(function () {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduceMotion) return;

    const section = document.getElementById("section-core");
    if (!section) return;

    document.querySelectorAll("a.register-link").forEach((link) => {
        link.addEventListener("click", (event) => {
            const href = link.getAttribute("href");

            // Leave modifier-clicks (new tab/window) to the browser.
            if (!href || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            event.preventDefault();
            section.classList.add("auth-leaving");
            setTimeout(() => { window.location.href = href; }, 300);
        });
    });

    // Same fade on login submit; fires only once native validation passes.
    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", (event) => {
            event.preventDefault();
            section.classList.add("auth-leaving");
            setTimeout(() => { loginForm.submit(); }, 300);
        });
    }
})();

// Auth screen transitions (login <-> register).
//
// The switch is a SAME-DOCUMENT swap: we fetch the target auth page and replace
// only #section-core inside document.startViewTransition(), so the card morphs
// from one screen to the other. This is done in JS (rather than a cross-document
// @view-transition) because the browser was skipping the cross-document version
// too often — it depends on the arriving page painting in time, on the tab being
// visible, etc. A same-document transition we drive ourselves is reliable.
//
// Both auth pages load the same auth scripts/styles, so either #section-core can
// be hosted by either page. Disabled under reduced motion (falls back to a plain
// navigation), and it degrades to an instant swap if the browser skips the anim.
(function () {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // Wire the login form's leaving fade. Re-run after each swap because the form
    // node is replaced; the submit navigates to a page without this transition.
    function wireAuthSection() {
        const loginForm = document.getElementById("login-form");
        if (!loginForm || loginForm.dataset.wired) return;
        loginForm.dataset.wired = "1";
        loginForm.addEventListener("submit", (event) => {
            if (reduceMotion) return;
            event.preventDefault();
            document.getElementById("section-core").classList.add("auth-leaving");
            setTimeout(() => { loginForm.submit(); }, 300);
        });
    }

    // Fetch another auth page and morph its card in without a full reload.
    async function swapAuth(url, push) {
        if (reduceMotion || !document.startViewTransition) {
            window.location.href = url;
            return;
        }

        let doc;
        try {
            const resp = await fetch(url);
            if (!resp.ok) throw new Error("status " + resp.status);
            doc = new DOMParser().parseFromString(await resp.text(), "text/html");
        } catch (e) {
            window.location.href = url;   // network / server issue: real navigation
            return;
        }

        const newSection = doc.getElementById("section-core");
        if (!newSection) {                // e.g. the server redirected elsewhere
            window.location.href = url;
            return;
        }
        const imported = document.importNode(newSection, true);
        const newTitle = doc.title;
        const newPageName = doc.getElementById("page-name");
        const newPageText = newPageName ? newPageName.textContent : null;

        const transition = document.startViewTransition(() => {
            // The morph is the animation, so suppress the card entrance fade.
            document.documentElement.classList.add("auth-no-enter");
            document.getElementById("section-core").replaceWith(imported);
            document.title = newTitle;
            const pageName = document.getElementById("page-name");
            if (pageName && newPageText !== null) pageName.textContent = newPageText;
            if (push) history.pushState({ authSwap: true }, "", url);
            wireAuthSection();
        });
        transition.ready.catch(() => {});   // ignore "skipped" (hidden tab, etc.)
    }

    // Intercept switches between the two auth screens (event delegation so it
    // keeps working on the swapped-in links).
    document.addEventListener("click", (event) => {
        if (reduceMotion) return;
        const link = event.target.closest("a.register-link");
        if (!link) return;
        // Leave modifier clicks (open in new tab, etc.) to the browser.
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const url = link.getAttribute("href");
        if (!url) return;
        event.preventDefault();
        swapAuth(url, true);
    });

    // Back/forward through the swapped history: re-sync the card to the URL.
    window.addEventListener("popstate", () => {
        if (document.getElementById("section-core")) swapAuth(location.href, false);
    });

    wireAuthSection();
})();

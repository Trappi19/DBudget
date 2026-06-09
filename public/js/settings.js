onload = () => {
    document.getElementById("loading-gif").style.display = "none";
    loadLanguages();

    // Save + Reload = Popup info
    if (localStorage.getItem("settings_saved") === "1") {
        localStorage.removeItem("settings_saved");
        new_popup(t("settings.save_success"), "success");
    }
}

function loadLanguages() {
    fetch("/api/v1/settings", { method: "GET" })
        .then(res => res.json())
        .then(data => {
            if (Math.floor(data.code / 100) !== 2) return;

            const select = document.getElementById("opt-langue");
            select.innerHTML = "";
            data.data.languages.forEach(lang => {
                const option = document.createElement("option");
                option.value = lang.code;
                option.textContent = lang.label;
                if (lang.code === data.data.current) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        })
        .catch(() => {});
}

function saveSettings() {
    const username = document.getElementById("input-username").value.trim();
    const lang = document.getElementById("opt-langue").value;

    if (!username) {
        new_popup(t("settings.username_empty"), "warn");
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
            new_popup(t("settings.error_prefix") + data.message, "error");
        }
    })
    .catch(() => new_popup(t("settings.error_network"), "error"));
}
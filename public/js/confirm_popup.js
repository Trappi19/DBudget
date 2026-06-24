/**
 * @param {string} title
 * @param {string} message
 * @param {Function} onConfirm
 * @param {Function} onCancel
 */
function confirm_popup(title, message, onConfirm, onCancel = null) {

    const overlay = document.createElement("div");
    overlay.id = "confirm-popup-overlay";

    overlay.innerHTML = `
        <div id="confirm-popup">
            <p id="confirm-popup-title">${escapeHTML(title)}</p>
            <p id="confirm-popup-message">${message}</p>
            <div id="confirm-popup-buttons">
                <button id="confirm-popup-confirm" class="valide_button noselect">Confirmer</button>
                <button id="confirm-popup-cancel" class="valide_button noselect">Annuler</button>
            </div>
        </div>
    `;

    const btnConfirm = overlay.querySelector("#confirm-popup-confirm");
    const btnCancel = overlay.querySelector("#confirm-popup-cancel");

    btnConfirm.onclick = () => { close_confirm_popup(); if (onConfirm) onConfirm(); };
    btnCancel.onclick = () => { close_confirm_popup(); if (onCancel) onCancel(); };
    overlay.onclick = (e) => { if (e.target.id === "confirm-popup-overlay") { close_confirm_popup(); if (onCancel) onCancel(); } };

    document.body.appendChild(overlay);
}

function close_confirm_popup() {
    let overlay = document.getElementById("confirm-popup-overlay");
    if (!overlay) return;
    overlay.classList.add("closing");
    overlay.addEventListener("animationend", () => overlay.remove(), { once: true });
}
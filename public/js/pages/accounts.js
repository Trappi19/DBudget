const email = '<%=Session["email"]%>'
const datasheet = document.getElementById("datasheet");
const date = document.getElementById("date");
const amount = document.getElementById("amount");
const label = document.getElementById("label");
const create_account_button = document.getElementById("create-account");
const create_account_overlay = document.getElementById("create-account-overlay");
const create_account_panel = document.getElementById("create-account-panel");
const create_account_icon_input = document.getElementById("create-account-icon-input");
const create_account_icon_preview = document.getElementById("create-account-icon-preview");
const create_account_type = document.getElementById("create-account-type");
const total_sold = document.getElementById("total-sold");
let transfer_data = [null, null];
let create_account_icon_base64 = null;

create_account_type.addEventListener("change", update_create_account_icon_type);
create_account_icon_input.addEventListener("change", handle_create_account_icon_upload);
create_account_overlay.addEventListener("click", (event) => {
    if (event.target === create_account_overlay) close_create_account_panel();
});
document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && create_account_overlay.classList.contains("is-visible")) close_create_account_panel();
});

function f_onload() { onload(); }

function get_account_icon(account) {
    if (account.icon) {
        return `<span class="account-icon account-icon--image"><img src="${account.icon}" alt="icon"></span>`;
    }

    let type_class = account.type ? "account-icon--savings" : "account-icon--checking";
    return `<span class="account-icon ${type_class}"></span>`;
}

// Reposition selected cards on resize/zoom
window.addEventListener('resize', reposition_transfer_cards);

// Zoom doesn't always fire 'resize'; observe the table (changes on zoom, not on transform) to avoid a loop
window.addEventListener('DOMContentLoaded', () => {
    let target = document.querySelector(".responsive-table");
    if (target && window.ResizeObserver) {
        let observer = new ResizeObserver(() => reposition_transfer_cards());
        observer.observe(target);
    }
});

onload = () => {
    datasheet.innerHTML = "";
    date.valueAsDate = new Date();
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/v1/accounts", true);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            let accounts = JSON.parse(xhr.responseText).data;

            if (accounts.length == 0) {
                datasheet.innerHTML = `<li class="table-row">
                        <div class="col col-1" data-label="${trans('table.label')}"> --- </div>
                        <div class="col col-2" data-label="${trans('table.sold')}"> --- </div>
                        <div class="col col-3" data-label="${trans('table.type')}"> --- </div>

                        <div class="col col-4" data-label="${trans('table.actions')}"> </div>
                    </tr>`;
                new_toast(trans('accounts.no_account'), "info");
                document.getElementById("loading-gif").style.display = "none";
                return;
            }

            accounts.forEach(account => {
                datasheet.innerHTML += `
                    <li id="card-${account.id_account}" onclick="manage_account_transfer(${account.id_account})" class="table-row">
                        <div class="col col-1" data-label="${trans('table.label')}">${get_account_icon(account)}<span class="account-label">${account.label}</div>
                        <div class="col col-2" data-label="${trans('table.sold')}">${account.sold.toFixed(2)} € </div>
                        <div class="col col-3" data-label="${trans('table.type')}">${account.type ? trans('accounts.saving_account') : trans('accounts.checking_account')}</div>

                        <div class="col col-4" data-label="${trans('table.actions')}">
                            <img src="/assets/images/edit.png" alt="edit" class="card-button" onclick="edit_element(event, ${account.id_account}, this)">
                            <img src="/assets/images/trash.png" alt="delete" class="card-button" onclick="confirm_popup_delete_element(event, ${account.id_account}, '${account.label}')">
                        </div>
                    </tr>`;
            });

            total_sold.innerHTML = trans('accounts.total') + ": " + accounts.reduce((acc, account) => acc + account.sold, 0).toFixed(2) + " €";
        }
        else {
            new_toast("Error getting accounts", "error")
        }
        document.getElementById("loading-gif").style.display = "none";
    };
    xhr.send();
}

function manage_account_transfer(id) {
    if ((transfer_data[0] == id) || (transfer_data[1] == id)) {
        transfer_animation_off(id);
        if (transfer_data[0] == id) {
            transfer_data[0] = null;
        }
        else {
            transfer_data[1] = null;
        }
    }
    else if (transfer_data[0] == null) {
        transfer_animation_on(id, 0)
        transfer_data[0] = id;
    }
    else if (transfer_data[1] == null) {
        transfer_animation_on(id, 1)
        transfer_data[1] = id;
    }

    document.getElementById("transfer-field").disabled = ((transfer_data[0] == null) || (transfer_data[1] == null));
}

// animate: true on click (slide), false on zoom (instant, avoids jitter)
function transfer_animation_on(id, postion, animate = true) {
    let card = document.getElementById("card-" + id);

    let slot = document.getElementById(`selected-account-${postion}`);

    // Reset + set final width before measuring so the offset is correct
    card.style.transition = "none";
    card.style.transform = "";
    card.style.width = "";
    card.offsetWidth; // force reflow

    let card_position = card.getBoundingClientRect();
    let slot_position = slot.getBoundingClientRect();

    let x = slot_position.x - card_position.x;
    let y = slot_position.y - card_position.y;
    let cs = getComputedStyle(card);
    let padding = parseFloat(cs.paddingLeft) + parseFloat(cs.paddingRight);

    card.style.transition = animate ? "" : "none";
    card.style.width = (slot_position.width - padding) + "px";
    card.style.transform = `translate(${x}px, ${y}px)`;

    if (!animate) {
        card.offsetWidth;
        card.style.transition = "";
    }
}

// Reposition selected cards without animation (on zoom/resize)
function reposition_transfer_cards() {
    if (transfer_data[0] != null) {
        transfer_animation_on(transfer_data[0], 0, false);
    }
    if (transfer_data[1] != null) {
        transfer_animation_on(transfer_data[1], 1, false);
    }
}

// Reset card to its original position
function transfer_animation_off(id) {
    let card = document.getElementById("card-" + id);
    card.style.transform = "";
    card.style.width = "";
}

function process_transfer() {
    if (transfer_data[0] == null || transfer_data[1] == null || date.value == "" || amount.value == "") {
        new_toast(trans('accounts.fill_fields'), "warn");
    }
    else {
        label_val = label.value == "" ? get_account_shortname() : label.value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", `/api/v1/operations/transaction`, false);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onload = () => {
            if (Math.floor(xhr.status / 100) === 2) {
                new_toast(trans('accounts.transfer_success'), "success");
                undo_transfer();
            }
            else {
                new_toast(trans('accounts.transfer_error'), "error")
            }
        }
        xhr.send(JSON.stringify({ from: transfer_data[0], to: transfer_data[1], label: label_val, date: date.value, amount: amount.value }));
        f_onload();
    }
}

function get_account_shortname() {
    let from_shortname = document.getElementById("card-" + transfer_data[0]).querySelector(".account-label").innerHTML;
    let to_shortname = document.getElementById("card-" + transfer_data[1]).querySelector(".account-label").innerHTML;

    if (from_shortname.includes(" ")) { // get first letter of each word OR get first 3 letters
        from_shortname = from_shortname
            .split(" ")
            .map(word => word.charAt(0))
            .join("")
            .replace(/[^a-zA-Z]/g, "");
    } else { from_shortname = from_shortname.slice(0, 3); }

    if (to_shortname.includes(" ")) {
        to_shortname = to_shortname
            .split(" ")
            .map(word => word.charAt(0))
            .join("")
            .replace(/[^a-zA-Z]/g, "");
    } else { to_shortname = to_shortname.slice(0, 3); }

    return "Trans. " + from_shortname + " => " + to_shortname;
}

function undo_transfer() {
    if (transfer_data[0] != null) {
        transfer_animation_off(transfer_data[0]);
    }
    if (transfer_data[1] != null) {
        transfer_animation_off(transfer_data[1]);
    }
    transfer_data = [null, null];
    document.getElementById("transfer-field").disabled = true;
    label.value = "";
    amount.value = "";
}

function open_create_account_panel() {
    create_account_overlay.style.display = "flex";
    requestAnimationFrame(() => {
        create_account_overlay.classList.add("is-visible");
        create_account_panel.classList.add("is-visible");
    });
}

function close_create_account_panel() {
    create_account_overlay.classList.remove("is-visible");
    create_account_panel.classList.remove("is-visible");

    create_account_panel.addEventListener("transitionend", () => {
        create_account_overlay.style.display = "none";
    }, { once: true });

    reset_create_account_form();
}

function reset_create_account_form() {
    document.getElementById("create-account-label").value = "";
    document.getElementById("create-account-sold").value = "";
    create_account_type.value = "0";
    create_account_icon_input.value = "";
    create_account_icon_base64 = null;
    create_account_icon_preview.innerHTML = "";
    create_account_icon_preview.classList.remove("account-icon-preview--image");
    update_create_account_icon_type();
}

function update_create_account_icon_type() {
    create_account_icon_preview.classList.remove("account-icon-preview--checking", "account-icon-preview--savings");
    create_account_icon_preview.classList.add(create_account_type.value == "1" ? "account-icon-preview--savings" : "account-icon-preview--checking");
}

function handle_create_account_icon_upload() {
    const file = create_account_icon_input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
            const size = 128;
            const canvas = document.createElement("canvas");
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext("2d");

            const scale = Math.max(size / img.width, size / img.height);
            const w = img.width * scale;
            const h = img.height * scale;
            ctx.drawImage(img, (size - w) / 2, (size - h) / 2, w, h);

            create_account_icon_base64 = canvas.toDataURL("image/jpeg", 0.85);
            create_account_icon_preview.innerHTML = `<img src="${create_account_icon_base64}" alt="icon">`;
            create_account_icon_preview.classList.add("account-icon-preview--image");
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

function create_account() {
    const acc_label = document.getElementById("create-account-label");
    const acc_type = create_account_type;
    const acc_sold = document.getElementById("create-account-sold");

    if (acc_label.value == "" || acc_type.value == "") {
        new_toast(trans('accounts.fill_fields'), "warn");
        return;
    }

    if (acc_sold.value == "") {
        acc_sold.value = 0;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("POST", `/api/v1/accounts`, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            new_toast(trans('accounts.create_success'), "success");
            onload();
            close_create_account_panel();
        }
        else {
            new_toast(trans('accounts.create_error'), "error")
        }
    }
    xhr.send(JSON.stringify({ label: acc_label.value, type: acc_type.value, sold: acc_sold.value, icon: create_account_icon_base64 }));
}

function cancel_create_account() {
    close_create_account_panel();
}

function edit_element(event, id, element) {
    event.stopPropagation();
    card = element.parentNode.parentNode;
    card.classList.add("editing-row");

    card.onclick = "";
    card.innerHTML = `
        <input class="col col-1" data-label="${trans('table.label')}" value="${card.querySelector(".account-label").innerHTML}" />
        <input class="col col-2" data-label="${trans('table.sold')}" type="number" value="${card.children[1].innerHTML.slice(0, -3)}" />
        <select class="col col-3" data-label="${trans('table.type')}">
            <option value="0">${trans('accounts.checking_account')}</option>
            <option value="1" ${card.children[2].innerHTML == trans('accounts.saving_account') ? "selected" : ""}>${trans('accounts.saving_account')}</option>
        </select>
        <div class="col col-4" data-label="${trans('table.actions')}">
            <img src="/assets/images/confirm.png" alt="confirm" class="card-button" onclick='confirm_edit_element(this.parentNode.parentNode.children[0].value, this.parentNode.parentNode.children[1].value, this.parentNode.parentNode.children[2].value, ${id})'>
            <img src="/assets/images/cancel.png" alt="cancel" class="card-button" onclick="f_onload()">
        </div>`;

    setTimeout(() => {
        undo_transfer();
    }, 1);
}

function confirm_edit_element(label, sold, type, id) {
    if (label == "" || type == "" || sold == "") {
        new_toast(trans('accounts.fill_fields'), "warn");
    }
    else {
        var xhr = new XMLHttpRequest();
        xhr.open("PATCH", `/api/v1/accounts`, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onload = () => {
            if (Math.floor(xhr.status / 100) === 2) {
                new_toast(trans('accounts.update_success'), "success");
                onload();
            }
            else {
                new_toast(trans('accounts.update_error'), "error")
            }
        }
        xhr.send(JSON.stringify({ id, label, sold, type }));
    }
}

function confirm_popup_delete_element(event, id, label) {
    event.stopPropagation();
    confirm_popup(
        "Suppression d'un compte",
        `Êtes-vous sûr de vouloir supprimer le compte <strong>${label}</strong> ? Cette action est irréversible.`,
        () => { 
            confirm_popup(
                "Suppression d'un compte",
                `Êtes vous VRAIMENT sur ? Tout les transactions liées à <strong>${label}</strong> seront supprimées. Je veux dire, êtes vous VRAIMENT VRAIMENT sur ? Je ne pourrais rien faire si vous le regrettez après.`,
                () => { delete_element(id); },
                () => {}
            );
        },
        () => {}
    );
}

function delete_element(id) {
    var xhr = new XMLHttpRequest();
    xhr.open("DELETE", `/api/v1/accounts`, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            new_toast(trans('accounts.delete_success'), "success");
            onload();
        }
        else {
            new_toast(trans('accounts.delete_error'), "error")
        }
    }
    xhr.send(JSON.stringify({ id }));
}

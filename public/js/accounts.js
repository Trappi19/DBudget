const email = '<%=Session["email"]%>'
const datasheet = document.getElementById("datasheet");
const date = document.getElementById("date");
const amount = document.getElementById("amount");
const label = document.getElementById("label");
const create_account_field = document.getElementById("create-account-field");
const create_account_button = document.getElementById("create-account");
const total_sold = document.getElementById("total-sold");
let transfer_data = [null, null];

function f_onload() { onload(); }

function account_icon(account) {
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
                        <div class="col col-1" data-label="Label"> --- </div>
                        <div class="col col-2" data-label="Sold"> --- </div>
                        <div class="col col-3" data-label="Type"> --- </div>

                        <div class="col col-4" data-label="Actions"> </div>
                    </tr>`;
                new_popup("There is no account yet", "info");
                document.getElementById("loading-gif").style.display = "none";
                return;
            }

            accounts.forEach(account => {
                datasheet.innerHTML += `
                    <li id="card-${account.id_account}" onclick="manage_account_transfer(${account.id_account})" class="table-row">
                        <div class="col col-1" data-label="Label">${account_icon(account)}<span class="account-label">${account.label}</span></div>
                        <div class="col col-2" data-label="Sold">${account.sold.toFixed(2)} € </div>
                        <div class="col col-3" data-label="Type">${account.type ? "Savings account" : "Checking account"}</div>

                        <div class="col col-4" data-label="Actions">
                            <img src="/assets/images/edit.png" alt="edit" class="card-button" onclick="edit_element(event, ${account.id_account}, this)">
                            <img src="/assets/images/trash.png" alt="delete" class="card-button" onclick="confirm_popup_delete_element(event, ${account.id_account}, '${account.label}')">
                        </div>
                    </tr>`;
            });

            total_sold.innerHTML = "Total: " + accounts.reduce((acc, account) => acc + account.sold, 0).toFixed(2) + " €";
        }
        else {
            new_popup("Error getting accounts", "error")
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

    if (!animate) {
        card.style.transition = "none";
    }

    // Reset + set final width before measuring so the offset is correct
    card.style.transform = "";
    card.style.width = "90%";
    card.offsetWidth; // force reflow

    let slot_position = document.getElementById(`selected-account-${postion}`).getBoundingClientRect();
    let card_position = card.getBoundingClientRect();

    let x = slot_position.x - card_position.x;
    let y = slot_position.y - card_position.y;

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
        new_popup("Please fill all fields", "warn");
    }
    else {
        label_val = label.value == "" ? get_account_shortname() : label.value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", `/api/v1/operations/transaction`, false);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onload = () => {
            if (Math.floor(xhr.status / 100) === 2) {
                new_popup("Transaction process", "success");
                undo_transfer();
            }
            else {
                new_popup("Error process transaction", "error")
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

function create_account() {
    if (create_account_field.style.display != "block") {
        create_account_field.style.display = "block";
        create_account_button.style.display = "none";
    }
    else {
        const acc_label = document.getElementById("create-account-label");
        const acc_type = document.getElementById("create-account-type");
        const acc_sold = document.getElementById("create-account-sold");

        if (acc_label.value == "" || acc_type.value == "") {
            new_popup("Please fill all fields", "warn");
        }
        else {
            if (acc_sold.value == "") {
                acc_sold.value = 0;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", `/api/v1/accounts`, true);
            xhr.setRequestHeader("Content-Type", "application/json");
            xhr.onload = () => {
                if (Math.floor(xhr.status / 100) === 2) {
                    new_popup("Account created", "success");
                    acc_label.value = "";
                    acc_sold.value = "";
                    onload();
                    create_account_field.style.display = "none";
                    create_account_button.style.display = "";
                }
                else {
                    new_popup("Error creating account", "error")
                }
            }
            xhr.send(JSON.stringify({ label: acc_label.value, type: acc_type.value, sold: acc_sold.value }));
        }
    }
}

function cancel_create_account() {
    create_account_field.style.display = "none";
    create_account_button.style.display = "";
}

function edit_element(event, id, element) {
    event.stopPropagation();
    card = element.parentNode.parentNode;
    card.classList.add("editing-row");

    card.onclick = "";
    card.innerHTML = `
        <input class="col col-1" data-label="Label" value="${card.querySelector(".account-label").innerHTML}" />
        <input class="col col-2" data-label="Sold" type="number" value="${card.children[1].innerHTML.slice(0, -3)}" />
        <select class="col col-3" data-label="Type">
            <option value="0">Checking account</option>
            <option value="1" ${card.children[2].innerHTML == "Savings account" ? "selected" : ""}>Savings account</option>
        </select>
        <div class="col col-4" data-label="Actions">
            <img src="/assets/images/confirm.png" alt="confirm" class="card-button" onclick='confirm_edit_element(this.parentNode.parentNode.children[0].value, this.parentNode.parentNode.children[1].value, this.parentNode.parentNode.children[2].value, ${id})'>
            <img src="/assets/images/cancel.png" alt="cancel" class="card-button" onclick="f_onload()">
        </div>`;

    setTimeout(() => {
        undo_transfer();
    }, 1);
}

function confirm_edit_element(label, sold, type, id) {
    if (label == "" || type == "" || sold == "") {
        new_popup("Please fill all fields", "warn");
    }
    else {
        var xhr = new XMLHttpRequest();
        xhr.open("PATCH", `/api/v1/accounts`, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onload = () => {
            if (Math.floor(xhr.status / 100) === 2) {
                new_popup("Account updated", "success");
                onload();
            }
            else {
                new_popup("Error updating account", "error")
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
            new_popup("Account deleted", "success");
            onload();
        }
        else {
            new_popup("Error deleting account", "error")
        }
    }
    xhr.send(JSON.stringify({ id }));
}

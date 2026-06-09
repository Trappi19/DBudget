const email = '<%=Session["email"]%>'

const datasheet = document.getElementById("datasheet");
const date_to_search = document.getElementById("date-to-search");
const account_list = document.getElementById("selected-account");
const select_category = document.getElementById("category");

const label_field = document.getElementById("label");
const amount_field = document.getElementById("amount");
const category_field = document.getElementById("category");
const start_field = document.getElementById("event_start");
const end_field = document.getElementById("event_end");
const frequency_field = document.getElementById("frequency");

let accounts = [];
let operation_type_list = [];

onload = () => {
    set_operation_type_list();
    fill_account_list();
    date_to_search.valueAsDate = new Date();

    document.getElementById("loading-gif").style.display = "none";
}

// Operation type list

function set_operation_type_list() {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/v1/operations/types", false);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            operation_type_list = JSON.parse(xhr.responseText).data;
        }
        else {
            new_popup("Error getting operation type list", "error");
        }
    };
    xhr.send();
}

function set_select_category() {
    // Get the selected account type by using let accounts
    select_category.innerHTML = "";

    accounts.forEach(account => {
        if (account.id_account == account_list.value) {
            operation_type_list.forEach(operation_type => {
                if (operation_type.account_type == account.type) {
                    select_category.innerHTML += `<option value="${operation_type.id}">${translate_category(operation_type.title)}</option>`;
                }
            });
        }
    });

    operation_type_list.forEach(operation_type => {
        if (operation_type.account_type == -1) {
            select_category.innerHTML += `<option value="${operation_type.id}">${translate_category(operation_type.title)}</option>`;
        }
    });
}

function confirm_popup_delete_element(event_id) {
    const event = events.find(e => e.id_regular_event == event_id);
    confirm_popup(
        "Supprimer un évènement",
        `Êtes-vous sûr de vouloir supprimer l'évènement ${bold(event.label)} ? Cette action est irréversible.`,
        () => { delete_element(event_id); },
        () => {}
    );
}

function delete_element(event_id) {
    var xhr = new XMLHttpRequest();
    xhr.open("DELETE", `/api/v1/events`, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            update_datasheet();
            new_popup(trans('events.delete_success'), "success");
        }
        else {
            new_popup(trans('events.delete_error'), "error");
        }
    }
    xhr.send(JSON.stringify({ id: event_id }));
}

function update_datasheet() {
    date = date_to_search.value;
    datasheet.innerHTML = "";

    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/v1/events?accounts=" + JSON.stringify(accounts.map(account => account.id_account)) + "&date=" + date, true);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            events = JSON.parse(xhr.responseText).data;
            nb_events = events.length;

            if (nb_events == 0) {
                datasheet.innerHTML += `<li class="table-row">
                    <div class="col col-1" data-label="${trans('table.label')}"> ${trans('events.no_event')} </div>
                    <div class="col col-2" data-label="${trans('table.amount')}"> --- </div>
                    <div class="col col-3" data-label="${trans('table.account')}"> --- </div>
                    <div class="col col-4" data-label="${trans('table.start')}"> --- </div>
                    <div class="col col-5" data-label="${trans('table.end')}"> --- </div>
                    <div class="col col-6" data-label="${trans('table.frequency')}"> --- </div>
                    <div class="col col-7" data-label="${trans('table.category')}"> --- </div>
                    <div class="col col-8" data-label="${trans('table.actions')}"> --- </div>
                </li>`;

                new_popup(trans('events.no_event'), "info");
            }
            else {
                // trier events par label
                events.sort(function (a, b) {
                    if (a.label < b.label) {
                        return -1;
                    }
                    if (a.label > b.label) {
                        return 1;
                    }
                    return 0;
                });

                for (let i = 0; i < nb_events; i++) {
                    datasheet.innerHTML += `<li class="table-row">
                        <div class="col col-1" data-label="${trans('table.label')}"> ${events[i].label} </div>
                        <div class="col col-2" data-label="${trans('table.amount')}"> ${events[i].amount.toFixed(2)} € </div>
                        <div class="col col-3" data-label="${trans('table.account')}"> ${accounts.find(account => account.id_account === events[i].id_account).label} </div>
                        <div class="col col-4" data-label="${trans('table.start')}"> ${formatDate(events[i].start)} </div>
                        <div class="col col-5" data-label="${trans('table.end')}"> ${formatDate(events[i].end)} </div>
                        <div class="col col-6" data-label="${trans('table.frequency')}"> ${events[i].frequency_type == 0 ? trans('events.every_day') : events[i].frequency_type == 1 ? trans('events.every_week') : events[i].frequency_type == 2 ? trans('events.every_month') : trans('events.every_year')} </div>
                        <div class="col col-7" data-label="${trans('table.category')}"> ${translate_category(operation_type_list[events[i].category].title)} </div>
                        <div class="col col-8" data-label="${trans('table.actions')}"> --- </div>
                    </li>`;

                    if (events[i].amount > 0) {
                        datasheet.children[i].children[1].style.color = "green";
                    }
                    else {
                        datasheet.children[i].children[1].style.color = "red";
                    }

                    datasheet.children[i].children[7].innerHTML = `<img src="/assets/images/edit.png" alt="edit" class="card-button" onclick="edit_element(${events[i].id_regular_event},this)">
                    <img src="/assets/images/trash.png" alt="delete" class="card-button" onclick="confirm_popup_delete_element(${events[i].id_regular_event})">`;
                }
            }
        }
        else {
            new_popup("Error getting events", "error");
        }
    }
    xhr.send();
}

function fill_account_list() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/v1/accounts", true);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            const response = JSON.parse(xhr.responseText);
            accounts = response.data;

            if (accounts.length == 0) {
                new_popup(trans('events.no_account'), "info");
                document.getElementById("event-form").disabled = true;
                return;
            }

            accounts.forEach(account => {
                account_list.innerHTML += `<option value="${account.id_account}">${account.label}</option>`;
            });

            update_datasheet();
        }
        else {
            new_popup("Error getting accounts", "error");
        }
    };
    xhr.send();
}

function create_event() {
    label = label_field.value;
    amount = amount_field.value;
    category = category_field.value;
    start = start_field.value;
    end = end_field.value;
    frequency = frequency_field.value;
    account = account_list.value;

    if (label.length > 50) {
        label = label.substring(0, 47) + "...";
    }

    if (label == "" || amount == "" || category == "" || start == "" || end == "" || frequency == "" || account == 0) {
        new_popup(trans('events.fill_fields'), "warn");
    }
    else {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "/api/v1/events", true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onload = () => {
            if (Math.floor(xhr.status / 100) === 2) {
                update_datasheet();

                label_field.value = "";
                amount_field.value = "";
                category_field.value = 1;
                start_field.value = "";
                end_field.value = "";
                frequency_field.value = 0;

                new_popup(trans('events.create_success'), "success");
            }
            else {
                new_popup(trans('events.create_error'), "error");
            }
        };
        xhr.send(JSON.stringify({ id_account: account_list.value, label, amount, category, start, end, frequency }));
    }
}

function edit_element(id, element) {
    let card = element.parentNode.parentNode;
    card.classList.add("editing-row");

    let start = parseFrenchDate(card.children[3].innerHTML);
    let end = parseFrenchDate(card.children[4].innerHTML);
    start.setDate(start.getDate() + 1);
    end.setDate(end.getDate() + 1);

    let frequency = card.children[5].innerHTML.trim() == trans('events.every_day') ? 0 : card.children[5].innerHTML.trim() == trans('events.every_week') ? 1 : card.children[5].innerHTML.trim() == trans('events.every_month') ? 2 : 3;

    let category = 0;
    operation_type_list.forEach(operation_type => {
        if (" " + translate_category(operation_type.title) + " " == card.children[6].innerHTML.replace(/&amp;/g, "&")) {
            category = operation_type.id;
        }
    });

    card.onclick = "";
    card.innerHTML = `
        <input class="col col-1" data-label="${trans('table.label')}" value="${card.children[0].innerHTML.slice(1, -1)}" />
        <input class="col col-2" data-label="${trans('table.amount')}" type="number" value="${card.children[1].innerHTML.slice(1, -3)}" />
        <input class="col col-3" data-label="${trans('table.account')}" disabled/ value="${card.children[2].innerHTML}">
        <input class="col col-4" data-label="${trans('table.start')}" type="date" />
        <input class="col col-5" data-label="${trans('table.end')}" type="date" />

        <select class="col col-6" data-label="${trans('table.frequency')}">
            <option value="3">${trans('events.every_year')}</option>
            <option value="2">${trans('events.every_month')}</option>
            <option value="1">${trans('events.every_week')}</option>
            <option value="0">${trans('events.every_day')}</option>
        </select>

        <select class="col col-7" data-label="${trans('table.category')}" id="category_edit">
            ${set_select_category_for_edit()}
        </select>

        <div class="col col-8" data-label="${trans('table.actions')}">
            <img src="/assets/images/confirm.png" alt="confirm" class="card-button" onclick='confirm_edit_element(this.parentNode.parentNode.children[0].value, this.parentNode.parentNode.children[1].value, this.parentNode.parentNode.children[3].value, this.parentNode.parentNode.children[4].value, this.parentNode.parentNode.children[5].value, this.parentNode.parentNode.children[6].value, ${id},this)'>
            <img src="/assets/images/cancel.png" alt="cancel" class="card-button" onclick="update_datasheet()">
        </div>`;

    card.children[3].valueAsDate = start;
    card.children[4].valueAsDate = end;
    card.children[5].value = frequency;
    card.children[6].value = category;
}

function parseFrenchDate(dateText) {
    const trimmed = dateText.trim();
    const parts = trimmed.split("/");
    if (parts.length !== 3) {
        return new Date(trimmed);
    }

    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const year = parseInt(parts[2], 10);
    return new Date(year, month, day);
}

function set_select_category_for_edit() {
    let temp;
    operation_type_list.forEach(operation_type => {
        temp += `<option value="${operation_type.id}">${translate_category(operation_type.title)}</option>`;
    })
    return temp;
}

function confirm_edit_element(label, amount, start, end, frequency, category, id, element) {
    if (label.length > 50) {
        label = label.substring(0, 47) + "...";
    }

    if (label == "" || amount == "" || start == "" || end == "" || frequency == "" || category == "") {
        new_popup(trans('events.fill_fields'), "warn");
    }
    else {
        element.parentNode.innerHTML = `<img src="/assets/images/load.gif" alt="load" class="card-button">`;
        var xhr = new XMLHttpRequest();
        xhr.open("PATCH", `/api/v1/events`, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.onload = () => {
            if (Math.floor(xhr.status / 100) === 2) {
                new_popup(trans('events.update_success'), "success");
                fill_account_list();
            }
            else {
                new_popup(trans('events.update_error'), "error")
            }
        }
        xhr.send(JSON.stringify({ id, label, amount, start, end, frequency, category }));
    }
}
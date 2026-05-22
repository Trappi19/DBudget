const email = '<%=Session["email"]%>'

const account_list = document.getElementById("selected-checking-account");
const selected_month = document.getElementById("selected-month");
const datasheet = document.getElementById("datasheet");

const total_outcome = document.getElementById("total-outcome");
const total_income = document.getElementById("total-income");
const total_balance = document.getElementById("total-balance");

let selected_account;
let operation_type_list = [];
let accounts = [];
let operations = [];

onload = () => {
    fill_account_lists();
    set_operation_type_list();
    selected_month.valueAsDate = new Date();
    account_list.addEventListener("change", update_datasheet);
    selected_month.addEventListener("change", update_datasheet);

    // When scrolling, notes need to follow
    window.addEventListener("scroll", () => {
        document.getElementById("scollable").style.transform = `translateY(${window.scrollY}px)`;
    });

    document.getElementById("loading-gif").style.display = "none";
}

function update_brief() {
    let sum_positive_operations = operations.reduce((acc, operation) => acc + (operation.amount > 0 ? operation.amount : 0), 0);
    let sum_negative_operations = operations.reduce((acc, operation) => acc + (operation.amount < 0 ? operation.amount : 0), 0);
    let sum_operations = sum_positive_operations + sum_negative_operations;

    total_outcome.innerHTML = sum_negative_operations.toFixed(2) + " €";
    total_income.innerHTML = sum_positive_operations.toFixed(2) + " €";
    total_balance.innerHTML = sum_operations.toFixed(2) + " €";

    if (sum_operations > 0) {
        total_balance.style.color = "green";
    }
    else if (sum_operations < 0) {
        total_balance.style.color = "red";
    }
    else {
        total_balance.style.color = "black";
    }
}

function fill_account_lists() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/get/accounts", true);
    xhr.onload = () => {
        if (xhr.status == 200) {
            accounts = JSON.parse(xhr.responseText);

            if (accounts.length == 0) {
                new_popup("There is no account yet", "info");
                document.getElementsByClassName("analytics-form")[0].disabled = true;
            }
            else {
                accounts.forEach(account => {
                    account_list.innerHTML += `<option value="${account.id_account}">${account.label}</option>`;
                });
            }
        }
        else {
            new_popup("Error getting accounts", "error");
        }
    };
    xhr.send();
}

function set_operation_type_list() {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/get/operation-types?type=0", false);
    xhr.onload = () => {
        if (xhr.status == 200) {
            operation_type_list = JSON.parse(xhr.responseText);
        }
        else {
            new_popup("Error getting operation type list", "error");
        }
    };
    xhr.send();
}

function update_datasheet() {
    if (account_list.value == 0) {
        let tmp_html = "";
        for ($i = 0; $i < 14; $i++) {
            tmp_html += `
            <li class="table-row">
                <div class="col col-1" data-label="Date"> --- </div>
                <div class="col col-2" data-label="Label"> --- </div>
                <div class="col col-3" data-label="Amount"> --- </div>
                <div class="col col-4" data-label="Category"> --- </div>
                <div class="col col-5" data-label="Actions"></div>
            </li>`;
        }
        datasheet.innerHTML = tmp_html;
        selected_month.disabled = true;
        additional_operation.disabled = true;

        new_popup("Please select an account", "warn");
        return;
    }
    else {
        selected_month.disabled = false;
        selected_account = account_list.value;

        let start_str = selected_month.value + "-01";
        let end = new Date(start_str);
        end.setMonth(end.getMonth() + 1);
        end.setDate(end.getDate() - 1);
        end_str = formatDateToString(end);

        datasheet.innerHTML = "";

        let xhr = new XMLHttpRequest();
        xhr.open("GET", `/api/get/operations-account?id_account=${account_list.value}&start=${start_str}&end=${end_str}`, false);
        xhr.onload = () => {
            if (xhr.status == 200) {
                operations = JSON.parse(xhr.responseText);
                nb_operations = operations.length;

                if (nb_operations == 0) {
                    new_popup("There is no operation at this date", "info");
                    return;
                }

                for (let i = 0; i < nb_operations; i++) {
                    datasheet.innerHTML += `
                    <li class="table-row" id_operation="${operations[i].id_operation}">
                        <div class="col col-1" data-label="Date"> ${new Date(operations[i].date).toLocaleDateString("fr-FR")} </div>
                        <div class="col col-2" data-label="Label"> ${operations[i].label} </div>
                        <div class="col col-3" data-label="Amount"> ${(operations[i].amount > 0 ? "+" : "") + operations[i].amount.toFixed(2)} € </div>
                        <div class="col col-4" data-label="Category"> ${operation_type_list[operations[i].category].title} </div>
                        <div class="col col-5" data-label="Actions">
                            <img src="/assets/images/trash.png" alt="delete" class="card-button"">
                        </div>
                    </li>`;

                    if (operations[i].amount > 0) {
                        datasheet.children[i].children[2].style.color = "green";
                    }
                    else {
                        datasheet.children[i].children[2].style.color = "black";
                    }
                }

                // Events
                Array.from(datasheet.children).forEach(element => {
                    element.addEventListener("click", (e) => {
                        validate_operation(element);
                    });
                    element.children[4].addEventListener("click", (e) => {
                        e.stopPropagation();
                        delete_list(element);
                    });
                }, this);
            }
            else {
                new_popup("Error getting operations code #1", "error")
            }
        }
        xhr.send();
        update_brief();
    }
}

function validate_operation(self) {
    if (self.classList.contains("selected") || self.classList.contains("to-delete")) {
        self.classList.remove("selected");
        self.classList.remove("to-delete");
    }
    else {
        self.classList.add("selected");
    }
}

function delete_list(self) {
    if (self.classList.contains("to-delete")) {
        self.classList.remove("selected");
        self.classList.remove("to-delete");
    }
    else {
        self.classList.add("to-delete");
    }
}

function confirm_delete() {
    let selected = Array.from(datasheet.getElementsByClassName("to-delete"));
    if (selected.length == 0) {
        new_popup("No operation selected", "warn");
        return;
    }

    if (confirm("Are you sure you want to delete these operations?") == true) {
        selected.forEach(element => {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", `/api/delete/operation?id=${element.getAttribute("id_operation")}`, true);
            xhr.onload = () => {
                if (xhr.status == 200) {
                    element.remove();
                    operations = operations.filter(operation => operation.id_operation != element.getAttribute("id_operation"));
                    update_brief();
                    new_popup("Operation deleted", "success");
                }
                else {
                    new_popup("Error deleting operation", "error");
                }
            }
            xhr.send();
        });
    }
}

function open_new_operation_tab() {
    let note = document.getElementById("notes").value;
    note = note.replace(/(?:\r\n|\r|\n)/g, '\\n');
    console.log(note);

    window.open(`/app/operations?note=${note}`);
}
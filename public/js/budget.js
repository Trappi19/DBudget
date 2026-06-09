const email = '<%=Session["email"]%>'

const account_list = document.getElementById("selected-checking-account")

const selected_month = document.getElementById("selected-month");
const pie_chart_canvas = document.getElementById('pie-chart-canvas');
const bar_chart_canvas = document.getElementById('bar-chart-canvas');
const checking_account_info = document.getElementById("checking-account-info");
const budgetAccountDivs = document.getElementsByClassName("budget-account-div");

const account_expected_savings = document.getElementById("account-expected-savings");
const account_additional_expenditure = document.getElementsByClassName("account-additional-expenditure");
const additional_expenditure_fieldset = document.getElementById("additional-expenditure-fieldset");
const logarithmic_box = document.getElementById("logarithmic-axis");

let chart_labels = [];
let chart_colors = [];
let budget_pie_chart;
let budget_bar_chart;
let accounts = [];
let operation_type_list = [];
let global_operations = [];

window.addEventListener('resize', () => {
    budget_pie_chart.resize();
    budget_bar_chart.resize();
});

onload = () => {
    fill_account_lists();
    set_operation_type_list();

    budget_pie_chart = new Chart(pie_chart_canvas,
        {
            type: 'pie',
            options: {
                animation: {
                    animation: true,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (value) {
                                return ` ${value.parsed.toFixed(2)} € (${(value.parsed / budget_pie_chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0) * 100).toFixed(0)}%)`;
                            }
                        },
                    },
                }
            },
            data: {
                labels: chart_labels,
                datasets: [
                    {
                        data: [20, 0, 13, 10, 0, 0, 0, 10, 0, 0],
                        backgroundColor: chart_colors,
                        hoverOffset: 4
                    }
                ]
            }
        }
    );
    budget_bar_chart = new Chart(bar_chart_canvas,
        {
            type: 'bar',
            options: {
                animation: {
                    animation: true,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function (value) {
                                return ` ${value.parsed.toFixed(2)} €`;
                            }
                        },
                    },
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function (value) {
                                return value + " €";
                            }
                        }
                    }
                }

            },
            data: {
                labels: chart_labels,
                datasets: [
                    {
                        data: [20, 18, 13, 10, 10, 7, 7, 6, 4, 1],
                        backgroundColor: chart_colors,
                        hoverOffset: 4
                    }
                ]
            }
        }
    );

    if (window.innerWidth < 767) {
        budget_pie_chart.options.plugins.legend.display = false;
        budget_pie_chart.resize(250, 250);
        budget_bar_chart.options.plugins.legend.display = false;
        budget_bar_chart.resize(250, 250);
    }

    selected_month.valueAsDate = new Date();
    account_list.addEventListener("change", update_global_operation);
    selected_month.addEventListener("change", update_global_operation);
    account_expected_savings.addEventListener("change", update_charts);
    additional_expenditure_fieldset.addEventListener("change", update_charts);

    logarithmic_box.addEventListener("change", () => {
        budget_bar_chart.options.scales.y.type = logarithmic_box.checked ? 'logarithmic' : 'linear';
        budget_bar_chart.update();
    });

    document.getElementById("loading-gif").style.display = "none";
}

function set_operation_type_list() {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/v1/operations/types", false);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            operation_type_list = JSON.parse(xhr.responseText).data;

            chart_labels = [trans('home.chart_remains')];
            chart_colors = ["#36a2eb"];
            for (let i = 0; i < 9; i++) {
                chart_labels[i + 1] = translate_category(operation_type_list[i].title);
                chart_colors[i + 1] = operation_type_list[i].chart_color;
            }
        }
        else {
            new_popup("Error getting operation type list", "error");
        }
    };
    xhr.send();
}

function fill_account_lists() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/v1/accounts", true);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            accounts = xhr.responseText;
            accounts_list = JSON.parse(xhr.responseText).data;

            checking_accounts_list = accounts_list.filter(account => account.type == 0);
            savings_accounts_list = accounts_list.filter(account => account.type == 1);

            if (checking_accounts_list.length == 0) {
                new_popup(trans('budget.no_checking_account'), "info");
                document.getElementsByClassName("analytics-form")[0].disabled = true;
            }
            else {
                checking_accounts_list.forEach(account => {
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

function update_global_operation() {
    let start = selected_month.value + "-01";
    let end = new Date(start);
    end.setMonth(end.getMonth() + 1);
    end.setDate(end.getDate() - 1);
    end = formatDateToString(end);

    if (account_list.value > 0) {
        let xhr = new XMLHttpRequest();
        xhr.open("GET", `/api/v1/accounts/operations?id_account=${account_list.value}&start=${start}&end=${end}`, true);
        xhr.onload = () => {
            if (Math.floor(xhr.status / 100) === 2) {
                global_operations = JSON.parse(xhr.responseText).data;
                if (global_operations.length == 0) {
                    new_popup(trans('budget.no_operations'), "info");
                    show_empty()
                }
                else {
                    update_charts();
                }
            }
            else {
                new_popup("Error getting operations", "error");
                show_empty()
            }
        };
        xhr.send();
    }
    else {
        show_empty()
    }
}

function update_charts() {
    for (let i = 0; i < budgetAccountDivs.length; i++) {
        budgetAccountDivs[i].style.filter = "none";
    }

    selected_month.disabled = false;
    account_expected_savings.disabled = false;
    additional_expenditure_fieldset.disabled = false;

    processed_operation = add_additional_operations();
    update_pie_chart(processed_operation);
    update_datasheet(processed_operation);
    update_bar_chart(processed_operation);
}

function add_additional_operations() {
    // Faire une deep copy de global_operations
    let operations = JSON.parse(JSON.stringify(global_operations));

    expected_savings = parseInt(account_expected_savings.value == "" ? 0 : account_expected_savings.value);
    let additional_expenditure = document.getElementsByClassName("account-additional-expenditure");

    additional_expenditure_acc = 0;
    for (let i = 0; i < additional_expenditure.length; i++) { additional_expenditure_acc += parseInt(additional_expenditure[i].value == "" ? 0 : additional_expenditure[i].value); }
    document.getElementById("total-add-expenditure").innerHTML = -additional_expenditure_acc;

    operations.push({ ["amount"]: -additional_expenditure_acc, ["category"]: 6, ["label"]: trans('budget.additional_expenditure_label') });
    operations.push({ ["amount"]: -expected_savings, ["category"]: 0, ["label"]: trans('budget.expected_savings_label') });

    return operations;
}

function update_pie_chart(operations) {
    let income = operations.reduce((acc, operation) => (operation.amount > 0) ? acc + operation.amount : acc, 0);
    let expenses = operations.reduce((acc, operation) => (operation.amount < 0) ? acc + operation.amount : acc, 0);
    let remains = income + expenses;

    let sum_per_categories = [];
    sum_per_categories[0] = { ["type"]: -1, ["amount"]: (remains > 0) ? remains : 0 };
    for (let i = 0; i < 9; i++) {
        sum_per_categories[i + 1] = { ["type"]: i, ["amount"]: operations.reduce((acc, operation) => (operation.category == i && operation.amount < 0) ? acc - operation.amount : acc, 0) };
    }

    document.getElementById("account-incomes").value = income.toFixed(2);
    document.getElementById("account-expenses").value = expenses.toFixed(2);
    document.getElementById("account-remains").value = remains.toFixed(2);
    document.getElementById("account-remains").style.color = ((parseInt(remains) > 0) ? "" : "red");


    budget_pie_chart.data.datasets[0].data = sum_per_categories.map(categorie => categorie.amount);

    budget_pie_chart.update();
}

function update_datasheet(operations) {
    datasheet.innerHTML = "";

    operations = operations.filter(operation => operation.amount < 0);

    operations.sort((a, b) => {
        if (a.category === b.category) {
            return a.amount - b.amount;
        }
        return a.category - b.category;
    });

    for (let i = 0; i < operations.length; i++) {
        datasheet.innerHTML += `
                    <li class="table-row" id_operation="${operations[i].id_operation}">
                        <div class="col col-1" data-label="${trans('table.date')}"> ${formatDate(operations[i].date)} </div>
                        <div class="col col-2" data-label="${trans('table.label')}"> ${operations[i].label} </div>
                        <div class="col col-3" data-label="${trans('table.amount')}"> ${(operations[i].amount > 0 ? "+" : "") + operations[i].amount.toFixed(2)} € </div>
                        <div class="col col-4" data-label="${trans('table.category')}"> ${translate_category(operation_type_list[operations[i].category].title)} </div>
                    </li>`;

        datasheet.children[i].style.backgroundColor = chart_colors[operations[i].category + 1];
    }
}

function update_bar_chart(operations) {
    operations = operations.filter(operation => operation.amount < 0);
    operations.forEach(operation => { operation.amount = Math.abs(operation.amount); });
    operations.sort((a, b) => { return b.amount - a.amount; });

    budget_bar_chart.data.datasets[0].data = operations.map(operation => operation.amount);
    budget_bar_chart.data.labels = operations.map(() => "");
    budget_bar_chart.data.datasets[0].backgroundColor = operations.map(operation => chart_colors[operation.category + 1]);

    budget_bar_chart.update();
}

function show_empty() {
    let tmp_html = "";
    for ($i = 0; $i < 14; $i++) {
        tmp_html += `
        <li class="table-row">
            <div class="col col-1" data-label="${trans('table.date')}"> --- </div>
            <div class="col col-2" data-label="${trans('table.label')}"> --- </div>
            <div class="col col-3" data-label="${trans('table.amount')}"> --- </div>
            <div class="col col-4" data-label="${trans('table.category')}"> --- </div>
            <div class="col col-5" data-label="${trans('table.actions')}"></div>
        </li>`;
    }
    datasheet.innerHTML = tmp_html;

    for (let i = 0; i < budgetAccountDivs.length; i++) {
        budgetAccountDivs[i].style.filter = "";
    }

    account_expected_savings.disabled = true;
    additional_expenditure_fieldset.disabled = true;
}

function add_expenditure() {
    let new_expenditure = document.createElement("div");
    new_expenditure.classList.add("additional-expenditure");
    new_expenditure.innerHTML = `
        <div class="row-field">
            <div>
                <input type="text" name="label-additional-expenditure" class="label-additional-expenditure"
                    placeholder="${trans('table.label')}">
                <input type="number" name="account-additional-expenditure" class="account-additional-expenditure" onchange="update_charts()" placeholder="${trans('table.amount')}"> €
            </div>
            <img src="/assets/images/trash.png" class="button" alt="delete" class="card-button"
                onclick="remove_expenditure(this)">
        </div>
    `;

    docum
    document.getElementById("additional-expenditure-section").appendChild(new_expenditure);
}

function remove_expenditure(self) {
    self.parentNode.remove();
    update_charts();
}

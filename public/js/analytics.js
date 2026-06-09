const email = '<%=Session["email"]%>'
const account_list = document.getElementById("selected-account");

const analytics_start = document.getElementById("analytics-start");
const analytics_end = document.getElementById("analytics-end");
const analytics_index = document.getElementById("analytics-index");

const forecast_toggle = document.getElementById("forecast-toggle");
const forecast_ajust = document.getElementById("forecast-ajust");
const forecast_slope_info = document.getElementById("forecast-info")

const categories_chart_container = document.getElementById('categories-account-chart');
let selected_account;
let operations = [];
let accounts = [];
let pie_labels = [];
let pie_colors = [];
let categories_chart;
let log_chart;

window.addEventListener('resize', () => {
    log_chart.resize();
    categories_chart.resize();
});

function set_operation_type_list() {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/v1/operations/types", false);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            operation_type_list = JSON.parse(xhr.responseText).data;

            for (let i = 0; i < 9; i++) {
                pie_labels[i] = translate_category(operation_type_list[i].title);
                pie_colors[i] = operation_type_list[i].chart_color;
            }
        }
        else {
            new_popup("Error getting operation type list", "error");
        }
    };
    xhr.send();
}

onload = () => {
    fill_account_list();
    set_operation_type_list();
    analytics_index.valueAsDate = new Date();

    log_chart = new Chart(
        document.getElementById('log-account-chart'),
        {
            type: 'line',
            options: {
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    x: {
                        type: 'time',
                        ticks: {
                            color: function (context) {
                                return context.tick.value > Date.now() ? 'darkgrey' : 'dark';
                            },
                            callback: function (value) {
                                return new Date(value).toLocaleDateString("fr-FR");
                            }
                        }
                    },
                    y: {
                        ticks: {
                            color: function (context) {
                                return context.tick.value >= 0 ? 'green' : 'red';
                            },
                            callback: function (value) {
                                return value + " €";
                            }
                        }
                    }
                },
                animation: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ": " + (context.parsed.y).toFixed(2) + " €";
                            },
                            title: function (context) {
                                return new Date(context[0].parsed.x).toLocaleDateString("fr-FR");
                            }
                        },
                    }
                }
            }
        });

    categories_chart = new Chart(
        categories_chart_container,
        {
            type: 'pie',
            options: {
                animation: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (value) {
                                return ` ${Math.abs(value.parsed.toFixed(2))} € (${(value.parsed / value.chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0) * 100).toFixed(2)}%)`;
                            }
                        },
                    },
                    title: {
                        display: true,
                        text: t('analytics.chart_expenses_by_category')
                    },
                }
            },
        }
    );

    account_list.addEventListener("change", selected_account_change);

    analytics_start.addEventListener("change", get_operations);
    analytics_end.addEventListener("change", get_operations);

    forecast_toggle.addEventListener("change", update_charts);
    forecast_ajust.addEventListener("change", update_charts);
    analytics_index.addEventListener("change", update_charts);

    document.getElementById("loading-gif").style.display = "none";
}

function fill_account_list() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/api/v1/accounts", true);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            accounts_list = JSON.parse(xhr.responseText).data;
            accounts = JSON.stringify(accounts_list);

            if (accounts_list.length == 0) {
                new_popup(t('analytics.no_account'), "info");
                document.getElementById("analytics-form").disabled = true;
                return;
            }

            accounts_list.forEach(account => {
                account_list.innerHTML += `<option value="${account.id_account}">${account.label}</option>`;
            });
        }
        else {
            new_popup("Error getting accounts", "error");
        }
    };
    xhr.send();
}

function selected_account_change() {
    if (account_list.value > 0) {
        analytics_start.disabled = false;
        analytics_end.disabled = false;

        selected_account = accounts_list.find(account => account.id_account == account_list.value);
        today = new Date();

        if (selected_account.type == 0) {
            analytics_start.valueAsDate = new Date(today.getFullYear() - 2, today.getMonth(), today.getDate());
            analytics_end.valueAsDate = today;
        }
        else {
            analytics_start.valueAsDate = new Date(today.getFullYear() - 3, today.getMonth(), today.getDate());
            analytics_end.valueAsDate = new Date(today.getFullYear(), today.getMonth() + 3, today.getDate());
        }

        get_operations();
    }
    else {
        analytics_start.disabled = true;
        analytics_end.disabled = true;
        log_chart.data = {};
        log_chart.update();
        categories_chart.data = {};
        categories_chart.update();
    }
}

function get_operations() {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", `/api/v1/accounts/operations?id_account=${selected_account.id_account}&start=${analytics_start.value}&end=${analytics_end.value}`, true);
    xhr.onload = () => {
        if (Math.floor(xhr.status / 100) === 2) {
            operations = JSON.parse(xhr.responseText).data;

            if (operations.length == 0) {
                new_popup(t('analytics.no_operations'), "info");
            }

            // Security if there is no operation at the start of the chart
            let xhr2 = new XMLHttpRequest();
            xhr2.open("GET", `/api/v1/accounts/balance?id_account=${account_list.value}&date=${analytics_start.value}`, false);
            xhr2.onload = () => {
                if (Math.floor(xhr2.status / 100) === 2) {
                    operations.unshift({ ["date"]: analytics_start.value, ["new_sold"]: parseInt(JSON.parse(xhr2.responseText).data) });
                    operations.push({ ["date"]: analytics_end.value, ["new_sold"]: operations[operations.length - 1].new_sold });
                }
                else {
                    new_popup("Error getting balance", "error");
                }
            };
            xhr2.send();

            update_charts();
        }
        else {
            new_popup("Error getting operations", "error");
        }
    };
    xhr.send();
}

function update_charts() {
    forecast_slope_info.style.display = forecast_toggle.checked ? "inline" : "none";
    selected_account.type ? update_saving_chart() : update_checking_chart();
}

function update_checking_chart() {
    update_saving_chart()
    categories_chart_container.parentNode.style.display = "block";

    let sum_per_categories = [];
    for (let i = 0; i < 9; i++) {
        sum_per_categories[i] = { ["type"]: i, ["amount"]: operations.reduce((acc, operation) => (operation.category == i && operation.amount < 0) ? acc + operation.amount : acc, 0) };
    }

    let data = {
        labels: pie_labels,
        datasets: [
            {
                data: sum_per_categories.map(categorie => categorie.amount),
                backgroundColor: pie_colors,
                hoverOffset: 4
            }
        ]
    };

    categories_chart.data = data;
    categories_chart.update();
    categories_chart.resize();
}

function update_saving_chart() {
    categories_chart_container.parentNode.style.display = "none";

    let data = {
        labels: operations.map(operation => operation.date),
        datasets: [
            {
                stepped: true,
                label: selected_account.label,
                data: operations.map(operation => ({ ["x"]: operation.date, ["y"]: operation.new_sold })),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: "start",
                pointStyle: false,
                borderWidth: 2,
                pointHoverRadius: 15
            }
        ]
    };

    log_chart.data = data;

    if (forecast_toggle.checked) {
        data = forecast(data);
    }

    log_chart.update();
    log_chart.resize();
}

function forecast(data) {
    let { slope, intercept } = calculateRegressionParameters(operations.slice());

    const AjustlastOperation = operations.slice().reverse().find(operation => new Date(operation.date) <= new Date(analytics_index.value));
    const AjustlastSold = AjustlastOperation?.new_sold;

    if (forecast_ajust.checked) {
        intercept = AjustlastSold - slope * new Date(analytics_index.value).getTime();
    }

    const regressionData = calculateRegressionLine(slope, intercept);
    forecast_slope_info.innerHTML = ` : ${(slope*86400000*30).toFixed(2)} €/month`;

    var prev = {
        stepped: false,
        label: t('analytics.chart_predicted'),
        data: regressionData,
        borderColor: 'rgb(99, 132, 255)',
        pointStyle: false,
    };

    data.datasets.push(prev);

    return data;
}

function calculateRegressionParameters(points) {
    points.pop(); // Remove fake last point created to fill the chart
    points.push({ ["date"]: DateToString(new Date()), ["new_sold"]: points[points.length - 1].new_sold });
    const x = points.map((operation) => new Date(operation.date).getTime());
    const y = points.map((operation) => operation.new_sold);

    const n = x.length;
    let sumX = 0;
    let sumY = 0;
    let sumXY = 0;
    let sumXX = 0;

    for (let i = 0; i < n; i++) {
        sumX += x[i];
        sumY += y[i];
        sumXY += x[i] * y[i];
        sumXX += x[i] * x[i];
    }

    const slope = (n * sumXY - sumX * sumY) / (n * sumXX - sumX * sumX);
    const intercept = (sumY - slope * sumX) / n;

    return { slope, intercept };
}

function calculateRegressionLine(slope, intercept){
    const start = new Date(analytics_start.value).getTime();
    const end = new Date(analytics_end.value).getTime();
    const step = (end - start) / 100;

    let regressionData = [];
    for (let i = start; i <= end; i += step) {
        regressionData.push({ x: i, y: slope * i + intercept });
    }

    return regressionData;
}

function DateToString(date) {
    return `${date.getFullYear()}-${date.getMonth()+1}-${date.getDate()}`;
}

function exportCSV() {
    let csv = "Date,Amount,Label,Category\n";

    let CSVoperations = operations.slice();
    CSVoperations.shift(); // Remove the first operation
    CSVoperations.pop(); // Remove the last operation

    CSVoperations.forEach(operation => {
        csv += `${operation.date},${operation.amount},${operation.label},${operation.category}\n`;
    });

    let hiddenElement = document.createElement('a');
    hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
    hiddenElement.target = '_blank';
    hiddenElement.download = `${selected_account.label}_operations.csv`;
    hiddenElement.click();
}

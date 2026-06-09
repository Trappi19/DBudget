
<link rel="stylesheet" href="/public/styles/pages/analytics/analytics.css">
<link rel="stylesheet" href="/public/styles/pages/budget/budget.css">
<link rel="stylesheet" href="/public/styles/table/table.css">
<link rel="stylesheet" href="/public/styles/pages/home/home.css">

<section id="analytics-board">
    <fieldset id="analytics-form">
        <div class="row-field">
            <select name="selected-checking-account" id="selected-checking-account">
                <option value="0"><?= t('budget.select_checking') ?></option>
            </select>
            <input type="month" name="selected-month" id="selected-month" disabled>
        </div>
    </fieldset>

    <section class="analytics-charts" id="checking-analytics-charts">
        <div id="checking-account-pannel">
            <fieldset id="checking-account-fieldset">
                <legend><?= t('budget.account_info') ?></legend>
                <div class="row-field">
                    <label for="account-incomes"><?= t('budget.month_incomes') ?></label>
                    <span><input type="text" name="account-incomes" id="account-incomes" disabled>€</span>
                </div>
                <div class="row-field">
                    <label for="account-expenses"><?= t('budget.month_expenses') ?></label>
                    <span><input type="text" name="account-expenses" id="account-expenses" disabled>€</span>
                </div>
                <div class="row-field">
                    <label for="account-remains"><?= t('budget.month_remains') ?></label>
                    <span><input type="text" name="account-remains" id="account-remains" disabled>€</span>
                </div>
                <div class="row-field">
                    <label for="account-expected-savings"><?= t('budget.expected_savings') ?></label>
                    <section><input type="number" name="account-expected-savings" id="account-expected-savings"
                            disabled>€</section>
                </div>
            </fieldset>
            <fieldset id="additional-expenditure-fieldset" disabled>
                <legend><?= t('budget.additional_expenditure') ?></legend>
                <section id="additional-expenditure-section">
                    <div class="row-field">
                        <section>
                            <input type="text" name="label-additional-expenditure" class="label-additional-expenditure"
                                placeholder="<?= t('table.label') ?>">
                            <input type="number" name="account-additional-expenditure"
                                class="account-additional-expenditure" placeholder="<?= t('table.amount') ?>"
                                onchange="update_charts()"><span> €</span>
                        </section>
                        <img src="/assets/images/trash.png" class="button" alt="delete" class="card-button"
                            onclick="remove_expenditure(this)">
                    </div>
                </section>
                <div class="row-field bottom-info">
                    <img src="/assets/images/plus.webp" class="button add-button" alt="add" class="card-button"
                        onclick="add_expenditure()">
                    <span><?= t('budget.total_expected') ?> : <span id="total-add-expenditure">0</span> €</span>
                </div>
            </fieldset>
            <div id="checking-account-info">
            </div>
        </div>
        <div class="budget-account-div">
            <canvas id="pie-chart-canvas" style="height: 460px; width: 460px;">Your browser does not support the
                canvas element.</canvas>
        </div>
    </section>

    <section class="dashboard">
        <section class="container">
            <ul class="responsive-table">
                <li class="table-header">
                    <div class="col col-1"><?= t('table.date') ?></div>
                    <div class="col col-2"><?= t('table.label') ?></div>
                    <div class="col col-3"><?= t('table.amount') ?></div>
                    <div class="col col-4"><?= t('table.category') ?></div>
                </li>
                <div id="datasheet" class="budget-account-div">
                    <?php for ($i = 0; $i < 14; $i++): ?>
                        <li class="table-row">
                            <div class="col col-1" data-label="<?= t('table.date') ?>"> --- </div>
                            <div class="col col-2" data-label="<?= t('table.label') ?>"> --- </div>
                            <div class="col col-3" data-label="<?= t('table.amount') ?>"> --- </div>
                            <div class="col col-4" data-label="<?= t('table.category') ?>"> --- </div>
                        </li>
                    <?php endfor; ?>
                </div>
            </ul>
        </section>
        <section class="container">
            <div class="budget-account-div">
                <canvas id="bar-chart-canvas" style="height: 500px; width: 100%;">Your browser does not support the
                    canvas element.</canvas>
            </div>
            <div>
                <input type="checkbox" id="logarithmic-axis" name="logarithmic-axis">
                <label for="logarithmic-axis"><?= t('budget.logarithmic_axis') ?></label>
            </div>
        </section>
    </section>

</section>

<br>
<br>

<script src="https://cdn.jsdelivr.net/npm/chart.js@^4"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@^2"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@^1"></script>

<script src="/public/js/budget.js" type="text/javascript"></script>



<link rel="stylesheet" href="/public/styles/pages/analytics/analytics.css">

<section id="phone-warning">
    <h1><?= t('analytics.phone_warning') ?></h1>
    <h2><?= t('analytics.phone_warning_sub') ?></h2>
</section>

<section id="analytics-board">
    <fieldset id="analytics-form">
        <div class="row-field">
            <select name="selected-account" id="selected-account">
                <option value="0"><?= t('analytics.select_account') ?></option>
            </select>
            <input type="date" name="start" id="analytics-start" disabled>
            <input type="date" name="end" id="analytics-end" disabled>
        </div>
    </fieldset>

    <section class="analytics-charts">
        <div id="log-account-div"><canvas id="log-account-chart">Your browser does not support the canvas
                element.</canvas></div>
        <div id="categories-account-div"><canvas id="categories-account-chart">Your browser does not support the canvas
                element.</canvas></div>
    </section>

    <section id="under-chart">
        <div>
            <div class="forecast-checkbox">
                <input type="checkbox" id="forecast-toggle" name="forecast-toggle">
                <label for="forecast-toggle" class="noselect"><?= t('analytics.enable_forecast') ?></label>
                <span id="forecast-info"></span>
            </div>
            <div class="forecast-checkbox">
                <input type="checkbox" id="forecast-ajust" name="forecast-ajust">
                <label for="forecast-ajust" class="noselect"><?= t('analytics.adjust_to_date') ?></label>
            </div>
            <input type="date" name="index" id="analytics-index">
        </div>
        <div>
            <a id="export-csv-button" class="valide_button no-select" onclick="exportCSV()"><?= t('analytics.export_csv') ?></a>
        </div>
    </section>

</section>

<br>
<br>

<script src="https://cdn.jsdelivr.net/npm/chart.js@^4"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@^2"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@^1"></script>

<script src="/public/js/analytics.js" type="text/javascript"></script>


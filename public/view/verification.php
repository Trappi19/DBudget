
<link rel="stylesheet" href="/public/styles/pages/analytics/analytics.css">
<link rel="stylesheet" href="/public/styles/pages/budget/budget.css">
<link rel="stylesheet" href="/public/styles/pages/operations/operations.css">
<link rel="stylesheet" href="/public/styles/pages/verification/verification.css">
<link rel="stylesheet" href="/public/styles/table/table.css">

<section id="analytics-board">
    <fieldset id="analytics-form">
        <div class="row-field">
            <select name="selected-checking-account" id="selected-checking-account">
                <option value="0"><?= trans('budget.select_checking') ?></option>
            </select>
            <input type="month" name="selected-month" id="selected-month" disabled>
        </div>
    </fieldset>
</section>

<section class="dashboard">
    <section class="container">
        <ul class="responsive-table">
            <li class="table-header">
                <div class="col col-1"><?= trans('table.date') ?></div>
                <div class="col col-2"><?= trans('table.label') ?></div>
                <div class="col col-3"><?= trans('table.amount') ?></div>
                <div class="col col-4"><?= trans('table.category') ?></div>
                <div class="col col-5"><?= trans('table.actions') ?></div>
            </li>
            <div id="datasheet">
                <?php for ($i = 0; $i < 14; $i++): ?>
                    <li class="table-row">
                        <div class="col col-1" data-label="<?= trans('table.date') ?>"> --- </div>
                        <div class="col col-2" data-label="<?= trans('table.label') ?>"> --- </div>
                        <div class="col col-3" data-label="<?= trans('table.amount') ?>"> --- </div>
                        <div class="col col-4" data-label="<?= trans('table.category') ?>"> --- </div>
                        <div class="col col-5" data-label="<?= trans('table.actions') ?>"></div>
                    </li>
                <?php endfor; ?>
            </div>
        </ul>

        <!-- bouton creer une nouvelle opération et bouton confirmer delete -->
        <div class="row-field">
            <a id="add-operation" class="valide_button noselect" onclick="open_new_operation_tab()"><?= trans('verification.add_missing') ?></a>
            <a id="confirm-delete" class="valide_button noselect" onclick="confirm_popup_delete()"><?= trans('verification.confirm_delete') ?></a>
        </div>
    </section>

    <section class="container" id="scollable">
        <div id="notes-pannel">
            <textarea id="notes" name="notes" rows="12" cols="35"><?= trans('verification.notes_placeholder') ?></textarea>
        </div>
        <div id="month-brief"><p><?= trans('verification.outcome') ?>: <span id="total-outcome">0.00€</span></p><p><?= trans('verification.income') ?>: <span id="total-income">0.00€</span></p><p><?= trans('verification.balance_sheet') ?>: <span id="total-balance">0.00€</span></p></div>
    </section>
</section>

<br>
<br>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/verification.js" type="text/javascript"></script>


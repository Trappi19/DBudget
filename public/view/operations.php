
<link rel="stylesheet" href="/public/styles/pages/operations/operations.css">
<link rel="stylesheet" href="/public/styles/table/table.css">

<section class="dashboard">
    <section class="container">
        <ul class="responsive-table">
            <li class="table-header">
                <div class="col col-1"><?= t('table.date') ?></div>
                <div class="col col-2"><?= t('table.label') ?></div>
                <div class="col col-3"><?= t('table.amount') ?></div>
                <div class="col col-4"><?= t('table.category') ?></div>
                <div class="col col-5"><?= t('table.actions') ?></div>
            </li>
            <div id="datasheet">
                <?php for ($i = 0; $i < 14; $i++): ?>
                    <li class="table-row">
                        <div class="col col-1" data-label="<?= t('table.date') ?>"> --- </div>
                        <div class="col col-2" data-label="<?= t('table.label') ?>"> --- </div>
                        <div class="col col-3" data-label="<?= t('table.amount') ?>"> --- </div>
                        <div class="col col-4" data-label="<?= t('table.category') ?>"> --- </div>
                        <div class="col col-5" data-label="<?= t('table.actions') ?>"></div>
                    </li>
                <?php endfor; ?>
            </div>
        </ul>
        <input type="date" name="date-to-search" id="date-to-search" onchange="update_datasheet()">
        <select name="balance-view" id="balance-view" onchange="update_datasheet()">
            <option value="0"><?= t('operations.select_account') ?></option>
        </select>
        <input type="text" name="balance" id="balance" placeholder="<?= t('operations.balance') ?>" disabled>
    </section>

    <section id="add-pannel" class="container">
        <h1><?= t('operations.add_operation') ?></h1>

        <form id="add-form">

            <div id="account_selection">
                <p><?= t('operations.select_account') ?></p>
                <select name="selected-account" id="selected-account">
                    <option value="0"><?= t('operations.select_account') ?></option>
                </select>
            </div>

            <fieldset id="add-field">
                <div>
                    <label for="amount"><?= t('operations.amount') ?></label>
                    <input type="number" name="amount" id="amount" placeholder="100€"
                        required="We need to know how much you want to transfer" step="0.01">
                </div>
                <div class="flex-div">
                    <div>
                        <label for="operation_date"><?= t('operations.date') ?></label>
                        <input type="date" name="date" id="operation_date" required>
                    </div>

                    <div>
                        <label for="category"><?= t('table.category') ?></label>
                        <select name="category" id="category">
                        </select>
                    </div>
                </div>
                <div>
                    <label for="label"><?= t('operations.label') ?></label>
                    <input type="text" name="label" id="label" placeholder="<?= t('operations.label') ?>" required>
                </div>

                <a id="create-operation" class="valide_button noselect" onclick="create_operation()"><?= t('operations.create') ?></a>

            </fieldset>
        </form>
    </section>
</section>

<br>
<br>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/operations.js" type="text/javascript"></script>


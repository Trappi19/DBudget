<link rel="stylesheet" href="/public/styles/components/table/responsive-table.css">
    <link rel="stylesheet" href="/public/styles/components/table/table-operations.css">
    <link rel="stylesheet" href="/public/styles/components/add-panel-operation.css">
    <link rel="stylesheet" href="/public/styles/components/operation-filter.css">
    <link rel="stylesheet" href="/public/styles/components/notes.css">

<section class="dashboard">
    <section class="container">

        <section id="search-filters">
            <div id="search-filters-left">
                <input type="text" name="search-label" id="search-label" placeholder="<?= trans('operations.filters.search_label') ?>">
                <button type="button" id="filter-toggle"><img src="/assets/images/filter.png" alt="filter"></button>
            </div>
            <div id="filter-dropdown">
                <select name="balance-view" id="balance-view">
                    <option value="0"><?= trans('operations.filters.all_accounts') ?></option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= (int) $account['id_account'] ?>"><?= htmlspecialchars($account['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="filter-type" id="filter-type">
                    <option value=""><?= trans('operations.filters.all_types') ?></option>
                </select>
                <input type="date" name="date-to-search" id="date-to-search">
            </div>
            <input type="text" name="balance" id="balance" placeholder="<?= trans('operations.filters.balance') ?>" disabled style="display:none;">
        </section>

        <ul class="responsive-table responsive-table--5cols">
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
            <div id="balance-footer">
                <span id="balance-footer-text"></span>
            </div>
        </ul>
    </section>

    <section id="add-pannel" class="container">
        <h1><?= trans('operations.add_operation') ?></h1>

        <form id="add-form">

            <div id="account_selection">
                <p><?= trans('operations.select_account') ?></p>
                <select name="selected-account" id="selected-account">
                    <option value="0"><?= trans('operations.account_list') ?></option>
                    <?php foreach ($accounts as $account): ?>
                        <option value="<?= (int) $account['id_account'] ?>"><?= htmlspecialchars($account['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <fieldset id="add-field">
                <div>
                    <label for="amount"><?= trans('operations.amount') ?></label>
                    <input type="number" name="amount" id="amount" placeholder="100€"
                        required="We need to know how much you want to transfer" step="0.01">
                </div>
                <div class="flex-div">
                    <div>
                        <label for="operation_date"><?= trans('operations.date') ?></label>
                        <input type="date" name="date" id="operation_date" required>
                    </div>

                    <div>
                        <label for="category"><?= trans('table.category') ?></label>
                        <select name="category" id="category">
                        </select>
                    </div>
                </div>
                <div>
                    <label for="label"><?= trans('operations.label') ?></label>
                    <input type="text" name="label" id="label" placeholder="<?= trans('operations.label') ?>" required>
                </div>

                <a id="create-operation" class="valide_button noselect" onclick="create_operation()"><?= trans('operations.create') ?></a>

            </fieldset>
        </form>
    </section>
</section>


<script>
    window.ACCOUNTS = <?= json_encode($accounts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    window.OPERATION_TYPES = <?= json_encode($operation_types, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/pages/operations.js" type="text/javascript"></script>

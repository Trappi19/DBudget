<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0&icon_names=filter_alt" />
<link rel="stylesheet" href="/public/styles/pages/operations/operations.css">
<link rel="stylesheet" href="/public/styles/table/table.css">

<section class="dashboard">
    <section class="container">

        <section id="search-filters">
            <div id="search-filters-left">
                <input type="text" name="search-label" id="search-label" placeholder="Rechercher un label...">
                <button type="button" id="filter-toggle"><span class="material-symbols-outlined">filter_alt</span></button>
            </div>
            <div id="filter-dropdown">
                <select name="balance-view" id="balance-view">
                    <option value="0">Tous les comptes</option>
                </select>
                <select name="filter-type" id="filter-type">
                    <option value="0">Tous les types</option>
                </select>
                <input type="date" name="date-to-search" id="date-to-search">
            </div>
            <input type="text" name="balance" id="balance" placeholder="Balance" disabled style="display:none;">
        </section>

        <ul class="responsive-table">
            <li class="table-header">
                <div class="col col-1">Date</div>
                <div class="col col-2">Label</div>
                <div class="col col-3">Amount</div>
                <div class="col col-4">Category</div>
                <div class="col col-5">Actions</div>
            </li>
            <div id="datasheet">
                <?php for ($i = 0; $i < 14; $i++): ?>
                    <li class="table-row">
                        <div class="col col-1" data-label="Date"> --- </div>
                        <div class="col col-2" data-label="Label"> --- </div>
                        <div class="col col-3" data-label="Amount"> --- </div>
                        <div class="col col-4" data-label="Category"> --- </div>
                        <div class="col col-5" data-label="Actions"></div>
                    </li>
                <?php endfor; ?>
                <li id="no-result" class="table-row" style="display:none;">
                    <div style="width:100%; text-align:center;">Aucune opération</div>
                </li>
            </div>
        </ul>
    </section>

    <section id="add-pannel" class="container">
        <h1>Add an operation</h1>

        <form id="add-form">

            <div id="account_selection">
                <p>Selects the account on which to add an operation</p>
                <select name="selected-account" id="selected-account">
                    <option value="0"> Select an account </option>
                </select>
            </div>

            <fieldset id="add-field">
                <div>
                    <label for="amount">Amount</label>
                    <input type="number" name="amount" id="amount" placeholder="100€"
                        required="We need to know how much you want to transfer" step="0.01">
                </div>
                <div class="flex-div">
                    <div>
                        <label for="operation_date">Date</label>
                        <input type="date" name="date" id="operation_date" required>
                    </div>

                    <div>
                        <label for="category">Category</label>
                        <select name="category" id="category">
                        </select>
                    </div>
                </div>
                <div>
                    <label for="label">Label</label>
                    <input type="text" name="label" id="label" placeholder="Label" required>
                </div>

                <a id="create-operation" class="valide_button noselect" onclick="create_operation()">Create</a>

            </fieldset>
        </form>
    </section>
</section>

<br>
<br>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/operations.js" type="text/javascript"></script>


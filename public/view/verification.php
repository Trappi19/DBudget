
<link rel="stylesheet" href="/public/styles/components/table/responsive-table.css">
<link rel="stylesheet" href="/public/styles/components/table/table-operations.css">
<link rel="stylesheet" href="/public/styles/components/analytics-board.css">
    <link rel="stylesheet" href="/public/styles/components/notes.css">

<section id="analytics-board">
    <fieldset id="analytics-form">
        <div class="row-field">
            <select name="selected-checking-account" id="selected-checking-account">
                <option value="0"> Select a checking account </option>
            </select>
            <input type="month" name="selected-month" id="selected-month" disabled>
        </div>
    </fieldset>
</section>

<section class="dashboard">
    <section class="container">
        <ul class="responsive-table responsive-table--5cols">
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
            </div>
        </ul>

        <!-- bouton creer une nouvelle opération et bouton confirmer delete -->
        <div class="row-field">
            <a id="add-operation" class="valide_button valide_button--spaced noselect" onclick="open_new_operation_tab()">Add missing
                operation</a>
            <a id="confirm-delete" class="valide_button valide_button--spaced noselect" onclick="confirm_popup_delete()">Confirm delete</a>
        </div>
    </section>

    <section class="container" id="scollable">
        <div id="notes-pannel">
            <textarea id="notes" name="notes" rows="12" cols="35">Take notes here.</textarea>
        </div>
        <div id="month-brief"><p>Outcome: <span id="total-outcome">0.00€</span></p><p>Income: <span id="total-income">0.00€</span></p><p>Balance sheet: <span id="total-balance">0.00€</span></p></div>
    </section>
</section>

<br>
<br>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/pages/verification.js" type="text/javascript"></script>


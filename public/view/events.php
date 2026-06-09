
<link rel="stylesheet" href="/public/styles/pages/events/events.css">
<link rel="stylesheet" href="/public/styles/table/table.css">

<section id="event-board">

    <fieldset id="event-form">
        <div class="row-field">
            <div class="column-field">
                <label for="selected-account"><?= t('events.account') ?></label>
                <select name="selected-account" id="selected-account" onchange="set_select_category()">
                    <option value="0"><?= t('operations.select_account') ?></option>
                </select>
            </div>

            <div class="column-field">
                <label for="label"><?= t('events.label') ?></label>
                <input type="text" name="label" id="label" placeholder="<?= t('events.label') ?>" required>
            </div>

            <div class="column-field">
                <label for="amount"><?= t('events.amount') ?></label>
                <input type="number" name="amount" id="amount" placeholder="100€"
                    required="We need to know how much you want to transfer" step="0.01">
            </div>

            <div class="column-field">
                <label for="event_start"><?= t('events.start') ?></label>
                <input type="date" name="start" id="event_start" required>
            </div>

            <div class="column-field">
                <label for="event_end"><?= t('events.end') ?></label>
                <input type="date" name="end" id="event_end" required>
            </div>

            <div class="column-field">
                <label for="frequency"><?= t('events.frequency') ?></label>
                <select name="frequency" id="frequency">
                    <option value="3"><?= t('events.every_year') ?></option>
                    <option value="2"><?= t('events.every_month') ?></option>
                    <option value="1"><?= t('events.every_week') ?></option>
                    <option value="0"><?= t('events.every_day') ?></option>
                </select>
            </div>

            <div class="column-field">
                <label for="category"><?= t('table.category') ?></label>
                <select name="category" id="category">
                    <option value="0"><?= t('categories.other') ?></option>
                </select>
            </div>
        </div>
        <a id="create-event" class="valide_button noselect" onclick="create_event()"><?= t('events.create') ?></a>
    </fieldset>

    <section id="event-list">
        <ul class="responsive-table">

            <li class="table-header">
                <div class="col col-1"><?= t('table.label') ?></div>
                <div class="col col-2"><?= t('table.amount') ?></div>
                <div class="col col-3"><?= t('table.account') ?></div>
                <div class="col col-4"><?= t('table.start') ?></div>
                <div class="col col-5"><?= t('table.end') ?></div>
                <div class="col col-6"><?= t('table.frequency') ?></div>
                <div class="col col-7"><?= t('table.category') ?></div>
                <div class="col col-8"><?= t('table.actions') ?></div>
            </li>
            <div id="datasheet"> </div>
        </ul>
    </section>

    <input type="date" name="date-to-search" id="date-to-search" onchange="update_datasheet()">

</section>

<br>
<br>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/events.js" type="text/javascript"></script>



<link rel="stylesheet" href="/public/styles/pages/events/events.css">
<link rel="stylesheet" href="/public/styles/table/table.css">

<section id="event-board">

    <fieldset id="event-form">
        <div class="row-field">
            <div class="column-field">
                <label for="selected-account"><?= trans('events.account') ?></label>
                <select name="selected-account" id="selected-account" onchange="set_select_category()">
                    <option value="0"><?= trans('operations.select_account') ?></option>
                </select>
            </div>

            <div class="column-field">
                <label for="label"><?= trans('events.label') ?></label>
                <input type="text" name="label" id="label" placeholder="<?= trans('events.label') ?>" required>
            </div>

            <div class="column-field">
                <label for="amount"><?= trans('events.amount') ?></label>
                <input type="number" name="amount" id="amount" placeholder="100€"
                    required="We need to know how much you want to transfer" step="0.01">
            </div>

            <div class="column-field">
                <label for="event_start"><?= trans('events.start') ?></label>
                <input type="date" name="start" id="event_start" required>
            </div>

            <div class="column-field">
                <label for="event_end"><?= trans('events.end') ?></label>
                <input type="date" name="end" id="event_end" required>
            </div>

            <div class="column-field">
                <label for="frequency"><?= trans('events.frequency') ?></label>
                <select name="frequency" id="frequency">
                    <option value="3"><?= trans('events.every_year') ?></option>
                    <option value="2"><?= trans('events.every_month') ?></option>
                    <option value="1"><?= trans('events.every_week') ?></option>
                    <option value="0"><?= trans('events.every_day') ?></option>
                </select>
            </div>

            <div class="column-field">
                <label for="category"><?= trans('table.category') ?></label>
                <select name="category" id="category">
                    <option value="0"><?= trans('categories.other') ?></option>
                </select>
            </div>
        </div>
        <a id="create-event" class="valide_button noselect" onclick="create_event()"><?= trans('events.create') ?></a>
    </fieldset>

    <section id="event-list">
        <ul class="responsive-table">

            <li class="table-header">
                <div class="col col-1"><?= trans('table.label') ?></div>
                <div class="col col-2"><?= trans('table.amount') ?></div>
                <div class="col col-3"><?= trans('table.account') ?></div>
                <div class="col col-4"><?= trans('table.start') ?></div>
                <div class="col col-5"><?= trans('table.end') ?></div>
                <div class="col col-6"><?= trans('table.frequency') ?></div>
                <div class="col col-7"><?= trans('table.category') ?></div>
                <div class="col col-8"><?= trans('table.actions') ?></div>
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


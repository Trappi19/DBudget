
<link rel="stylesheet" href="/public/styles/pages/home/home.css">
<link rel="stylesheet" href="/public/styles/table/table.css">

<section class="dashboard">
    <section class="container">
        <ul class="responsive-table">
            <li class="table-header">
                <div class="col col-1">Date</div>
                <div class="col col-2">Label</div>
                <div class="col col-3">Amount</div>
                <div class="col col-4">Category</div>
            </li>
            <div id="datasheet">
                <?php for ($i = 0; $i < 14; $i++): ?>
                    <li class="table-row">
                        <div class="col col-1" data-label="Date"> --- </div>
                        <div class="col col-2" data-label="Label"> --- </div>
                        <div class="col col-3" data-label="Amount"> --- </div>
                        <div class="col col-4" data-label="Category"> --- </div>
                    </li>
                <?php endfor; ?>
            </div>
        </ul>
        <a id="create-transfer" class="valide_button noselect" href="/app/operations">Add Operation</a>
    </section>

    <!-- col gauche avec liste mouvement bancaire récent X dernier à partir de date ajd -->

    <section class="container">
        <section>
            <!--mini zone compte épargne-->
            <div style="width: 100%;"><canvas id="overview-savings-account"></canvas></div>
        </section>

        <section>
            <!-- camembert budget du mois -->
            <div style="width: 70%;"><canvas id="overview-monthly-budget"></canvas></div>
        </section>
    </section>
</section>

<br>
<br>

<script src="https://cdn.jsdelivr.net/npm/chart.js@^4"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@^2"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@^1"></script>
<script src="/public/js/home.js" type="text/javascript"></script>


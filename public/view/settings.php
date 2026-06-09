<link rel="stylesheet" href="/public/styles/pages/settings/settings.css">

<section id="settings-board">
    <div class="settings-card">
        <h2><?= t('settings.title') ?></h2>

        <div class="settings-group">
            <label for="username"><?= t('settings.username') ?></label>
            <input type="text" id="input-username" value="<?= htmlspecialchars($username) ?>" placeholder="<?= t('settings.username_placeholder') ?>">
        </div>

        <div class="settings-group">
            <label for="opt-langue"><?= t('settings.language') ?></label>
            <select id="opt-langue"></select>
        </div>

        <div class="settings-group settings-group--inline">
            <label for="opt-theme"><?= t('settings.dark_theme') ?></label>
            <input type="checkbox" id="opt-theme">
        </div>

        <button class="valide_button" onclick="saveSettings()"><?= t('settings.save') ?></button>
    </div>
</section>

<script src="/public/js/settings.js" type="text/javascript"></script>
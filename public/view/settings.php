<link rel="stylesheet" href="/public/styles/components/cards-account.css">

<section id="settings-board">
    <div class="settings-card">
        <h2><?= trans('settings.title') ?></h2>

        <div class="settings-group">
            <label for="username"><?= trans('settings.username') ?></label>
            <input type="text" id="input-username" value="<?= htmlspecialchars($username) ?>" placeholder="<?= trans('settings.username_placeholder') ?>">
        </div>

        <div class="settings-group">
            <label for="opt-langue"><?= trans('settings.language') ?></label>
            <select id="opt-langue">
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= htmlspecialchars($lang['code']) ?>"<?= $lang['code'] === $current_lang ? ' selected' : '' ?>>
                        <?= htmlspecialchars($lang['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="settings-group settings-group--inline">
            <label for="opt-theme"><?= trans('settings.dark_theme') ?></label>
            <input type="checkbox" id="opt-theme">
        </div>

        <button class="valide_button valide_button--save_settings" onclick="saveSettings()"><?= trans('settings.save') ?></button>
    </div>
</section>

<script src="/public/js/pages/settings.js" type="text/javascript"></script>
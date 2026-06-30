<link rel="stylesheet" href="/public/styles/components/cards-account.css">
<link rel="stylesheet" href="/public/styles/components/popup/contact-form.css">

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

        <div class="settings-group--inline" style="display:flex;justify-content:space-between;gap:10px;">
            <button class="valide_button" onclick="openContactForm()" style="background:#f0f5ff;color:#2c7be5;border:1px solid #c0d6f5;margin:0;">
                <?= trans('settings.contact_form.title') ?>
            </button>
            <button class="valide_button valide_button--save_settings" onclick="saveSettings()" style="margin:0;"><?= trans('settings.save') ?></button>
        </div>
    </div>
</section>

<!-- Contact form overlay — always in DOM, toggled via JS -->
<div id="contact-overlay" aria-modal="true" role="dialog" aria-labelledby="contact-form-title">
    <div class="contact-form">
        <h2 id="contact-form-title"><?= trans('settings.contact_form.title') ?></h2>

        <!-- Fields -->
        <div id="contact-form-fields">
            <div class="contact-form__group">
                <label for="contact-from"><?= trans('settings.contact_form.from') ?></label>
                <input type="text" id="contact-from" value="<?= htmlspecialchars($email) ?>" disabled>
            </div>

            <div class="contact-form__group">
                <label for="contact-theme"><?= trans('settings.contact_form.theme') ?></label>
                <select id="contact-theme">
                    <option value="Bug"><?= trans('settings.contact_form.theme_bug') ?></option>
                    <option value="Suggestion"><?= trans('settings.contact_form.theme_suggestion') ?></option>
                    <option value="Question"><?= trans('settings.contact_form.theme_question') ?></option>
                    <option value="Autre"><?= trans('settings.contact_form.theme_other') ?></option>
                </select>
            </div>

            <div class="contact-form__group">
                <label for="contact-subject"><?= trans('settings.contact_form.subject') ?></label>
                <input type="text" id="contact-subject" maxlength="50" placeholder="<?= trans('settings.contact_form.subject_placeholder') ?>">
            </div>

            <div class="contact-form__group">
                <label for="contact-message"><?= trans('settings.contact_form.message') ?></label>
                <textarea id="contact-message" maxlength="1000" placeholder="<?= trans('settings.contact_form.message_placeholder') ?>"></textarea>
                <div class="contact-form__counter"><span id="contact-message-count">0</span>/<span id="contact-message-max">0</span></div>
            </div>

            <div class="contact-form__actions">
                <button class="contact-form__cancel" onclick="closeContactForm()"><?= trans('settings.contact_form.cancel') ?></button>
                <button class="contact-form__send" id="contact-send-btn" onclick="sendContactForm()"><?= trans('settings.contact_form.send') ?></button>
            </div>
        </div>

        <!-- Loading -->
        <div class="contact-form__loading" id="contact-loading">
            <div class="spinner"></div>
            <span><?= trans('settings.contact_form.sending') ?></span>
        </div>

        <!-- Feedback (success / error) -->
        <div class="contact-form__feedback" id="contact-feedback">
            <img class="contact-form__feedback-icon" id="contact-feedback-icon" src="" alt="">
            <p id="contact-feedback-text"></p>
            <button class="contact-form__cancel" onclick="closeContactForm()"><?= trans('settings.contact_form.close') ?></button>
        </div>
    </div>
</div>

<script>window.MAIL_CONTACT = "<?= htmlspecialchars($mail_contact, ENT_QUOTES, 'UTF-8') ?>";</script>
<script src="/public/js/pages/settings.js" type="text/javascript"></script>
<script src="/public/js/components/contact_form.js" type="text/javascript"></script>

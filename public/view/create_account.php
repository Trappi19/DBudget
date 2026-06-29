
<link rel="stylesheet" href="/public/styles/components/auth/auth.css">
<link rel="stylesheet" href="/public/styles/components/auth/create-account.css">
<link rel="stylesheet" href="/public/styles/components/popup/toast.css">

<section id="section-core">
    <div class="form-box form-box--register">
        <button type="button" id="register-back" class="auth-back" aria-label="<?= trans('auth.register.back') ?>" onclick="goBack()" hidden>
            <span class="material-icons">arrow_back</span>
        </button>
        <div class="form-value">
            <form id="register-form" novalidate onsubmit="event.preventDefault(); submitActiveStep();">

                <!-- Step 1 — Language -->
                <div class="auth-step auth-step--active" data-step="1">
                    <h2><?= trans('auth.register.title') ?></h2>

                    <p class="auth-info"><?= trans('auth.register.language_intro') ?></p>

                    <div class="input-box input-box--select">
                        <ion-icon name="language-outline"></ion-icon>
                        <select id="register-lang" onchange="changeLanguage(this.value)">
                            <?php foreach (get_available_languages() as $lang): ?>
                                <option value="<?= htmlspecialchars($lang['code']) ?>"<?= $lang['code'] === get_locale() ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($lang['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <input type="submit" value="<?= trans('auth.register.continue') ?>">

                    <div class="register">
                        <a class="register-link" href="/app/login"><?= trans('auth.register.have_account') ?> <span><?= trans('auth.register.login_here') ?></span></a>
                    </div>
                </div>

                <!-- Step 2 — Account information -->
                <div class="auth-step" data-step="2">
                    <h2><?= trans('auth.register.title') ?></h2>

                    <div class="input-box">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input id="register-email" type="email" autocomplete="email" required placeholder=" ">
                        <label for="register-email"><?= trans('auth.register.email') ?></label>
                    </div>

                    <div class="input-box">
                        <ion-icon name="person-outline"></ion-icon>
                        <input id="register-username" type="text" autocomplete="username" required placeholder=" ">
                        <label for="register-username"><?= trans('auth.register.username') ?></label>
                    </div>

                    <div class="input-box">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input id="register-password" type="password" autocomplete="new-password" required placeholder=" ">
                        <label for="register-password"><?= trans('auth.register.password') ?></label>
                    </div>

                    <div class="input-box">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input id="register-password-confirm" type="password" autocomplete="new-password" required placeholder=" ">
                        <label for="register-password-confirm"><?= trans('auth.register.password_confirm') ?></label>
                    </div>

                    <p class="error" id="register-error"></p>

                    <input type="submit" id="register-submit" value="<?= trans('auth.register.create') ?>">

                    <div class="register">
                        <a class="register-link" href="/app/login"><?= trans('auth.register.have_account') ?> <span><?= trans('auth.register.login_here') ?></span></a>
                    </div>
                </div>

                <!-- Step 3 — Verification code -->
                <div class="auth-step" data-step="3">
                    <h2><?= trans('auth.register.verify_title') ?></h2>

                    <p class="auth-info">
                        <?= trans('auth.register.code_sent') ?><br>
                        <strong id="register-email-display"></strong>
                    </p>

                    <div class="input-box input-box--code">
                        <input id="register-code" type="text" inputmode="text" autocomplete="one-time-code" maxlength="8" required placeholder=" ">
                        <label for="register-code"><?= trans('auth.register.code_label') ?></label>
                    </div>

                    <p class="error" id="code-error"><?= trans('auth.register.code_incorrect') ?></p>

                    <input type="submit" id="code-submit" value="<?= trans('auth.register.validate') ?>">
                </div>

            </form>
        </div>
    </div>
</section>

<script src="/public/js/components/toast.js" type="text/javascript"></script>
<script src="/public/js/components/auth_transition.js" type="text/javascript"></script>
<script src="/public/js/pages/create_account.js" type="text/javascript"></script>
<script>
    let side_bar = document.getElementById('side-menu');
    side_bar.style.filter = 'saturate(15%) brightness(50%)';
</script>

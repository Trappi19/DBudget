
<!-- The register card can be swapped into this page (see auth_transition.js),
     so we load the register styles/scripts here too. They are inert until that
     markup is present: none of it applies to or runs against the login card. -->
<link rel="stylesheet" href="/public/styles/components/auth/auth.css">
<link rel="stylesheet" href="/public/styles/components/auth/create-account.css">
<link rel="stylesheet" href="/public/styles/components/popup/toast.css">

<section id="section-core">
    <div class="form-box">
        <div class="form-value">
            <form id="login-form" action="/app/login" method="get">
                <h2>Login</h2>
                <div class="input-box">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input id="email_id" type="email" name="input_email" autocomplete="username" required placeholder=" ">
                    <label for="email_id">Email</label>
                </div>
                <div class="input-box">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input id="password_id" type="password" name="input_password" autocomplete="current-password" required placeholder=" ">
                    <label for="password_id">Password</label>
                </div>
                <div class="forget">
                    <a href="#good-luck-bro">Forget Password?</a>
                </div>

                <?php if ($error == 1): ?>
                    <p class="error">
                        Email or password incorrect
                    </p>
                <?php elseif ($error == 2): ?>
                    <p class="success">
                        Password saved successfully
                    </p>
                <?php endif; ?>

                <input type="submit" value="Login">
                <div class="register">
                    <a class="register-link" href="/app/create-account">Don't have an account? <span class="register-link__accent">Create here</span></a>
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

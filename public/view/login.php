
<link rel="stylesheet" href="/public/styles/components/auth.css">

<section id="section-core">
    <div class="form-box">
        <div class="form-value">
            <form action="/app/login" method="post">
                <h2>Login</h2>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES) ?>">
                <div class="input-box">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input id="email_id" type="email" name="input_email" required placeholder=" ">
                    <label for="email_id">Email</label>
                </div>
                <div class="input-box">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input id="password_id" type="password" name="input_password" required placeholder=" ">
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
                    <p>Don't have an account? <a href="#">Call me</a></p>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    let side_bar = document.getElementById('side-menu');
    side_bar.style.filter = 'saturate(15%) brightness(50%)';
</script>

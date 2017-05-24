<?php
/** @var \Bfc\Core\View\Template $this */
?>

<div class="sample-main">
    <div class="registration-container">
        <h3>Passwort zurücksetzen</h3>
        <p class="">Geben sie jetzt ein neues Passwort für Ihr Konto ein.</p>

        <form method="post">
            <input type="hidden" name="action" value="recoveryPassword">

            <div>
                <input name="pass" value="" placeholder="Passwort eingeben" type="password">
            </div>

            <div>
                <input name="pass2" value="" placeholder="Passwort wiederholen" type="password">
            </div>

            <button>Passwort zurücksetzen</button>
        </form>
    </div>
</div>
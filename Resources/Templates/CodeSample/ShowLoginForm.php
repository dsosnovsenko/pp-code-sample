<?php
// Available embedded objects:
/** @var \Bfc\Core\View\Template $this */
/** @var \Bfc\Core\View\Partial $partial */

// Available embedded variables:
/** @var string $action */

// Custom assigned variables:
/** @var string $reCaptchaSiteKey */
/** @var array $values */
/** @var array $errors */
?>

<div class="sample-main">
    <p>
        Die Registrierung ist kostenfrei und ohne Verpflichtungen.
    </p>
    <div class="br"> </div>

    <div class="registration-container two-col">
        <h3>Registrierung</h3>
        <p class="sub-title">Ich möchte ein neues Konto anlegen.</p>

        <form method="post">
            <input type="hidden" name="action" value="registrationUser">

            <?php if (isset($errors['registration.address'])) {?>
                <div class="error"><?= $errors['registration.address'] ?></div>
            <?php } ?>
            <span class="two-elm <?= isset($errors['registration.address']) ? 'error' : '' ?>">
            <select class="" name="address">
                <option value="0">Anrede</option>
                <option value="1" <?= ($values['registration.address'] !== '1') ?: 'selected="true"' ?>>Herr</option>
                <option value="2" <?= ($values['registration.address'] !== '2') ?: 'selected="true"' ?>>Frau</option>
            </select>
            </span>

            <div>
                <?php if (isset($errors['registration.firstname'])) {?>
                    <div class="error"><?= $errors['registration.firstname'] ?></div>
                <?php } ?>
                <?php if (isset($errors['registration.lastname'])) {?>
                    <div class="error"><?= $errors['registration.lastname'] ?></div>
                <?php } ?>
                <span class="two-elm <?= isset($errors['registration.firstname']) ? 'error' : '' ?>"><input class="short-input-1" name="firstname" value="<?= $values['registration.firstname'] ?>" placeholder="Vorname"></span><span class="two-elm-next <?= isset($errors['registration.lastname']) ? 'error' : '' ?>"><input class="short-input-1 next-row-element-1" name="lastname" value="<?= $values['registration.lastname'] ?>" placeholder="Nachname"></span>
            </div>

            <div>
                <?php if (isset($errors['registration.firm'])) {?>
                    <div class="error"><?= $errors['registration.firm'] ?></div>
                <?php } ?>
                <span class="<?= isset($errors['registration.firm']) ? 'error' : '' ?>">
                    <input name="firm" value="<?= $values['registration.firm'] ?>" placeholder="Firma">
                </span>
            </div>

            <div>
                <?php if (isset($errors['registration.phone'])) {?>
                    <div class="error"><?= $errors['registration.phone'] ?></div>
                <?php } ?>
                <span class="<?= isset($errors['registration.phone']) ? 'error' : '' ?>">
                    <input name="phone" value="<?= $values['registration.phone'] ?>" placeholder="Telefon">
                </span>
            </div>

            <div>
                <?php if (isset($errors['registration.email'])) {?>
                    <div class="error"><?= $errors['registration.email'] ?></div>
                <?php } ?>
                <span class="<?= isset($errors['registration.email']) ? 'error' : '' ?>">
                    <input name="email" value="<?= $values['registration.email'] ?>" placeholder="E-Mail">
                </span>
            </div>

            <div>
                <?php if (isset($errors['registration.pass'])) {?>
                    <div class="error"><?= $errors['registration.pass'] ?></div>
                <?php } ?>
                <span class="<?= isset($errors['registration.pass']) ? 'error' : '' ?>">
                    <input name="pass" value="" placeholder="Passwort" type="password">
                </span>
            </div>

            <div>
                <?php if (isset($errors['registration.accept'])) {?>
                    <div class="error"><?= $errors['registration.accept'] ?></div>
                <?php } ?>
                <label for="accept"><span class="checkbox"><input id="accept" name="accept" value="1" type="checkbox"></span> Ich akzeptiere die <a href="#" target="_blank">Nutzungsbedingungen</a></label>
            </div>

            <?php if (isset($errors['registration.recaptcha'])) {?>
                <div class="error"><?= $errors['registration.recaptcha'] ?></div>
            <?php } ?>
            <?php $this->partial->render('CodeSample/ReCaptcha', ['siteKey' => $reCaptchaSiteKey]); ?>

            <button>Registrieren</button>
        </form>
    </div>

    <div class="login-container two-col-next">
        <h3>Anmeldung</h3>
        <p class="sub-title">Ich habe bereits ein Konto.</p>

        <form method="post">
            <input type="hidden" name="action" value="loginUser">

            <div>
                <?php if (isset($errors['login.email'])) {?>
                    <div class="error"><?= $errors['login.email'] ?></div>
                <?php } ?>
                <span class="<?= isset($errors['login.email']) ? 'error' : '' ?>">
                    <input name="email" value="<?= $values['login.email'] ?>" placeholder="E-Mail">
                </span>
            </div>

            <div>
                <?php if (isset($errors['login.pass'])) {?>
                    <div class="error"><?= $errors['login.pass'] ?></div>
                <?php } ?>
                <span class="<?= isset($errors['login.pass']) ? 'error' : '' ?>">
                    <input name="pass" value="" placeholder="Passwort" type="password">
                </span>
            </div>

            <div>
                <label for="stay_login"><input id="stay_login" name="stay_login" value="1" type="checkbox"> Angemeldet bleiben</label>
            </div>

            <button>Anmelden</button>
        </form>

        <div class="br"> </div>
        <a class="js-pass-recovery" href="#">Ich habe mein Passwort vergessen</a>

        <div class="pass-recovery-container" style="display: <?= isset($errors['recovery.email']) ? 'block' : 'none'?>;">
            <h3>Passwort vergessen</h3>
            <p class="sub-title">Bitte geben Sie Ihre E-Mail-Adressen ein.</p>

            <form method="post">
                <input type="hidden" name="action" value="forgotPassword">

                <div>
                    <?php if (isset($errors['recovery.email'])) {?>
                        <div class="error"><?= $errors['recovery.email'] ?></div>
                    <?php } ?>
                    <span class="<?= isset($errors['recovery.email']) ? 'error' : '' ?>">
                        <input name="email" value="<?= $values['recovery.email'] ?>" placeholder="E-Mail">
                    </span>
                </div>

                <button>Passwort zurücksetzen</button>
            </form>
        </div>
    </div>
</div>

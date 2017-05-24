<?php
// Available embedded objects:
/** @var \Bfc\Core\View\Partial $partial */

// Available embedded variables:
/** @var string $action */

// Custom assigned variables:
/** @var string $siteKey */
?>

<?php if ($siteKey !== '') { ?>
    <div class="br"> </div>
    <div class="g-recaptcha" data-sitekey="<?= $siteKey ?>"></div>
    <div class="br"> </div>
<?php } ?>

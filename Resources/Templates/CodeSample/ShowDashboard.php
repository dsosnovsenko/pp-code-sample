<?php
// Available embedded objects:
/** @var \Bfc\Core\View\Template $this */
/** @var \Bfc\Core\View\Partial $partial */

// Available embedded variables:
/** @var string $action */

// Custom assigned variables:
/** @var \Pp\CodeSample\Model\User $user */
?>

<div class="sample-main">
    <?php $this->partial->render('CodeSample/ShowDashboard/Menu', ['action' => $action]); ?>

    <p>Ihr Konto ist angemeldet auf:</p>
    <div><?= $user->getFirm() ?></div>
    <div><?= $user->getTranslatedAddress() .' ' . $user->getFirstName() . ' ' . $user->getLastName() ?></div>
    <div>Telefon: <?= $user->getPhone() ?></div>
    <div>E-Mail: <?= $user->getEmail() ?></div>

</div>

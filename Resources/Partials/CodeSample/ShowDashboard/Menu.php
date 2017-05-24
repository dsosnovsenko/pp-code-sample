<?php
// Available embedded objects:
/** @var \Bfc\Core\View\Partial $partial */

// Available embedded variables:
/** @var string $action */

// Custom assigned variables:
?>

<div class="menu">
    <a class="menu-link" href="?action=showDashboard"><button class="<?= ($action != 'showDashboard') ?: 'active'?>">Dashboard</button></a>
    <a class="menu-link next" href="?action=logoutUser"><button class="<?= ($action != 'logoutUser') ?: 'active'?>">Abmelden</button></a>
</div>
<br>

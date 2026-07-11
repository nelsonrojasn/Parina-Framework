<?php
use Parina\Core\View;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Parina Framework - The Silence of the Altiplano</title>
    <link rel="stylesheet" href="/assets/css/parina.css">
</head>
<body>

<header>
    <nav>
        <a href="/">Home</a>
        <?php if (file_exists($config->getDbPath()) && !$auth->isLoggedIn()) : ?>
            <a href="/login">Login</a>
        <?php endif; ?>
        <?php if ($auth->isLoggedIn()) : ?>
            <a href="/admin/home/<?= $cipher->encryptUrl('admin/home');?>">Admin</a>
            <a href="/admin/users/<?= $cipher->encryptUrl('admin/users');?>">Users</a>
            <a href="/logout/<?= $cipher->encryptUrl('logout');?>">Logout</a>
        <?php endif; ?>
        <a href="/about">About</a>
    </nav>
</header>


<main>
    <?= $content ?>
</main>

<?php View::partial("footer"); ?>

</body>
</html>

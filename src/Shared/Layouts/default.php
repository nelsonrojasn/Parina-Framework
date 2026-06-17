<?php
use Parina\Shared\Services\Auth;
use Parina\Shared\Security\Cipher;
use Parina\Core\Config;
use Parina\Core\View;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Parina Framework - The Silence of the Altiplano</title>
    <link rel="stylesheet" href="./assets/css/simple.css">
</head>
<body>

<header>
    <nav>
        <a href="/">Home</a>
        <?php if (file_exists(Config::getDbPath()) && !Auth::isLoggedIn()) : ?>
            <a href="/login">Login</a>
        <?php endif; ?>
        <?php if (Auth::isLoggedIn()) : ?>
            <a href="/do?r=<?= Cipher::encryptUrl('admin/home');?>">Admin</a>
            <a href="/do?r=<?= Cipher::encryptUrl('admin/users');?>">Users</a>
            <a href="/do?r=<?= Cipher::encryptUrl('logout');?>">Logout</a>
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

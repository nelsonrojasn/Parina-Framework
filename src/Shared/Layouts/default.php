<?php
use Parina\Core\View;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Parina Framework - El Silencio del Altiplano</title>
    <link rel="stylesheet" href="/assets/css/parina.css">
</head>
<body>

<header>
    <nav>
        <a href="/">Inicio</a>
        <?php if (file_exists($config->getDbPath()) && !$auth->isLoggedIn()) : ?>
            <a href="/login">Iniciar Sesión</a>
        <?php endif; ?>
        <?php if ($auth->isLoggedIn()) : ?>
            <a href="/admin/home/<?= $cipher->encryptUrl('admin/home');?>">Admin</a>
            <a href="/admin/users/<?= $cipher->encryptUrl('admin/users');?>">Usuarios</a>
            <a href="/logout/<?= $cipher->encryptUrl('logout');?>">Cerrar Sesión</a>
        <?php endif; ?>
        <a href="/about">Acerca de</a>
    </nav>
</header>


<main>
    <?= $content ?>
</main>

<?php View::partial("footer"); ?>

</body>
</html>

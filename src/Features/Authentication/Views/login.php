<?php
    use Parina\Shared\Security\Cipher;
    use Parina\Shared\Security\Csrf;
?>
<h1>Parina Framework</h1>
<form action="/login" method="post">
    <input type="hidden" name="_csrf" value="<?= Csrf::token(); ?>" />
    <div>
        <label for="user">User</label>
        <input type="text" name="user" id="user" required autofocus />
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required />
    </div>
    <div>
        <button type="submit" class="btn btn-primary">Send</button>
    </div>
    </form>


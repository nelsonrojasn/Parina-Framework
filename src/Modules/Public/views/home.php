<h1>Welcome to Parina Framework</h1>
<p>A solid and minimalist foundation for your web projects.</p>

<p>
Parina offers you the essential tools so you have total control over your code.<br>
Designed to be lightweight, clear, and easy to understand from the very first moment.
</p>

<h3>Getting Started</h3>
<ul>
  <li>Edit your routes in <code>public/index.php</code></li>
  <li>Create a handler in <code>src/Modules/Public/</code></li>
  <li>Render a view from <code>Views/</code></li>
  <li>Start the server:
    <pre>php -S localhost:8000 -t public</pre>
  </li>
</ul>

<p>
We hope you enjoy the experience of building on a clean and efficient ground.
</p>

<?php if (!$db_exists): ?>
    <div style="background: #fff3cd; color: #856404; padding: 1rem; border: 1px solid #ffeeba; border-radius: 4px; margin: 1rem 0;">
        <h3>⚙️ System Configuration</h3>
        <p>To start using persistence features, it is necessary to initialize the database.</p>
        
        <?php if ($setup_allowed): ?>
            <p>Everything is ready to begin:</p>
            <a href="/setup" style="display: inline-block; padding: 0.5rem 1rem; background: #856404; color: white; text-decoration: none; border-radius: 4px;">
                Run Installer (Setup)
            </a>
        <?php else: ?>
            <p style="font-size: 0.9rem;">
                ⚠️ To initialize the system, please change the configuration in 
                <code>Config::allowSetup();</code> 
                in your <code>Parina\Core\Config.php</code> file.
            </p>
        <?php endif; ?>
    </div>
<?php else: ?>
  <div style="background: #f3ffcd; color: #318504; padding: 1rem; border: 1px solid #ffeeba; border-radius: 4px; margin: 1rem 0;">
        <strong>✅ The database is configured and ready to operate.</strong>
  </div>
<?php endif; ?>

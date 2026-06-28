# Deployment

Keep public-facing files separated from application code to ensure a safe installation.

**Recommended steps**

1. Copy the public files to your webserver document root (example: `/var/www/html` or the root for a subdomain):

```bash
cp -R public/* /var/www/html/
```

*Note: Make sure to copy the hidden `.htaccess` file as well. In some operating systems or shell configurations, wildcard copies like `public/*` might miss hidden files. You can copy it explicitly:*

```bash
cp public/.htaccess /var/www/html/
```

2. Copy the rest of the project files outside the public site (example: `/var/www/parina`):

```bash
mkdir -p /var/www/parina
# From the project repository root; exclude the public folder
rsync -a --exclude='public' ./ /var/www/parina/
```

3. Parina Framework has zero production dependencies and does not require Composer. The custom `src/autoload.php` handles class loading automatically out-of-the-box.

4. Give the Apache (webserver) group ownership of the database folder so the server can read/write as needed (example for Debian/Ubuntu):

```bash
sudo chown -R www-data:www-data /var/www/parina/src/Db
```

Notes:
- If you prefer, instead of copying files you can point your virtual host DocumentRoot to the `public` folder inside your project (for example `/var/www/parina/public`).
- Adjust commands and user/group names to match your distribution and hosting setup.
- Secure any environment or configuration files (do not expose them inside the public webroot).

Cheers


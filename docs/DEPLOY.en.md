# Deployment

Keep publicly accessible files separated from application code to ensure a secure installation.

**Recommended Steps**

1. Copy public files to your web server's document root (e.g., `/var/www/html` or the root of a subdomain):

```bash
cp -R public/* /var/www/html/
```

*Note: Make sure to copy the hidden `.htaccess` file as well. In some operating systems or terminal configurations, copying with wildcards like `public/*` may omit hidden files. You can copy it explicitly:*

```bash
cp public/.htaccess /var/www/html/
```

2. Copy the rest of the project files outside of the public directory (e.g., `/var/www/parina`):

```bash
mkdir -p /var/www/parina
# From the project repository root; exclude the public folder
rsync -a --exclude='public' ./ /var/www/parina/
```

3. Parina Framework has no production dependencies and does not require Composer. The custom class loader `src/autoload.php` handles class loading automatically out of the box.

4. Grant the Apache (web server) group ownership of the database folder so the server can read/write as needed (example for Debian/Ubuntu):

```bash
sudo chown -R www-data:www-data /var/www/parina/src/Db
```

Notes:
- If preferred, instead of copying files, you can point your virtual host's DocumentRoot to the `public` folder inside your project (for example, `/var/www/parina/public`).
- Adjust commands and user/group names to match your distribution and hosting setup.
- Protect any environment or configuration files (do not expose them inside the public web root directory).

Best regards!

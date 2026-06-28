# Bereitstellung

Halten Sie öffentlich zugängliche Dateien vom Anwendungscode getrennt, um eine sichere Installation zu gewährleisten.

**Empfohlene Schritte**

1. Kopieren Sie die öffentlichen Dateien in das Dokumentenverzeichnis Ihres Webservers (Beispiel: `/var/www/html` oder das Stammverzeichnis für eine Subdomain):

```bash
cp -R public/* /var/www/html/
```

*Hinweis: Stellen Sie sicher, dass Sie auch die versteckte Datei `.htaccess` kopieren. In einigen Betriebssystemen oder Terminalkonfigurationen werden versteckte Dateien bei Kopiervorgängen mit Platzhaltern wie `public/*` möglicherweise ausgelassen. Sie können sie explizit kopieren:*

```bash
cp public/.htaccess /var/www/html/
```

2. Kopieren Sie die restlichen Projektdateien außerhalb der öffentlichen Website (Beispiel: `/var/www/parina`):

```bash
mkdir -p /var/www/parina
# Vom Projekt-Repository-Stammverzeichnis aus; schließt den Ordner public aus
rsync -a --exclude='public' ./ /var/www/parina/
```

3. Installieren Sie PHP-Abhängigkeiten mit Composer im Projekt-Stammverzeichnis. Stellen Sie sicher, dass Composer installiert ist, und führen Sie den Befehl als Projekteigentümer (nicht als `root`) aus. Verwenden Sie für die Produktion die unten gezeigten empfohlenen Flags, um Entwicklungspakete zu überspringen und den Autoloader zu optimieren.

```bash
# wechseln Sie in das Projekt-Stammverzeichnis (in dem sich composer.json befindet)
cd /var/www/parina
# Entwicklung: installieren Sie alle Abhängigkeiten
# No composer needed
# Produktion: Entwicklungspakete überspringen und Autoloader optimieren
# No composer needed --no-dev --optimize-autoloader
```

4. Übertragen Sie dem Apache- (Webserver-) Gruppe die Eigentumsrechte am Datenbankordner, damit der Server nach Bedarf lesen/schreiben kann (Beispiel für Debian/Ubuntu):

```bash
sudo chown -R www-data:www-data /var/www/parina/src/Db
```

Notes:
- Wenn Sie es vorziehen, können Sie das DocumentRoot Ihres virtuellen Hosts auf den Ordner `public` in Ihrem Projekt verweisen (zum Beispiel `/var/www/parina/public`), anstatt Dateien zu kopieren.
- Passen Sie die Befehle sowie die Benutzer- und Gruppennamen an Ihre Distribution und Ihr Hosting-Setup an.
- Sichern Sie alle Umgebungs- oder Konfigurationsdateien (legen Sie diese nicht im öffentlichen Web-Stammverzeichnis offen).

Viele Grüße

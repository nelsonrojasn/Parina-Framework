# Despliegue (Churanapaq)

Llikachapa qillqankunata allin amachasqa churanaykipaqqa, lliw runapaq kaq p'anqakunata sapaqchay.

**Allin thakhikuna**

1. Runapaq p'anqakunata webserver DocumentRoot ukhuman churaqpuy (ejemplo: `/var/www/html` utaq subdomain DocumentRoot):

```bash
cp -R public/* /var/www/html/
```

*Yuyariy: `.htaccess` pakashqa p'anqatapas churanaykipuni. Wakin llikachakunapiqa `public/*` nisqa mana rikunqa pakashqa p'anqakunata. Chiqanta churanaykipaq kayta ruray:*

```bash
cp public/.htaccess /var/www/html/
```

2. Llikachapa qaqlla qillqankunata lliw runa mana riknan ukhuman churaqpuy (ejemplo: `/var/www/parina`):

```bash
mkdir -p /var/www/parina
# Qallariy repositoriomanta; "public" ukhuta ama churaychu
rsync -a --exclude='public' ./ /var/www/parina/
```

3. Parina Frameworkqa manam dependenciakunayuqchu hinaspa manam Composerta necesitanchu. Custom `src/autoload.php` autoloaderqa kikinmantam clasekunata load-anqa.

4. Apache (webserver) qutupaman Database ukhupa dueñon kayta quy, server leeyta utaq qillqaytapas atinanpaq (ejemplo Debian/Ubuntu pachapaq):

```bash
sudo chown -R www-data:www-data /var/www/parina/src/Db
```

Yuyariykuna:
- Munaspaqa, ama copiaspa, virtual host DocumentRoot-ta churanaykipaq `public` ukhuman chiqanyachiwaq (ejemplo: `/var/www/parina/public`).
- Kamachiykunata, ukhukunatapas, hosting wakichisqaykiman hina allichay.
- Lliw amachana utaq configuración p'anqakunata sumaqta wisq'ay (ama lliw runapaq kaq DocumentRoot ukhupi saqiychu).

Allillan kachun!

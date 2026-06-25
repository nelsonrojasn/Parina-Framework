# Distribuzione

Mantieni i file accessibili al pubblico separati dal codice dell'applicazione per garantire un'installazione sicura.

**Passaggi consigliati**

1. Copia i file pubblici nella directory radice dei documenti del tuo server web (esempio: `/var/www/html` o la radice di un sottodominio):

```bash
cp -R public/* /var/www/html/
```

*Nota: Assicurati di copiare anche il file nascosto `.htaccess`. In alcuni sistemi operativi o configurazioni di terminale, le copie con caratteri jolly come `public/*` potrebbero omettere i file nascosti. Puoi copiarlo esplicitamente:*

```bash
cp public/.htaccess /var/www/html/
```

2. Copia il resto dei file del progetto al di fuori del sito pubblico (esempio: `/var/www/parina`):

```bash
mkdir -p /var/www/parina
# Dalla directory radice del repository del progetto; esclude la cartella public
rsync -a --exclude='public' ./ /var/www/parina/
```

3. Installa le dipendenze PHP con Composer nella directory radice del progetto. Assicurati che Composer sia installato ed esegui il comando come proprietario del progetto (non come `root`). Per la produzione, utilizza i flag consigliati mostrati di seguito per saltare i pacchetti di sviluppo e ottimizzare l'autoloader.

```bash
# passa alla directory radice del progetto (dove si trova composer.json)
cd /var/www/parina
# sviluppo: installa tutte le dipendenze
composer install
# produzione: salta i pacchetti di sviluppo e ottimizza l'autoloader
composer install --no-dev --optimize-autoloader
```

4. Assegna al gruppo di Apache (server web) la proprietà della cartella del database in modo che il server possa leggere/scrivere come richiesto (esempio per Debian/Ubuntu):

```bash
sudo chown -R www-data:www-data /var/www/parina/src/Db
```

Notes:
- Se preferisci, invece di copiare i file puoi indirizzare il DocumentRoot del tuo host virtuale alla cartella `public` all'interno del tuo progetto (ad esempio `/var/www/parina/public`).
- Regola i comandi e i nomi di utenti/gruppi per farli corrispondere alla tua distribuzione e configurazione di hosting.
- Proteggi qualsiasi file di ambiente o di configurazione (non esporli all'interno della directory radice web pubblica).

Un saluto

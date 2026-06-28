# Déploiement

Séparez les fichiers accessibles au public du code de l'application pour garantir une installation sécurisée.

**Étapes recommandées**

1. Copiez les fichiers publics vers la racine des documents de votre serveur web (exemple : `/var/www/html` ou la racine d'un sous-domaine) :

```bash
cp -R public/* /var/www/html/
```

*Note : Assurez-vous de copier également le fichier caché `.htaccess`. Dans certains systèmes d'exploitation ou configurations de terminal, les copies génériques comme `public/*` peuvent omettre les fichiers cachés. Vous pouvez le copier explicitement :*

```bash
cp public/.htaccess /var/www/html/
```

2. Copiez le reste des fichiers du projet en dehors du site public (exemple : `/var/www/parina`) :

```bash
mkdir -p /var/www/parina
# Depuis la racine du dépôt du projet ; exclut le dossier public
rsync -a --exclude='public' ./ /var/www/parina/
```

3. Installez les dépendances PHP avec Composer à la racine du projet. Assurez-vous que Composer est installé et exécutez la commande en tant que propriétaire du projet (pas en tant que `root`). Pour la production, utilisez les options recommandées ci-dessous pour ignorer les packages de développement et optimiser l'autoloader.

```bash
# accédez à la racine du projet (là où se trouve composer.json)
cd /var/www/parina
# développement : installez toutes les dépendances
# No composer needed
# production : ignorez les packages de développement et optimisez l'autoloader
# No composer needed --no-dev --optimize-autoloader
```

4. Donnez au groupe d'Apache (serveur web) la propriété du dossier de base de données afin que le serveur puisse lire/écrire selon les besoins (exemple pour Debian/Ubuntu) :

```bash
sudo chown -R www-data:www-data /var/www/parina/src/Db
```

Notes :
- Si vous préférez, au lieu de copier les fichiers, vous pouvez faire pointer le DocumentRoot de votre hôte virtuel vers le dossier `public` de votre projet (par exemple `/var/www/parina/public`).
- Ajustez les commandes et les noms d'utilisateurs/groupes pour correspondre à votre distribution et à votre configuration d'hébergement.
- Sécurisez tous les fichiers d'environnement ou de configuration (ne les exposez pas à l'intérieur de la racine web publique).

Cordialement

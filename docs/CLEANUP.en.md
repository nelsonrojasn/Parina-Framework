# Cleanup and Reset (Remove Demonstration Code)

Parina Framework includes a complete demonstration application (modules, routes, tests, database) to showcase its features.

To remove all demonstration files and reset the framework to a clean, empty state, run:

```bash
php bin/cleanup.php
```

### What this script does:
1. **Removes Modules**: Deletes the `src/Modules/Admin/` and `src/Modules/Private/` directories.
2. **Removes Public Demo Handlers**: Deletes `LoginFormHandler.php`, `LoginCheckHandler.php`, `AboutHandler.php`, and `AutoPurchaseHandler.php` inside `src/Modules/Public/`.
3. **Removes Demo Views**: Deletes `about.php` and `login.php` in `src/Modules/Public/Views/`.
4. **Removes Demo Tests**: Deletes the test suites in `tests/Handlers/` associated with the removed handlers.
5. **Removes the Database**: Deletes the local SQLite database file `src/Db/app.sqlite`.
6. **Resets Routes**: Overwrites `config/routes.php` to only contain `/` and `/setup`, and clears `routes.csv` leaving only the header.

### Automation (Skip confirmation)
To skip the confirmation prompt:
```bash
php bin/cleanup.php --force
```

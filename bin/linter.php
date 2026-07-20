<?php

/**
 * Backward-compatible wrapper for Parina Guardian CLI Tool.
 * Deprecated: Please use 'php bin/guardian.php' instead.
 */

fwrite(STDERR, "\033[1;33m⚠️  DEPRECATION NOTICE: 'php bin/linter.php' has evolved to 'php bin/guardian.php'. Redirecting...\033[0m\n\n");

require __DIR__ . '/guardian.php';

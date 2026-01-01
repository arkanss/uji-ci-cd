<?php

/**
 * Load Docker Swarm secrets into environment variables.
 *
 * This file processes environment variables that point to Docker secret files
 * (typically in /run/secrets/) and replaces them with the actual secret content.
 */

foreach ($_ENV as $key => $value) {
    // Check if the value points to a file in /run/secrets/
    if (is_string($value) && str_starts_with($value, '/run/secrets/') && file_exists($value)) {
        // Read the secret file content and remove trailing whitespace
        $secretContent = trim(file_get_contents($value));

        // Update the environment variable with the actual secret content
        $_ENV[$key] = $secretContent;
        $_SERVER[$key] = $secretContent;
        putenv("{$key}={$secretContent}");
    }
}

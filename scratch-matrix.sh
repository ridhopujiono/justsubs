#!/bin/bash
set -e

# Define Matrix
PHP_VERSIONS=("8.2" "8.3" "8.4")
TESTBENCH_VERSIONS=("^9.0" "^10.0" "^11.0")
declare -a LARAVEL_MAP
LARAVEL_MAP[9]="Laravel 11"
LARAVEL_MAP[10]="Laravel 12"
LARAVEL_MAP[11]="Laravel 13"

echo "Running Matrix..."
for PHP in "${PHP_VERSIONS[@]}"; do
    for TB in "${TESTBENCH_VERSIONS[@]}"; do
        # Extract major version
        TB_MAJOR=${TB:1:2}
        TB_MAJOR=${TB_MAJOR%%.*}
        LARAVEL=${LARAVEL_MAP[$TB_MAJOR]}
        echo "==================================="
        echo "Testing PHP $PHP with $LARAVEL"
        echo "==================================="
        
        # Test if this combo is valid. We'll run composer inside Docker.
        # Use an ephemeral container
        cp composer.json composer.json.bak
        cp composer.lock composer.lock.bak
        
        if docker run --rm -v $(pwd):/app -w /app composer:$PHP bash -c "composer require --dev orchestra/testbench:\"$TB\" -W --no-interaction && composer test"; then
            echo "[RESULT] PHP $PHP + $LARAVEL : PASS"
        else
            echo "[RESULT] PHP $PHP + $LARAVEL : FAIL"
        fi
        
        mv composer.json.bak composer.json
        mv composer.lock.bak composer.lock
    done
done

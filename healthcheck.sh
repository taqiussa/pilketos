#!/bin/sh
set -e

# Cek Laravel /health route
if ! curl -fs http://127.0.0.1/health | grep -q '"status":"ok"'; then
    echo "Laravel health route failed"
    exit 1
fi

# Cek storage & bootstrap/cache permission
for dir in /var/www/html/storage /var/www/html/bootstrap/cache; do
    if [ ! -d "$dir" ] || [ ! -w "$dir" ]; then
        echo "Directory $dir not writable"
        exit 1
    fi
done

echo "All health checks passed"
exit 0

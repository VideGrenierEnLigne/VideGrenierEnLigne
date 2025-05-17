# Aller dans le dossier du script, peu importe où il est lancé
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

if [ -d "prod-fetch" ]; then
    echo "prod-fetch folder found. Stopping Docker containers..."

    docker compose -f ./docker-compose.yml down
    if [ $? -eq 0 ]; then
        echo "Docker containers stopped successfully."
    else
        echo "Error: Failed to stop Docker containers."
        exit 1
    fi

    echo "Removing prod-fetch folder..."
    rm -rf prod-fetch
    if [ $? -eq 0 ]; then
        echo "prod-fetch folder removed successfully."
    else
        echo "Error: Failed to remove prod-fetch folder."
        exit 1
    fi

    echo "Cleanup done."
else
    echo "prod-fetch folder not found. Production does not seem to be running."
fi

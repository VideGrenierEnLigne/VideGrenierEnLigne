#!/bin/bash

# Aller dans le dossier du script, peu importe où il est lancé
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Charger les variables d'environnement
set -o allexport
source prod.env
set +o allexport

PAT=${GH_TOKEN}
gh_url="https://${PAT}@github.com/VideGrenierEnLigne/VideGrenierEnLigne.git"

# Cloner ou mettre à jour le repo
if [ -d "prod-fetch" ]; then
    echo "Repo already fetch, pulling latest changes on main..."
    cd ./prod-fetch/VideGrenierEnLigne
    git pull origin main
    if [ $? -eq 0 ]; then
        echo "Repo successfully updated"
    else
        echo "Error : Repo not updated, resetting..."
        cd "$SCRIPT_DIR"
        rm -rf prod-fetch
        exit 1
    fi
else
    mkdir prod-fetch
    cd prod-fetch
    git clone -b main "$gh_url"
    if [ -d "VideGrenierEnLigne" ]; then
        echo "Repo successfully cloned"
        cd VideGrenierEnLigne
    else
        echo "Error : Repo not cloned"
        cd "$SCRIPT_DIR"
        rm -rf prod-fetch
        exit 1
    fi
fi

# Installer les dépendances npm et composer
echo "Installing npm dependencies..."
npm install
if [ $? -ne 0 ]; then
    echo "Error: npm install failed"
    exit 1
fi

echo "Installing composer dependencies..."
composer install
if [ $? -ne 0 ]; then
    echo "Error: composer install failed"
    exit 1
fi

cd "$SCRIPT_DIR"

# Lancer les containers Docker
echo "Starting Docker containers..."
docker compose -f ./docker-compose.yml up -d --build
if [ $? -eq 0 ]; then
    clear
    echo "Docker containers successfully started"
    docker ps
else
    echo "Error : Docker containers not started"
    exit 1
fi

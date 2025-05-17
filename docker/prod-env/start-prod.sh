#!/bin/bash

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Go to the script directory, wherever it's launched from
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Load environment variables
set -o allexport
source prod.env
set +o allexport

PAT=${GH_TOKEN}
gh_url="https://${PAT}@github.com/VideGrenierEnLigne/VideGrenierEnLigne.git"

clear
echo -e "${CYAN}→ Cloning or updating the repo...${NC}"

# Clone or update the repo
if [ -d "prod-fetch" ]; then
    cd ./prod-fetch/VideGrenierEnLigne
    clear
    echo -e "${CYAN}→ Repo already fetched, pulling latest changes on main...${NC}"
    git pull origin main
    if [ $? -eq 0 ]; then
        clear
        echo -e "${GREEN}✓ Repo updated successfully${NC}"
    else
        clear
        echo -e "${RED}✗ Error: repo update failed, resetting...${NC}"
        cd "$SCRIPT_DIR"
        rm -rf prod-fetch
        exit 1
    fi
else
    mkdir prod-fetch
    cd prod-fetch
    clear
    echo -e "${CYAN}→ Cloning main branch...${NC}"
    git clone -b main $gh_url
    if [ -d "VideGrenierEnLigne" ]; then
        clear
        echo -e "${GREEN}✓ Repo cloned successfully${NC}"
        cd VideGrenierEnLigne
    else
        clear
        echo -e "${RED}✗ Error: repo clone failed${NC}"
        cd "$SCRIPT_DIR"
        rm -rf prod-fetch
        exit 1
    fi
fi

clear
echo -e "${CYAN}→ Copying .env file to avoid breaking production...${NC}"

# Copy the .env file to avoid breaking prod
ENV_SRC_PATH="$SCRIPT_DIR/../../.env"
ENV_DEST_PATH="$SCRIPT_DIR/prod-fetch/VideGrenierEnLigne/.env"

if [ -f "$ENV_SRC_PATH" ]; then
    cp "$ENV_SRC_PATH" "$ENV_DEST_PATH"
    clear
    echo -e "${GREEN}✓ .env file copied from $ENV_SRC_PATH to $ENV_DEST_PATH${NC}"
else
    clear
    echo -e "${YELLOW}⚠️  .env file not found at $ENV_SRC_PATH, skipping copy${NC}"
fi

clear
echo -e "${CYAN}→ Installing npm dependencies...${NC}"
npm install
if [ $? -ne 0 ]; then
    clear
    echo -e "${RED}✗ Error: npm install failed${NC}"
    exit 1
fi

clear
echo -e "${CYAN}→ Installing composer dependencies...${NC}"
composer update
if [ $? -ne 0 ]; then
    clear
    echo -e "${RED}✗ Error: composer update failed${NC}"
    exit 1
fi

composer install
if [ $? -ne 0 ]; then
    clear
    echo -e "${RED}✗ Error: composer install failed${NC}"
    exit 1
fi

cd "$SCRIPT_DIR"

clear
echo -e "${CYAN}→ Starting Docker containers...${NC}"
docker compose -f ./docker-compose.yml up -d --build
if [ $? -eq 0 ]; then
    clear
    echo -e "${GREEN}✓ Production Environment available at http://localhost:8080${NC}"
else
    clear
    echo -e "${RED}✗ Error: failed to start Docker containers${NC}"
    exit 1
fi

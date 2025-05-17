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

clear
if [ -d "prod-fetch" ]; then
    echo -e "${CYAN}→ 'prod-fetch' folder found. Stopping Docker containers...${NC}"

    docker compose -f ./docker-compose.yml down
    if [ $? -eq 0 ]; then
        clear
        echo -e "${GREEN}✓ Docker containers stopped successfully.${NC}"
    else
        clear
        echo -e "${RED}✗ Error: Failed to stop Docker containers.${NC}"
        exit 1
    fi

    echo -e "${CYAN}→ Removing 'prod-fetch' folder...${NC}"
    rm -rf prod-fetch
    if [ $? -eq 0 ]; then
        clear
        echo -e "${GREEN}✓ 'prod-fetch' folder removed successfully.${NC}"
    else
        clear
        echo -e "${RED}✗ Error: Failed to remove 'prod-fetch' folder.${NC}"
        exit 1
    fi

    echo -e "${GREEN}✓ Cleanup done.${NC}"
else
    clear
    echo -e "${YELLOW}⚠️  'prod-fetch' folder not found. Production does not seem to be running.${NC}"
fi

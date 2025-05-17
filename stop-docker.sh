#!/bin/bash

GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}What do you want to stop?${NC}"
echo -e "${GREEN}1) prod${NC}"
echo -e "${GREEN}2) dev environment${NC}"
echo -e "${GREEN}3) both${NC}"
read choice

case "$choice" in
  1)
    clear
    echo -e "${GREEN}Stopping production...${NC}"
    cd ./docker/prod-env || { echo -e "${RED}Prod folder not found${NC}"; exit 1; }
    ./stop-prod.sh
    ;;
  2)
    clear
    echo -e "${GREEN}Stopping development environment...${NC}"
    cd ./docker/dev-env || { echo -e "${RED}Dev folder not found${NC}"; exit 1; }
    ./stop-dev.sh
    ;;
  3)
    clear
    echo -e "${GREEN}Stopping production and development environments...${NC}"
    cd ./docker/prod-env || { echo -e "${RED}Prod folder not found${NC}"; exit 1; }
    ./stop-prod.sh

    cd ../dev-env || { echo -e "${RED}Dev folder not found${NC}"; exit 1; }
    ./stop-dev.sh

    clear
    echo -e "${GREEN}✓ Production environment stopped.${NC}"
    echo -e "${GREEN}✓ Development environment stopped.${NC}"
    echo -e "${GREEN}✓ Cleanup done.${NC}"
    ;;
  *)
    clear
    echo -e "${RED}Invalid choice. Please select: prod, dev environment, or both${NC}"
    exit 1
    ;;
esac

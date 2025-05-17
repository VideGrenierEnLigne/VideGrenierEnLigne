GREEN='\033[0;32m'
NC='\033[0m'

clear
echo -e "${GREEN}→ Stopping the development environment...${NC}"
docker-compose down

clear
echo -e "${GREEN}→ Development environment stopped.${NC}"

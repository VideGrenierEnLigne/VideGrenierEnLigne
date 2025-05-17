GREEN='\033[0;32m'
NC='\033[0m'

clear
echo -e "${GREEN}→ Starting the development environment...${NC}"
docker-compose up -d --build

clear
echo -e "${GREEN}→ Development environment available at http://localhost:8081/${NC}"
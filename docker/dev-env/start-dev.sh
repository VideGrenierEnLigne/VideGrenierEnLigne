GREEN='\033[0;32m'
NC='\033[0m' 

echo -e "${GREEN}→ Lancement de l'environnement de développement...${NC}"
docker-compose up -d --build

echo -e "${GREEN}→ Environnement de développement disponible sur http://localhost:8080/${NC}"
docker ps
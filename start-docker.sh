echo "What do you want to start?"
echo "1) prod"
echo "2) dev environment"
echo "3) both"
read choice

case "$choice" in
  1)
    echo "Starting production..."
    cd ./docker/prod-env || { echo "Prod folder not found"; exit 1; }
    ./start-prod.sh
    ;;
  2)
    echo "Starting development environment..."
    cd ./docker/dev-env || { echo "Dev folder not found"; exit 1; }
    ./start-dev.sh
    ;;
  3)
    echo "Starting production and development environments..."
    cd ./docker/dev-env || { echo "Dev folder not found"; exit 1; }
    ./start-dev.sh

    cd ../prod-env || { echo "Prod folder not found"; exit 1; }
    ./start-prod.sh
    
    clear
    echo -e "${GREEN}✓ Production environment available at http://localhost:8080${NC}"
    echo -e "${GREEN}✓ Development environment available at http://localhost:8081${NC}"
    ;;
  *)
    echo "Invalid choice. Please select: 1, 2 or 3"
    exit 1
    ;;
esac

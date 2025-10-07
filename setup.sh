#!/bin/bash

# LAMP Skeleton Setup Script
# ==========================

set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to check if network exists and ask for confirmation
check_and_remove_network() {
    local network_name="$1"

    if docker network ls | grep -q "$network_name"; then
        echo -e "${YELLOW}⚠️  Network '$network_name' already exists${NC}"
        echo -e "${YELLOW}   This will be removed to ensure a clean setup.${NC}"
        echo ""
        echo -e "${GREEN}🗑️  Removing existing network...${NC}"
        docker network rm "$network_name" 2>/dev/null || true
        echo "  ✅ Network removed"
    fi
}

# Function to stop and remove existing containers
stop_existing_containers() {
    local web_container="$1"
    local db_container="$2"

    echo -e "${GREEN}🛑 Checking for existing containers...${NC}"

    # Check if containers are running
    local containers_running=false
    if docker ps -q -f name="$web_container" | grep -q .; then
        containers_running=true
    fi
    if docker ps -q -f name="$db_container" | grep -q .; then
        containers_running=true
    fi

    if [ "$containers_running" = true ]; then
        echo -e "${YELLOW}⚠️  Found running containers:${NC}"
        docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "($web_container|$db_container)" || true
        echo ""
        echo -e "${GREEN}🛑 Stopping and removing containers...${NC}"
        docker stop "$web_container" "$db_container" 2>/dev/null || true
        docker rm "$web_container" "$db_container" 2>/dev/null || true
        echo "  ✅ Containers stopped and removed"
    else
        echo "  ✅ No running containers found"
    fi
}

# Function to replace placeholders in template files
replace_placeholders() {
    local file="$1"
    local temp_file="${file}.tmp"

    # Replace placeholders using the already exported variables

    # Replace placeholders using | as delimiter to avoid issues with /
    sed -e "s|{{PROJECT_NAME}}|${PROJECT_NAME}|g" \
        -e "s|{{APP_NAMESPACE}}|${APP_NAMESPACE}|g" \
        -e "s|{{PHP_VERSION}}|${PHP_VERSION}|g" \
        -e "s|{{DOCKER_CONTAINER_NAME}}|${DOCKER_CONTAINER_NAME}|g" \
        -e "s|{{DOCKER_DB_CONTAINER_NAME}}|${DOCKER_DB_CONTAINER_NAME}|g" \
        -e "s|{{DOCKER_NETWORK}}|${DOCKER_NETWORK}|g" \
        -e "s|{{SUBNET_IP}}|${SUBNET_IP}|g" \
        -e "s|{{SUBNET_BASE}}|${SUBNET_BASE}|g" \
        -e "s|{{SUBNET_ALIAS}}|${SUBNET_ALIAS}|g" \
        -e "s|{{APACHE_PORT}}|${APACHE_PORT}|g" \
        -e "s|{{DB_PORT}}|${DB_PORT}|g" \
        -e "s|{{DB_NAME}}|${DB_NAME}|g" \
        -e "s|{{DB_USER}}|${DB_USER}|g" \
        -e "s|{{DB_PASSWORD}}|${DB_PASSWORD}|g" \
        -e "s|{{DB_ROOT_PASSWORD}}|${DB_ROOT_PASSWORD}|g" \
        -e "s|{{XDEBUG_HOST}}|${XDEBUG_HOST}|g" \
        -e "s|{{XDEBUG_PORT}}|${XDEBUG_PORT}|g" \
        -e "s|{{APP_SECRET}}|${APP_SECRET}|g" \
        "$file" > "$temp_file"

    # Determine output filename based on template type
    if [[ "$file" == *.template.php ]]; then
        output_file="${file%.template.php}.php"
    elif [[ "$file" == *.template.yaml ]]; then
        output_file="${file%.template.yaml}.yaml"
    elif [[ "$file" == *.template.xml ]]; then
        output_file="${file%.template.xml}.xml"
    elif [[ "$file" == *.template ]]; then
        output_file="${file%.template}"
    else
        output_file="${file%.template}"
    fi

    mv "$temp_file" "$output_file"
}

# Function to check if a port is available
check_port_availability() {
    local port="$1"
    local port_name="$2"

    if ss -tulpn | grep -q ":$port "; then
        echo -e "${RED}  ❌ Port $port ($port_name) is already in use${NC}"
        return 1
    else
        echo -e "${GREEN}  ✅ Port $port ($port_name) is available${NC}"
        return 0
    fi
}

# Function to check Docker network
check_docker_network_availability() {
    local network_name="$1"

    if docker network ls --format "{{.Name}}" | grep -q "^$network_name$"; then
        echo -e "${YELLOW}  ⚠️  Network '$network_name' already exists (will be removed)${NC}"
        return 1
    else
        echo -e "${GREEN}  ✅ Network '$network_name' is available${NC}"
        return 0
    fi
}

# Function to check container names
check_container_names() {
    local web_container="$1"
    local db_container="$2"
    local conflicts=0

    if docker ps -a --format "{{.Names}}" | grep -q "^$web_container$"; then
        echo -e "${YELLOW}  ⚠️  Container '$web_container' already exists (will be removed)${NC}"
        conflicts=1
    else
        echo -e "${GREEN}  ✅ Container name '$web_container' is available${NC}"
    fi

    if docker ps -a --format "{{.Names}}" | grep -q "^$db_container$"; then
        echo -e "${YELLOW}  ⚠️  Container '$db_container' already exists (will be removed)${NC}"
        conflicts=1
    else
        echo -e "${GREEN}  ✅ Container name '$db_container' is available${NC}"
    fi

    return $conflicts
}

# Function to check hosts file
check_hosts_file() {
    local alias="$1"

    if grep -q "$alias" /etc/hosts 2>/dev/null; then
        echo -e "${YELLOW}  ⚠️  Alias '$alias' already exists in /etc/hosts${NC}"
        return 1
    else
        echo -e "${GREEN}  ✅ Alias '$alias' not found in /etc/hosts${NC}"
        return 0
    fi
}

# Function to run all availability checks
run_availability_checks() {
    local apache_port="$1"
    local db_port="$2"
    local network="$3"
    local web_container="$4"
    local db_container="$5"
    local alias="$6"

    echo -e "${GREEN}🔍 Checking resource availability...${NC}"
    echo ""

    local has_conflicts=0

    # Check ports
    echo -e "${YELLOW}Checking ports:${NC}"
    check_port_availability "$apache_port" "Apache" || has_conflicts=1
    check_port_availability "$db_port" "Database" || has_conflicts=1
    echo ""

    # Check Docker network
    echo -e "${YELLOW}Checking Docker network:${NC}"
    check_docker_network_availability "$network" || has_conflicts=1
    echo ""

    # Check container names
    echo -e "${YELLOW}Checking container names:${NC}"
    check_container_names "$web_container" "$db_container" || has_conflicts=1
    echo ""

    # Check hosts file
    echo -e "${YELLOW}Checking hosts file:${NC}"
    check_hosts_file "$alias" || has_conflicts=1
    echo ""

    if [ $has_conflicts -eq 1 ]; then
        echo -e "${YELLOW}⚠️  Some resources already exist but will be cleaned up during setup${NC}"
    else
        echo -e "${GREEN}✅ All resources are available${NC}"
    fi

    return 0
}

echo -e "${GREEN}🚀 LAMP Skeleton Setup Script${NC}"
echo ""

# Check if Makefile exists
if [ ! -f "Makefile" ]; then
    echo -e "${RED}❌ Makefile not found!${NC}"
    exit 1
fi

# Read Makefile variables and expand them
export PROJECT_NAME=$(grep '^PROJECT_NAME :=' Makefile | cut -d' ' -f3)
export APP_NAMESPACE=$(grep '^APP_NAMESPACE :=' Makefile | cut -d' ' -f3)
export PHP_VERSION=$(grep '^PHP_VERSION :=' Makefile | cut -d' ' -f3)

# Expand variables that depend on PROJECT_NAME
export DOCKER_CONTAINER_NAME="${PROJECT_NAME}_web"
export DOCKER_DB_CONTAINER_NAME="${PROJECT_NAME}_db"
export DOCKER_NETWORK="${PROJECT_NAME}_network"
export SUBNET_IP=$(grep '^SUBNET_IP :=' Makefile | cut -d' ' -f3)
export SUBNET_BASE=$(grep '^SUBNET_BASE :=' Makefile | cut -d' ' -f3)
export SUBNET_ALIAS="${PROJECT_NAME}.local"
export APACHE_PORT=$(grep '^APACHE_PORT :=' Makefile | cut -d' ' -f3)
export DB_PORT=$(grep '^DB_PORT :=' Makefile | cut -d' ' -f3)
export DB_NAME="${PROJECT_NAME}"
export DB_USER="${PROJECT_NAME}"
export DB_PASSWORD="${PROJECT_NAME}"
export DB_ROOT_PASSWORD=$(grep '^DB_ROOT_PASSWORD :=' Makefile | cut -d' ' -f3)
export XDEBUG_HOST="${SUBNET_BASE}.1"
export XDEBUG_PORT=$(grep '^XDEBUG_PORT :=' Makefile | cut -d' ' -f3)

# Generate a secure random APP_SECRET
export APP_SECRET=$(openssl rand -hex 32)

echo -e "${YELLOW}Configuration Summary:${NC}"
echo "  Project Name: ${PROJECT_NAME}"
echo "  App Namespace: ${APP_NAMESPACE}"
echo "  Docker Container: ${DOCKER_CONTAINER_NAME}"
echo "  Docker Network: ${DOCKER_NETWORK}"
echo "  Subnet: ${SUBNET_IP}"
echo "  Alias: ${SUBNET_ALIAS}"
echo "  Apache Port: ${APACHE_PORT}"
echo "  Database Port: ${DB_PORT}"
echo "  Database Name: ${DB_NAME}"
echo "  App Secret: ${APP_SECRET:0:8}... (generated securely)"
echo ""

# Run availability checks
run_availability_checks "${APACHE_PORT}" "${DB_PORT}" "${DOCKER_NETWORK}" "${DOCKER_CONTAINER_NAME}" "${DOCKER_DB_CONTAINER_NAME}" "${SUBNET_ALIAS}"
echo ""

# Ask for confirmation
echo -e "${YELLOW}Do you want to continue with the setup? (y/N)${NC}"
read -r response
if [[ ! "$response" =~ ^[Yy]$ ]]; then
    echo -e "${RED}Setup cancelled by user${NC}"
    exit 1
fi

# Ask about hosts file configuration
echo ""
echo -e "${YELLOW}Do you want to add ${SUBNET_ALIAS} to /etc/hosts? (y/N)${NC}"
echo -e "${YELLOW}This will allow you to access the application via http://${SUBNET_ALIAS}:${APACHE_PORT}${NC}"
read -r hosts_response
if [[ "$hosts_response" =~ ^[Yy]$ ]]; then
    export SETUP_HOSTS=true
    echo -e "${GREEN}✅ Hosts file configuration will be added${NC}"
else
    export SETUP_HOSTS=false
    echo -e "${YELLOW}⚠️  Hosts file configuration skipped${NC}"
    echo -e "${YELLOW}   You can access the application via http://localhost:${APACHE_PORT}${NC}"
fi

# Check for existing infrastructure and ask for confirmation
echo -e "${GREEN}🔍 Checking existing infrastructure...${NC}"
check_and_remove_network "$DOCKER_NETWORK"
stop_existing_containers "$DOCKER_CONTAINER_NAME" "$DOCKER_DB_CONTAINER_NAME"

echo ""
echo -e "${GREEN}✅ Infrastructure check completed${NC}"
echo ""

echo -e "${GREEN}📝 Processing template files...${NC}"

if [ -f "docker-compose.yml.template" ]; then
    replace_placeholders "docker-compose.yml.template"
    echo "  ✅ docker-compose.yml generated"
fi

if [ -f ".docker/config/vhosts/default.conf.template" ]; then
    replace_placeholders ".docker/config/vhosts/default.conf.template"
    echo "  ✅ Apache vhosts configuration generated"
fi

if [ -f ".docker/php8.4/Dockerfile.template" ]; then
    replace_placeholders ".docker/php8.4/Dockerfile.template"
    echo "  ✅ Dockerfile generated"
fi

if [ -f "composer.json.template" ]; then
    replace_placeholders "composer.json.template"
    echo "  ✅ composer.json generated"
fi

if [ -f "config/routes/entrypoint.yaml.template" ]; then
    replace_placeholders "config/routes/entrypoint.yaml.template"
    echo "  ✅ config/routes/entrypoint.yaml generated"
fi

if [ -f "config/services/entrypoint.yaml.template" ]; then
    replace_placeholders "config/services/entrypoint.yaml.template"
    echo "  ✅ config/services/entrypoint.yaml generated"
fi

if [ -f "config/services/shared.yaml.template" ]; then
    replace_placeholders "config/services/shared.yaml.template"
    echo "  ✅ config/services/shared.yaml generated"
fi

if [ -f "bin/console.template" ]; then
    replace_placeholders "bin/console.template"
    echo "  ✅ bin/console generated"
fi

if [ -f "config/packages/doctrine.yaml.template" ]; then
    replace_placeholders "config/packages/doctrine.yaml.template"
    echo "  ✅ doctrine.yaml generated"
fi

if [ -f "public/index.php.template" ]; then
    replace_placeholders "public/index.php.template"
    echo "  ✅ public/index.php generated"
fi

if [ -f "src/Kernel.php.template" ]; then
    replace_placeholders "src/Kernel.php.template"
    echo "  ✅ src/Kernel.php generated"
fi

if [ -f "phpunit.xml.template" ]; then
    replace_placeholders "phpunit.xml.template"
    echo "  ✅ phpunit.xml generated"
fi

if [ -f "src/Entrypoint/Http/Controllers/HelloWorldController.php.template" ]; then
    replace_placeholders "src/Entrypoint/Http/Controllers/HelloWorldController.php.template"
    echo "  ✅ HelloWorldController.php generated"
fi

if [ -f "src/Entrypoint/Console/Commands/HelloWorldCommand.php.template" ]; then
    replace_placeholders "src/Entrypoint/Console/Commands/HelloWorldCommand.php.template"
    echo "  ✅ HelloWorldCommand.php generated"
fi

if [ -f "src/Shared/Domain/ValueObject.php.template" ]; then
    replace_placeholders "src/Shared/Domain/ValueObject.php.template"
    echo "  ✅ ValueObject.php generated"
fi

if [ -f "src/Shared/Domain/Entity.php.template" ]; then
    replace_placeholders "src/Shared/Domain/Entity.php.template"
    echo "  ✅ Entity.php generated"
fi

if [ -f "src/Shared/Domain/Repository.php.template" ]; then
    replace_placeholders "src/Shared/Domain/Repository.php.template"
    echo "  ✅ Repository.php generated"
fi

if [ -f "src/Shared/Application/Command.php.template" ]; then
    replace_placeholders "src/Shared/Application/Command.php.template"
    echo "  ✅ Command.php generated"
fi

if [ -f "src/Shared/Application/Query.php.template" ]; then
    replace_placeholders "src/Shared/Application/Query.php.template"
    echo "  ✅ Query.php generated"
fi

if [ -f "tests/Entrypoint/Http/Controllers/HelloWorldControllerTest.php.template" ]; then
    replace_placeholders "tests/Entrypoint/Http/Controllers/HelloWorldControllerTest.php.template"
    echo "  ✅ HelloWorldControllerTest.php generated"
fi

if [ -f "tests/Shared/Domain/ValueObjectTest.php.template" ]; then
    replace_placeholders "tests/Shared/Domain/ValueObjectTest.php.template"
    echo "  ✅ ValueObjectTest.php generated"
fi

if [ -f ".env.template" ]; then
    replace_placeholders ".env.template"
    echo "  ✅ .env generated"
fi

if [ -f ".env.test.template" ]; then
    replace_placeholders ".env.test.template"
    echo "  ✅ .env.test generated"
fi

if [ -f "phpstan.neon.template" ]; then
    replace_placeholders "phpstan.neon.template"
    echo "  ✅ phpstan.neon generated"
fi

if [ -f "config/services/entrypoint.yaml.template" ]; then
    replace_placeholders "config/services/entrypoint.yaml.template"
    echo "  ✅ entrypoint.yaml generated"
fi

if [ -f "config/services/shared.yaml.template" ]; then
    replace_placeholders "config/services/shared.yaml.template"
    echo "  ✅ shared.yaml generated"
fi

if [ -f "config/routes/entrypoint.yaml.template" ]; then
    replace_placeholders "config/routes/entrypoint.yaml.template"
    echo "  ✅ entrypoint routes generated"
fi

echo ""
echo -e "${GREEN}✅ Template processing completed!${NC}"
echo ""

echo -e "${GREEN}🔨 Building Docker images...${NC}"
COMPOSE_PROJECT_NAME="${PROJECT_NAME}" docker-compose build --no-cache
echo -e "${GREEN}✅ Images built successfully${NC}"

echo -e "${GREEN}🚀 Starting containers...${NC}"
COMPOSE_PROJECT_NAME="${PROJECT_NAME}" docker-compose up -d
echo -e "${GREEN}✅ Containers started${NC}"

echo -e "${GREEN}📦 Installing dependencies...${NC}"
docker exec "${DOCKER_CONTAINER_NAME}" composer install --optimize-autoloader
echo -e "${GREEN}✅ Dependencies installed${NC}"

echo -e "${GREEN}📦 Setting up Composer...${NC}"
docker exec "${DOCKER_CONTAINER_NAME}" composer dump-autoload --optimize
echo -e "${GREEN}✅ Composer configured${NC}"

find . -name "*.template" -not -path "./vendor/*" -not -path "./.git/*" -delete
echo -e "${GREEN}✅ Template files cleaned up${NC}"

echo -e "${GREEN}🔐 Fixing file permissions...${NC}"
sudo chown -R $(whoami):$(whoami) .
find . -type d -not -path "./vendor/*" -not -path "./.git/*" -exec chmod 755 {} \;
find . -type f -not -path "./vendor/*" -not -path "./.git/*" -exec chmod 644 {} \;
chmod +x bin/console
chmod -R 777 var/
echo -e "${GREEN}✅ Permissions fixed${NC}"


echo ""
echo -e "${GREEN}✅ Setup completed successfully!${NC}"
if [ "$SETUP_HOSTS" = "true" ]; then
    echo -e "${YELLOW}Your application is available at: http://${SUBNET_ALIAS}:${APACHE_PORT}${NC}"
else
    echo -e "${YELLOW}Your application is available at: http://localhost:${APACHE_PORT}${NC}"
    echo -e "${YELLOW}To use the domain alias, run: make setup-hosts${NC}"
fi

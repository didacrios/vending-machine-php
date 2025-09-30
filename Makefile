# LAMP Skeleton Makefile Configuration
# =====================================

# Project Configuration
PROJECT_NAME := artesansdigitals
APP_NAMESPACE := ArtesansDigitals
PHP_VERSION := 8.4

# Docker Configuration
DOCKER_CONTAINER_NAME := $(PROJECT_NAME)_web
DOCKER_DB_CONTAINER_NAME := $(PROJECT_NAME)_db
DOCKER_NETWORK := $(PROJECT_NAME)_network
SUBNET_IP := 192.20.85.0/16
SUBNET_ALIAS := $(PROJECT_NAME).local

# Port Configuration
APACHE_PORT := 8089
DB_PORT := 13307

# Database Configuration
DB_NAME := $(PROJECT_NAME)
DB_USER := $(PROJECT_NAME)
DB_PASSWORD := $(PROJECT_NAME)
DB_ROOT_PASSWORD := root_password

# Development Configuration
XDEBUG_HOST := 192.20.85.1
XDEBUG_PORT := 9000

# Colors for output
GREEN := \033[0;32m
YELLOW := \033[1;33m
RED := \033[0;31m
NC := \033[0m # No Color

# Default target
.PHONY: help
help: ## Show this help message
	@echo "$(GREEN)LAMP Skeleton - Available Commands:$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2}'

# Setup and Installation
.PHONY: setup
setup: ## Run the complete setup process
	@echo "$(GREEN)🚀 Starting LAMP Skeleton Setup...$(NC)"
	@echo ""
	@$(MAKE) check-dependencies
	@./setup.sh
	@$(MAKE) setup-hosts


# Dependency checks
.PHONY: check-dependencies
check-dependencies: ## Check if required tools are installed
	@echo "$(GREEN)🔍 Checking dependencies...$(NC)"
	@command -v docker >/dev/null 2>&1 || { echo "$(RED)❌ Docker is required but not installed.$(NC)"; exit 1; }
	@command -v docker-compose >/dev/null 2>&1 || { echo "$(RED)❌ Docker Compose is required but not installed.$(NC)"; exit 1; }
	@command -v make >/dev/null 2>&1 || { echo "$(RED)❌ Make is required but not installed.$(NC)"; exit 1; }
	@echo "$(GREEN)✅ All dependencies are installed$(NC)"

# Conflict checks
.PHONY: check-conflicts
check-conflicts: ## Check for port and network conflicts
	@echo "$(GREEN)🔍 Checking for conflicts...$(NC)"
	@if docker network ls | grep -q $(DOCKER_NETWORK); then \
		echo "$(YELLOW)⚠️  Network $(DOCKER_NETWORK) already exists$(NC)"; \
	else \
		echo "$(GREEN)✅ No network conflicts$(NC)"; \
	fi
	@if netstat -tuln 2>/dev/null | grep -q ":$(APACHE_PORT) "; then \
		echo "$(RED)❌ Port $(APACHE_PORT) is already in use$(NC)"; exit 1; \
	else \
		echo "$(GREEN)✅ Port $(APACHE_PORT) is available$(NC)"; \
	fi

# Network management
.PHONY: create-network
create-network: ## Create Docker network if it doesn't exist
	@echo "$(GREEN)🌐 Creating Docker network...$(NC)"
	@if ! docker network ls | grep -q $(DOCKER_NETWORK); then \
		docker network create --subnet=$(SUBNET_IP) $(DOCKER_NETWORK); \
		echo "$(GREEN)✅ Network $(DOCKER_NETWORK) created$(NC)"; \
	else \
		echo "$(YELLOW)⚠️  Network $(DOCKER_NETWORK) already exists$(NC)"; \
	fi

# Container management
.PHONY: stop-containers
stop-containers: ## Stop and remove existing containers
	@echo "$(GREEN)🛑 Stopping existing containers...$(NC)"
	@docker-compose down 2>/dev/null || true
	@docker stop $(DOCKER_CONTAINER_NAME) $(DOCKER_DB_CONTAINER_NAME) 2>/dev/null || true
	@docker rm $(DOCKER_CONTAINER_NAME) $(DOCKER_DB_CONTAINER_NAME) 2>/dev/null || true
	@echo "$(GREEN)✅ Containers stopped and removed$(NC)"

.PHONY: build-images
build-images: ## Build Docker images
	@echo "$(GREEN)🔨 Building Docker images...$(NC)"
	@COMPOSE_PROJECT_NAME=$(PROJECT_NAME) docker-compose build --no-cache
	@echo "$(GREEN)✅ Images built successfully$(NC)"

.PHONY: start-containers
start-containers: ## Start Docker containers
	@echo "$(GREEN)🚀 Starting containers...$(NC)"
	@COMPOSE_PROJECT_NAME=$(PROJECT_NAME) docker-compose up -d
	@echo "$(GREEN)✅ Containers started$(NC)"

# Hosts file management
.PHONY: setup-hosts
setup-hosts: ## Add alias to /etc/hosts
	@echo "$(GREEN)📝 Setting up hosts file...$(NC)"
	@if ! grep -q "$(SUBNET_ALIAS)" /etc/hosts; then \
		echo "127.0.0.1 $(SUBNET_ALIAS)" | sudo tee -a /etc/hosts; \
		echo "$(GREEN)✅ Added $(SUBNET_ALIAS) to /etc/hosts$(NC)"; \
	else \
		echo "$(YELLOW)⚠️  $(SUBNET_ALIAS) already exists in /etc/hosts$(NC)"; \
	fi

# Dependencies installation
.PHONY: install-dependencies
install-dependencies: ## Install Composer dependencies
	@echo "$(GREEN)📦 Installing dependencies...$(NC)"
	@docker exec $(DOCKER_CONTAINER_NAME) composer install --optimize-autoloader
	@echo "$(GREEN)✅ Dependencies installed$(NC)"

# Composer setup
.PHONY: setup-composer
setup-composer: ## Configure Composer autoload with namespace
	@echo "$(GREEN)📦 Setting up Composer...$(NC)"
	@docker exec $(DOCKER_CONTAINER_NAME) composer dump-autoload --optimize
	@echo "$(GREEN)✅ Composer configured$(NC)"

# Development commands
.PHONY: dev
dev: ## Start development environment
	@echo "$(GREEN)🚀 Starting development environment...$(NC)"
	@docker-compose up -d
	@echo "$(GREEN)✅ Development environment ready$(NC)"
	@echo "$(YELLOW)Application: http://$(SUBNET_ALIAS):$(APACHE_PORT)$(NC)"

.PHONY: logs
logs: ## Show container logs
	@docker-compose logs -f

.PHONY: shell
shell: ## Open shell in web container
	@docker exec -it $(DOCKER_CONTAINER_NAME) /bin/bash

.PHONY: db-shell
db-shell: ## Open MySQL shell
	@docker exec -it $(DOCKER_DB_CONTAINER_NAME) mysql -u$(DB_USER) -p$(DB_PASSWORD) $(DB_NAME)

# Testing commands
.PHONY: test
test: ## Run all tests
	@echo "$(GREEN)🧪 Running all tests...$(NC)"
	@docker exec $(DOCKER_CONTAINER_NAME) ./vendor/bin/phpunit

.PHONY: test-unit
test-unit: ## Run unit tests
	@echo "$(GREEN)🧪 Running unit tests...$(NC)"
	@docker exec $(DOCKER_CONTAINER_NAME) ./vendor/bin/phpunit --testsuite=Unit

.PHONY: test-integration
test-integration: ## Run integration tests
	@echo "$(GREEN)🧪 Running integration tests...$(NC)"
	@docker exec $(DOCKER_CONTAINER_NAME) ./vendor/bin/phpunit --testsuite=Integration

# Code Quality commands
.PHONY: phpstan
phpstan: ## Run PHPStan static analysis
	@echo "$(GREEN)🔍 Running PHPStan static analysis...$(NC)"
	@docker exec $(DOCKER_CONTAINER_NAME) ./vendor/bin/phpstan analyse

.PHONY: phpstan-baseline
phpstan-baseline: ## Generate PHPStan baseline
	@echo "$(GREEN)📊 Generating PHPStan baseline...$(NC)"
	@docker exec $(DOCKER_CONTAINER_NAME) ./vendor/bin/phpstan analyse --generate-baseline

.PHONY: quality
quality: phpstan test ## Run all quality checks (PHPStan + Tests)
	@echo "$(GREEN)✅ All quality checks passed!$(NC)"

# Network management
.PHONY: network-info
network-info: ## Show Docker network information
	@echo "$(GREEN)🔍 Network Information:$(NC)"
	@if docker network ls --format "{{.Name}}" | grep -q "^$(DOCKER_NETWORK)$$"; then \
		echo "$(YELLOW)Network '$(DOCKER_NETWORK)' found:$(NC)"; \
		docker network inspect $(DOCKER_NETWORK) --format '  Name: {{.Name}}'; \
		docker network inspect $(DOCKER_NETWORK) --format '  Created: {{.Created}}'; \
		docker network inspect $(DOCKER_NETWORK) --format '  Driver: {{.Driver}}'; \
		docker network inspect $(DOCKER_NETWORK) --format '  Subnet: {{range .IPAM.Config}}{{.Subnet}}{{end}}'; \
		container_count=$$(docker network inspect $(DOCKER_NETWORK) --format '{{len .Containers}}'); \
		echo "  Containers: $$container_count"; \
		if [ "$$container_count" -gt 0 ]; then \
			echo "$(YELLOW)  Connected containers:$(NC)"; \
			docker network inspect $(DOCKER_NETWORK) --format '  {{range .Containers}}    - {{.Name}} ({{.IPv4Address}}){{end}}'; \
		fi; \
	else \
		echo "$(YELLOW)⚠️  Network '$(DOCKER_NETWORK)' not found$(NC)"; \
	fi

.PHONY: remove-network
remove-network: ## Remove Docker network (with confirmation)
	@echo "$(GREEN)🔍 Checking for network '$(DOCKER_NETWORK)'...$(NC)"
	@if docker network ls --format "{{.Name}}" | grep -q "^$(DOCKER_NETWORK)$$"; then \
		echo "$(YELLOW)⚠️  Network '$(DOCKER_NETWORK)' found:$(NC)"; \
		docker network inspect $(DOCKER_NETWORK) --format '  Name: {{.Name}}'; \
		docker network inspect $(DOCKER_NETWORK) --format '  Created: {{.Created}}'; \
		container_count=$$(docker network inspect $(DOCKER_NETWORK) --format '{{len .Containers}}'); \
		echo "  Containers: $$container_count"; \
		if [ "$$container_count" -gt 0 ]; then \
			echo "$(YELLOW)⚠️  Network has active containers. Stopping containers first...$(NC)"; \
			COMPOSE_PROJECT_NAME=$(PROJECT_NAME) docker-compose down; \
			echo "$(GREEN)✅ Containers stopped$(NC)"; \
			echo "$(GREEN)✅ Network '$(DOCKER_NETWORK)' removed automatically$(NC)"; \
		else \
			echo ""; \
			read -p "Remove this network? (y/N): " confirm && [ "$$confirm" = "y" ] || exit 1; \
			if docker network rm $(DOCKER_NETWORK); then \
				echo "$(GREEN)✅ Network '$(DOCKER_NETWORK)' removed$(NC)"; \
			else \
				echo "$(RED)❌ Failed to remove network '$(DOCKER_NETWORK)'$(NC)"; \
				exit 1; \
			fi; \
		fi; \
	else \
		echo "$(YELLOW)⚠️  Network '$(DOCKER_NETWORK)' not found$(NC)"; \
	fi

# Cleanup commands
.PHONY: clean
clean: ## Clean up containers and images
	@echo "$(GREEN)🧹 Cleaning up...$(NC)"
	@docker-compose down -v
	@docker system prune -f
	@echo "$(GREEN)✅ Cleanup completed$(NC)"

.PHONY: reset
reset: ## Reset everything (containers, images, network)
	@echo "$(RED)⚠️  This will remove all containers, images, and networks. Continue? (y/N)$(NC)"
	@read -p "" confirm && [ "$$confirm" = "y" ] || exit 1
	@$(MAKE) clean
	@docker network rm $(DOCKER_NETWORK) 2>/dev/null || true
	@echo "$(GREEN)✅ Reset completed$(NC)"

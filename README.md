# Vending Machine - Technical Challenge

A vending machine simulation built with Symfony 7.3.3, following DDD + Hexagonal Architecture principles. This project implements a vending machine that accepts coins, dispenses items, and handles change.

## 🎯 Project Requirements

The vending machine requirements have been refined and converted into user stories for iterative development:

### 📋 User Stories

- **[USER_STORY_001.md](USER_STORY_001.md)** - As a customer, I want to insert money and purchase items, so that I can get the products I need
- **[USER_STORY_002.md](USER_STORY_002.md)** - As a customer, I want to receive change when I overpay for an item, so that I don't lose money
- **[USER_STORY_003.md](USER_STORY_003.md)** - As a customer, I want to return my inserted money if I change my mind, so that I can get my money back
- **[USER_STORY_004.md](USER_STORY_004.md)** - As a service person, I want to restock items and change in the machine, so that customers can continue to use the vending machine

Each user story includes acceptance tests in Gherkin format to guide development.

## 🏗️ Technical Foundation

This project is built on a LAMP (Linux - Apache - MySQL/MariaDB - PHP) skeleton for Symfony 7.3.3 projects with Docker and Docker Compose, following DDD + Hexagonal Architecture principles.

## 🚀 Quick Start

1. **Clone the skeleton:**
   ```bash
   git clone <your-repo-url> my-new-project
   cd my-new-project
   ```

2. **Configure your project** in `Makefile`:
   ```makefile
   PROJECT_NAME := my-project
   APP_NAMESPACE := MyProject
   # ... other variables
   ```

3. **Run setup:**
   ```bash
   make setup
   ```

   This will automatically:
   - Process all template files with your namespace
   - Generate personalized configuration files
   - Set up Docker containers
   - Install dependencies
   - Configure Composer autoload
   - Clean up template files (no longer needed)

4. **Access your application:**
   - Web: http://localhost:8089
   - Database: localhost:13307

Or the possible domain alias and ports configured in the Makefile.

## 📁 Project Structure

```
src/
├── Entrypoint/                 # Entry points to the application
│   ├── Http/Controllers/       # HTTP Controllers
│   └── Console/Commands/       # Console Commands
└── Shared/                     # Shared components
    ├── Application/            # Application services, commands, queries
    ├── Domain/                 # Domain entities, value objects, repositories
    └── Infrastructure/         # Infrastructure implementations
```

## ⚙️ Configuration

The project uses a `Makefile` with configurable variables:

- `PROJECT_NAME`: Project identifier
- `APP_NAMESPACE`: PHP namespace (default: App)
- `DOCKER_CONTAINER_NAME`: Docker container name
- `DOCKER_NETWORK`: Docker network name
- `SUBNET_IP`: Docker subnet IP range
- `SUBNET_ALIAS`: Local domain alias
- `APACHE_PORT`: Apache port (default: 8089)
- `DB_PORT`: Database port (default: 13307)
- `DB_NAME`: Database name
- `DB_USER`: Database user
- `DB_PASSWORD`: Database password

## 🔧 Template System

The skeleton uses a template system that automatically generates personalized files based on your configuration. When you run `make setup`, the following files are automatically generated with your namespace:
- `bin/console` - Symfony console entry point
- `public/index.php` - Web entry point
- `src/Kernel.php` - Application kernel
- `config/packages/doctrine.yaml` - Doctrine configuration
- `phpunit.xml` - PHPUnit configuration
- All source code files in `src/`
- All test files in `tests/`

This ensures that when you clone the skeleton and change the `APP_NAMESPACE` in the Makefile, all files are automatically updated with your custom namespace.

## 🛠️ Available Commands

### Setup & Installation
- `make setup` - Complete project setup
- `make process-templates` - Process template files
- `make check-dependencies` - Check required tools
- `make check-conflicts` - Check for port/network conflicts

### Development
- `make dev` - Start development environment
- `make logs` - Show container logs
- `make shell` - Open shell in web container
- `make db-shell` - Open MySQL shell

### Testing
- `make test` - Run all tests
- `make test-unit` - Run unit tests
- `make test-integration` - Run integration tests

### Code Quality
- `make phpstan` - Run PHPStan static analysis
- `make phpstan-baseline` - Generate PHPStan baseline
- `make quality` - Run all quality checks (PHPStan + Tests)

### Cleanup
- `make clean` - Clean up containers and images
- `make reset` - Reset everything (containers, images, network)

## 🧪 Testing

The project includes three test suites with a structure that mirrors the production code:

- **All**: Runs all tests
- **Unit**: Unit tests for domain and application layers
- **Integration**: Integration tests mainly in infrastructure layers

Tests follow the Given-When-Then format and use PHPUnit 12.

## 📦 Included Packages

### Production
- Symfony 7.3.3
- Symfony Dotenv
- Symfony Messenger
- Twig
- PHPMailer

### Development
- PHPUnit 12
- PHPStan (Level 6)
- PHPStan Symfony Extension
- PHPStan Doctrine Extension
- Rector
- Symfony Web Profiler
- Symfony Maker Bundle

## 🏗️ Architecture

This skeleton follows **Domain-Driven Design (DDD)** and **Hexagonal Architecture** principles:

- **Entrypoint Layer**: HTTP Controllers and Console Commands
- **Application Layer**: Use cases, commands, queries
- **Domain Layer**: Business logic, entities, value objects
- **Infrastructure Layer**: External concerns (database, HTTP clients, etc.)

## 📝 Configuration Files

- **Routing**: Modular YAML files in `config/routes/`
- **Services**: Modular YAML files in `config/services/`
- **Docker**: Template-based configuration with variable substitution

## 🔧 Customization

1. **Change project name**: Edit `PROJECT_NAME` in `Makefile`
2. **Add new aggregates**: Create directories under `src/` following the pattern
3. **Add new routes**: Create YAML files in `config/routes/`
4. **Add new services**: Create YAML files in `config/services/`

## 📋 Example Usage

After setup, you can:

1. **Test the HelloWorld controller:**
   ```bash
   curl http://localhost:8089/hello
   curl http://localhost:8089/hello/Alice
   ```

2. **Test the HelloWorld command:**
   ```bash
   make shell
   php bin/console app:hello-world
   php bin/console app:hello-world Alice
   ```

3. **Run tests and quality checks:**
   ```bash
   make test
   make phpstan
   make quality  # Runs both PHPStan and tests
   ```

## 🐳 Docker

The project uses Docker with:
- **PHP 8.4** with Apache
- **MariaDB 10.2**
- **Custom network** with fixed IPs
- **Volume mounting** for development

## 📄 License

This project is released into the public domain under the [Creative Commons Zero (CC0) License](LICENSE).

You are free to:
- ✅ Use this code for any purpose (commercial or non-commercial)
- ✅ Modify, distribute, and redistribute without restrictions
- ✅ Include in proprietary projects
- ✅ No attribution required

For more details, see the [LICENSE](LICENSE) file.

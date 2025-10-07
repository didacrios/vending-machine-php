# Vending Machine - Technical Challenge

A vending machine simulation built with PHP 8.4 and Symfony 7.3.3, following Domain-Driven Design (DDD) and Hexagonal Architecture principles. This project implements a functional vending machine that handles coin insertion, product purchases, change calculation, and inventory management.

## 🚀 Quick Start

### Prerequisites
- Docker
- Docker Compose
- Make

### Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/didacrios/vending-machine-php.git
   cd vending-machine-php
   ```

2. **Configure (optional):**

   If you need to change ports or other settings, edit the `Makefile`:
   ```makefile
   APACHE_PORT := 8085    # Web access port
   DB_PORT := 13309       # Database port
   ```

   Or edit `docker-compose.yml` directly for more control.

3. **Run setup:**
   ```bash
   make setup
   ```

   This will:
   - Build and start Docker containers
   - Install all dependencies
   - Configure the application

4. **Run database migrations:**
   ```bash
   make doctrine-migrations-migrate
   ```

   This will create the necessary database structure.

5. **Verify installation:**
   ```bash
   make test
   ```

## 🎯 User Stories

The vending machine requirements have been broken down into user stories for iterative development:

- **[USER_STORY_001.md](USER_STORY_001.md)** - As a customer, I want to insert money and purchase items
- **[USER_STORY_002.md](USER_STORY_002.md)** - As a customer, I want to receive change when I overpay for an item
- **[USER_STORY_003.md](USER_STORY_003.md)** - As a customer, I want to return my inserted money if I change my mind
- **[USER_STORY_004.md](USER_STORY_004.md)** - As a service person, I want to restock items and change in the machine

Each user story includes acceptance tests in Gherkin format to guide development.

### Quick Demo - Testing User Stories

Once you have the environment running (`make setup`), access the shell (`make shell`) and execute these commands to test each user story:

#### USER_STORY_001: Insert money and purchase items
```bash
# First, restock the machine with products and change
php bin/console vending-machine:restock --water=5 --juice=5 --soda=5 --coin-5=10 --coin-10=10 --coin-25=10 --coin-100=5

# Insert coins (total €1.50)
php bin/console vending-machine:insert-coin 1.00
php bin/console vending-machine:insert-coin 0.25
php bin/console vending-machine:insert-coin 0.25

# Purchase WATER (€1.50)
php bin/console vending-machine:purchase-product WATER
```

#### USER_STORY_002: Receive change when overpaying
```bash
# Insert coins (total €2.00)
php bin/console vending-machine:insert-coin 1.00
php bin/console vending-machine:insert-coin 1.00

# Purchase JUICE (€1.75) - should receive €0.25 change
php bin/console vending-machine:purchase-product JUICE
```

#### USER_STORY_003: Return inserted money
```bash
# Insert coins (total €1.00)
php bin/console vending-machine:insert-coin 0.25
php bin/console vending-machine:insert-coin 0.25
php bin/console vending-machine:insert-coin 0.25
php bin/console vending-machine:insert-coin 0.25

# Return all coins
php bin/console vending-machine:return-coin
```

#### USER_STORY_004: Restock items and change
```bash
# Restock the vending machine with products and change (service operation)
php bin/console vending-machine:restock --water=10 --juice=10 --soda=10 --coin-5=20 --coin-10=20 --coin-25=20 --coin-100=10

# You can also restock individual items
php bin/console vending-machine:restock --water=5
php bin/console vending-machine:restock --coin-25=10 --coin-100=5
```

**Note**: Each command includes `--help` for detailed usage:
```bash
php bin/console vending-machine:insert-coin --help
```

## 🏗️ Technical Foundation

This project is built on a custom PHP DDD skeleton, forked from [php-ddd-skeleton](https://github.com/didacrios/php-ddd-skeleton), which provides:

- **Docker-based development environment** (PHP 8.4 + Apache + MariaDB)
- **DDD + Hexagonal Architecture** structure
- **Symfony 7.3.3** with Messenger for CQRS
- **PHPUnit 12** for testing
- **PHPStan** for static analysis

The skeleton was developed by myself and customized and adapted specifically for this vending machine implementation.

## 🔄 Development Process

This project was developed following **Test-Driven Development (TDD)** principles with an iterative approach:

- **Red-Green-Refactor cycle**: Write failing tests → Make them pass → Refactor
- **Iterative implementation**: Each user story was developed incrementally
- **Commit history as documentation**: All design decisions and thought processes are reflected in the commit messages

The git history provides a complete narrative of the development process.

## 💻 Using the Application

The vending machine is a **terminal-based application** using Symfony Console commands.

### List Available Commands

```bash
make vending-machine-help
```

This will display all available vending machine operations:
- `vending-machine:insert-coin` - Insert coins into the machine
- `vending-machine:purchase-product` - Purchase a product
- `vending-machine:return-coin` - Return inserted coins
- `vending-machine:restock` - Restock products and change (service operation)

### Execute Commands

1. **Access the container shell:**
   ```bash
   make shell
   ```

2. **Run commands:**
   ```bash
   # Get help for a specific command
   php bin/console vending-machine:insert-coin --help

   # Insert a coin
   php bin/console vending-machine:insert-coin 0.25

   # Purchase a product
   php bin/console vending-machine:purchase-product WATER

   # Return coins
   php bin/console vending-machine:return-coin

   # Restock (service operation)
   php bin/console vending-machine:restock
   ```

## 🛠️ Essential Commands

### Setup & Installation
```bash
make setup                  # Complete project setup
```

### Testing
```bash
make test                   # Run all tests
make test-with-coverage     # Run tests with coverage report (requires Xdebug)
```

### Development
```bash
make vending-machine-help   # List all vending machine commands
make shell                  # Access container shell to run commands
make logs                   # Show container logs
make quality                # Run PHPStan + all tests
```

### Other Commands
See the `Makefile` for additional commands including database management, cache clearing, and more.

## 📁 Project Structure

```
src/
├── Entrypoint/
│   └── Console/Commands/          # Console commands (user interface)
├── Shared/
│   └── Domain/
│       └── Money.php              # Shared value object
└── VendingMachine/
    ├── Application/               # Use cases (CQRS handlers)
    │   ├── Customer/              # Customer operations
    │   └── Service/               # Service operations
    ├── Domain/                    # Business logic
    │   ├── Entity/
    │   ├── ValueObject/
    │   ├── Repository/
    │   ├── Service/
    │   └── Exception/
    └── Infrastructure/            # Technical implementation
        └── Repository/
```

## 🏛️ Architecture

The project follows **Domain-Driven Design (DDD)** and **Hexagonal Architecture**:

- **Domain Layer**: Core business logic, entities, value objects, and domain services
- **Application Layer**: Use cases implemented as command/query handlers (CQRS pattern)
- **Infrastructure Layer**: Technical implementations (repositories, persistence)
- **Entrypoint Layer**: User interfaces (console commands, HTTP controllers)

Key patterns used:
- **CQRS**: Commands for write operations, Queries for read operations
- **Repository Pattern**: Abstraction over data persistence
- **Value Objects**: Immutable objects for domain concepts (Money, Coin, Product)
- **Domain Services**: Business logic that doesn't belong to a single entity

## 🧪 Testing

The project maintains a comprehensive test suite with:

- **Unit Tests**: Domain and application layer logic (handlers, domain services, entities, value objects)
- **Test Coverage**: Aim for 80%+ code coverage
- **TDD Approach**: Tests written before implementation

Run tests with:
```bash
make test                   # All tests
make test-with-coverage     # With coverage report
```

### Why No Integration Tests?

Integration tests for console commands and infrastructure layers were deliberately omitted because:
- **Business logic is fully covered**: All application handlers and domain services have comprehensive unit tests
- **Console commands are thin wrappers**: They only parse arguments and dispatch to already-tested handlers
- **Manual testing is straightforward**: The terminal interface makes manual verification simple and quick
- **Cost-benefit analysis**: For an assessment context, the time investment doesn't justify the marginal additional coverage

The core business logic is thoroughly tested, and the thin integration layers can be reliably verified through manual testing.

## 📝 Configuration

- **Docker**: `docker-compose.yml` and `.docker/` directory
- **Symfony**: `config/` directory with modular YAML files
- **Database**: MariaDB 10.2 (configured in docker-compose.yml)
- **PHP**: PHP 8.4 with Xdebug for coverage

## 📄 License

This project is released into the public domain under the [Creative Commons Zero (CC0) License](LICENSE).

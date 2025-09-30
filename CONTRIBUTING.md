# Contributing to LAMP Skeleton

Thank you for your interest in contributing to the LAMP Skeleton! This document provides guidelines and information for contributors.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Project Structure](#project-structure)
- [Development Workflow](#development-workflow)
- [Template System](#template-system)
- [Testing](#testing)
- [Pull Request Process](#pull-request-process)
- [Issue Guidelines](#issue-guidelines)

## 🤝 Code of Conduct

By participating in this project, you agree to abide by our code of conduct. Please be respectful and constructive in all interactions.

## 🚀 Getting Started

### Prerequisites

Before contributing, ensure you have the following installed:

- **Docker** and **Docker Compose**
- **Make** (for using the Makefile commands)
- **Git** (for version control)

### Setting Up for Development

1. **Fork and clone the repository:**
   ```bash
   git clone https://github.com/your-username/php-ddd-docker-skeleton.git
   cd php-ddd-docker-skeleton
   ```

2. **Configure your development environment:**
   Edit the `Makefile` to use a unique project name for development:
   ```makefile
   PROJECT_NAME := skeleton-dev
   APP_NAMESPACE := SkeletonDev
   ```

3. **Run the setup:**
   ```bash
   make setup
   ```

4. **Verify everything works:**
   ```bash
   make test
   curl http://skeleton-dev.local:8089/hello
   ```

## 📁 Project Structure

Understanding the project structure is crucial for effective contributions:

```
src/
├── Entrypoint/                 # Application entry points
│   ├── Http/Controllers/       # HTTP Controllers (templates)
│   └── Console/Commands/       # Console Commands (templates)
└── Shared/                     # Shared components
    ├── Application/            # Application layer templates
    ├── Domain/                 # Domain layer templates
    └── Infrastructure/         # Infrastructure layer templates

tests/
├── Entrypoint/                 # Integration test templates
│   └── Http/Controllers/       # HTTP Controller tests
└── Shared/                     # Unit test templates
    └── Domain/                 # Domain layer tests

config/
├── packages/                   # Package configuration templates
├── routes/                     # Routing templates
└── services/                   # Service configuration templates
```

## 🔄 Development Workflow

### 1. Creating a New Feature

When adding new functionality to the skeleton:

1. **Create template files** with `.template` extension
2. **Add placeholder variables** using `{{VARIABLE_NAME}}` syntax
3. **Update the setup script** to process your templates
4. **Add corresponding tests** as templates
5. **Update documentation** (README.md, this file)

### 2. Template File Guidelines

#### Naming Convention
- PHP files: `MyClass.php.template`
- YAML files: `config.yaml.template`
- XML files: `config.xml.template`
- Other files: `filename.template`

#### Placeholder Variables
Use these standard placeholders in your templates:

```php
<?php

namespace {{APP_NAMESPACE}}\Domain;

class {{CLASS_NAME}}
{
    // Implementation
}
```

Available variables:
- `{{PROJECT_NAME}}` - Project identifier
- `{{APP_NAMESPACE}}` - PHP namespace
- `{{PHP_VERSION}}` - PHP version
- `{{DOCKER_CONTAINER_NAME}}` - Web container name
- `{{DOCKER_DB_CONTAINER_NAME}}` - Database container name
- `{{DOCKER_NETWORK}}` - Docker network name
- `{{APACHE_PORT}}` - Apache port
- `{{DB_PORT}}` - Database port
- `{{DB_NAME}}` - Database name
- `{{DB_USER}}` - Database user
- `{{DB_PASSWORD}}` - Database password

### 3. Updating the Setup Script

When adding new templates, update `setup.sh`:

1. **Add template processing** in the appropriate section
2. **Add cleanup** in the cleanup section
3. **Test the complete setup process**

Example addition to `setup.sh`:

```bash
# Process your new template
if [ -f "src/YourDomain/YourClass.php.template" ]; then
    replace_placeholders "src/YourDomain/YourClass.php.template"
    echo "  ✅ YourClass.php generated"
fi
```

## 🧪 Testing

### Test Structure

The skeleton includes comprehensive testing with a structure that mirrors the production code:

- **Unit Tests**: Test individual domain components in isolation (`tests/Shared/`)
- **Integration Tests**: Test entry points and component interactions (`tests/Entrypoint/`)
- **Template Tests**: Ensure templates generate correct code

### Running Tests

```bash
# Run all tests
make test

# Run specific test suites
make test-unit      # Unit tests (tests/Shared/)
make test-integration  # Integration tests (tests/Entrypoint/)
```

### Writing Tests

1. **Create test templates** following the same structure as `src/`
   - Unit tests: `tests/Shared/Domain/YourClassTest.php.template`
   - Integration tests: `tests/Entrypoint/Http/Controllers/YourControllerTest.php.template`
2. **Use Given-When-Then format** for test names and structure
3. **Include meaningful assertions**
4. **Test template generation** if applicable

Example test template:

```php
<?php

declare(strict_types=1);

namespace {{APP_NAMESPACE}}\Tests\Shared\Domain;

use {{APP_NAMESPACE}}\Shared\Domain\ValueObject;
use PHPUnit\Framework\TestCase;

class ValueObjectTest extends TestCase
{
    public function testValueObjectCanBeInstantiated(): void
    {
        // Given & When
        $valueObject = new class extends ValueObject {
            // Anonymous class extending ValueObject for testing
        };

        // Then
        $this->assertInstanceOf(ValueObject::class, $valueObject);
    }
}
```

## 🔧 Code Standards

### PHP Code Style

- Follow **PSR-12** coding standards
- Use **strict typing** (`declare(strict_types=1);`)
- Keep methods **small** (10-20 lines maximum)
- Use **meaningful names** for variables and functions
- Add **type hints** for all parameters and return types
- Pass **PHPStan Level 6** static analysis

### Template Code Style

- Use **consistent indentation** (4 spaces)
- Include **meaningful comments**
- Follow **PSR-12** even in templates
- Use **descriptive placeholder names**

### Documentation

- Update **README.md** for new features
- Add **inline comments** for complex logic
- Document **new Makefile targets**
- Update **this CONTRIBUTING.md** when needed

## 📝 Pull Request Process

### Before Submitting

1. **Run all quality checks:**
   ```bash
   make quality  # Runs PHPStan + Tests
   ```

2. **Verify setup works:**
   ```bash
   make reset  # Clean slate
   make setup  # Fresh setup
   make quality  # Verify everything works
   ```

3. **Check code style:**
   - Ensure PSR-12 compliance
   - Pass PHPStan Level 6 analysis
   - Verify all templates are processed correctly
   - Test with different project names/namespaces

### PR Guidelines

1. **Clear title** describing the change
2. **Detailed description** explaining:
   - What was added/changed
   - Why the change was needed
   - How to test the changes
3. **Reference related issues** if applicable
4. **Include screenshots** for UI changes (if any)

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Documentation update
- [ ] Template addition/modification

## Testing
- [ ] All tests pass
- [ ] Setup script works correctly
- [ ] Templates generate expected output
- [ ] Manual testing completed

## Checklist
- [ ] Code follows PSR-12 standards
- [ ] PHPStan Level 6 passes
- [ ] Templates use correct placeholders
- [ ] Documentation updated
- [ ] No breaking changes
```

## 🐛 Issue Guidelines

### Bug Reports

When reporting bugs, include:

1. **Environment details** (OS, Docker version, etc.)
2. **Steps to reproduce**
3. **Expected vs actual behavior**
4. **Error messages/logs**
5. **Project configuration** (relevant Makefile variables)

### Feature Requests

For new features, provide:

1. **Clear description** of the feature
2. **Use case** and motivation
3. **Proposed implementation** (if you have ideas)
4. **Impact** on existing functionality

### Template Issues

For template-related issues:

1. **Template file** that's problematic
2. **Generated output** vs expected output
3. **Setup script output** (if relevant)
4. **Project configuration** used

## 🔄 Release Process

### Versioning

We follow **Semantic Versioning** (MAJOR.MINOR.PATCH):

- **MAJOR**: Breaking changes to the skeleton
- **MINOR**: New features, new templates
- **PATCH**: Bug fixes, documentation updates

### Release Checklist

Before each release:

1. ✅ All tests pass
2. ✅ Documentation updated
3. ✅ CHANGELOG.md updated
4. ✅ Version numbers updated
5. ✅ Setup script tested thoroughly
6. ✅ Templates generate correctly

## 🆘 Getting Help

If you need help:

1. **Check existing issues** for similar problems
2. **Read the documentation** (README.md, this file)
3. **Create a new issue** with detailed information
4. **Join discussions** in existing issues

## 📚 Additional Resources

- [Symfony Documentation](https://symfony.com/doc/current/)
- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html)
- [Hexagonal Architecture](https://alistair.cockburn.us/hexagonal-architecture/)
- [Docker Documentation](https://docs.docker.com/)
- [PSR-12 Coding Standards](https://www.php-fig.org/psr/psr-12/)

---

Thank you for contributing to the LAMP Skeleton! 🎉

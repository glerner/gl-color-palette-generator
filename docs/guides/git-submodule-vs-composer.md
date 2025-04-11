# Git Submodule vs Composer Package for Testing Framework Integration

## Background and Decision Context

When designing a "Separate Testing Framework Repository with Development Tools," a key question emerged:

> Does this design work for a project where another developer, who already has their environment set up, can install only the PHPUnit part (which for experienced developers, would be the hard part)?

This document explores two primary approaches to integrating a separate testing framework repository into WordPress plugin projects, with a focus on developer experience and flexibility.

## Comparison Overview

| Factor | Git Submodules | Composer Packages |
|:-------|:--------------|:------------------|
| **Setup Complexity** | Moderate | Higher |
| **Developer Experience** | Requires Git knowledge | Requires Composer knowledge |
| **Versioning** | Manual tracking | Automatic via semantic versioning |
| **Update Process** | `git submodule update` | `composer update` |
| **Flexibility for Experienced Devs** | Direct access to files | Abstracted as a dependency |
| **Learning Curve** | Git commands | Composer + Package publishing |

## Option A: Git Submodules

### What Are Git Submodules?

Git submodules allow you to include one Git repository inside another as a subdirectory. This creates a link to a specific commit in the external repository rather than copying the code.

### Implementation Steps

1. **Create your testing framework repository**:
```bash
mkdir ~/sites/wp-phpunit-framework
cd ~/sites/wp-phpunit-framework
git init
# Add your testing framework files
git add .
git commit -m "Initial commit of testing framework"
git remote add origin https://github.com/glerner/wp-phpunit-framework.git
git push -u origin main
```

2. **Add the submodule to your plugin project**:
```bash
cd ~/sites/gl-color-palette-generator
git submodule add https://github.com/glerner/wp-phpunit-framework.git tests/framework
git commit -m "Add testing framework as submodule"
```

3. **For developers cloning your plugin project**:
```bash
git clone https://github.com/glerner/gl-color-palette-generator.git
cd gl-color-palette-generator
git submodule init
git submodule update
```

4. **To update the framework in your plugin**:
```bash
# In the main project
git submodule update --remote tests/framework
git commit -m "Update testing framework submodule"
```

### Advantages

- **Direct Access**: Developers can directly access and modify framework files if needed
- **No Publishing Required**: No need to publish packages to Packagist or set up private repositories
- **Version Control**: Clear tracking of which framework version is being used
- **Independence**: Framework can be developed independently of plugins using it
- **Simplicity**: No need to understand Composer's package system beyond basic dependencies

### Disadvantages

- **Git Knowledge Required**: Developers need to understand Git submodules
- **Extra Clone Steps**: Requires additional commands when cloning the repository
- **Potential Confusion**: Submodules can be confusing for developers new to Git
- **Manual Updates**: Updates to the framework must be manually pulled and committed

## Option B: Composer Packages

### What Are Composer Packages?

Composer is PHP's dependency manager. Packages are reusable libraries that can be required in projects via Composer.

### Implementation Steps

1. **Prepare your framework as a Composer package**:
   - Create a proper `composer.json` in your framework repository:
     ```json
     {
       "name": "yourusername/wp-phpunit-framework",
       "description": "WordPress PHPUnit Testing Framework",
       "type": "library",
       "require": {
         "php": ">=7.4",
         "phpunit/phpunit": "^9.0"
       },
       "autoload": {
         "psr-4": {
           "YourNamespace\\TestFramework\\": "src/"
         }
       }
     }
     ```

2. **Use a private repository approach** (simpler than Packagist):
   - In your plugin's `composer.json`:
     ```json
     {
       "repositories": [
         {
           "type": "vcs",
           "url": "https://github.com/yourusername/wp-phpunit-framework.git"
         }
       ],
       "require": {
         "yourusername/wp-phpunit-framework": "dev-main"
       }
     }
     ```

3. **Install the dependency**:
   ```bash
   composer require yourusername/wp-phpunit-framework:dev-main
   ```

### Advantages

- **Standard PHP Workflow**: Follows standard PHP dependency management practices
- **Semantic Versioning**: Can leverage semantic versioning for updates
- **Autoloading**: Automatic class autoloading
- **Dependency Management**: Handles nested dependencies automatically
- **Familiar to PHP Devs**: Most PHP developers are familiar with Composer

### Disadvantages

- **Publishing Required**: Requires understanding how to publish packages
- **Less Direct Access**: Files are in vendor directory and shouldn't be directly modified
- **More Complex Setup**: More complex initial setup, especially for private packages
- **Learning Curve**: Steeper learning curve for package maintenance

## Recommendation for WordPress Plugin Testing Frameworks

For a WordPress plugin testing framework that needs to be accessible to developers with varying experience levels:

### Use Git Submodules When:

- You're still learning Git and Composer
- You want direct control over the framework files
- You prefer simplicity over standardization
- You want to make frequent changes to both repositories
- Your team is comfortable with Git but not necessarily with Composer package publishing

### Use Composer Packages When:

- You're comfortable with Composer package management
- You have multiple projects that will use the framework
- You want to leverage semantic versioning
- Your team is already using Composer extensively
- You want to follow PHP community standards for dependency management

## Practical Considerations for WordPress Developers

1. **WordPress Development Culture**: Many WordPress developers are more familiar with Git than with Composer package publishing.

2. **Learning Path**: Git submodules can be a stepping stone to learning more advanced dependency management.

3. **Team Composition**: Consider the technical background of all developers who will work with the codebase.

4. **Long-term Maintenance**: Composer packages may be easier to maintain across multiple projects in the long run.

5. **Hybrid Approach**: You can start with Git submodules and transition to Composer packages as your team's expertise grows.

## Conclusion

For the specific context of a WordPress testing framework where experienced developers should be able to easily access just the PHPUnit part, Git submodules provide a more direct and accessible approach initially. As your project and team mature, you can consider transitioning to a Composer package for better standardization and dependency management.

The most important factor is that your testing framework remains modular and well-documented, regardless of the integration method chosen.

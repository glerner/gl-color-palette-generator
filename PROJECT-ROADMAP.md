# GL Color Palette Generator Project Roadmap

This document outlines the revised approach for the GL Color Palette Generator plugin. After assessment, we've determined that completing the test organization work and then rebuilding the plugin code from scratch will be more efficient than trying to fix the existing implementation.

## Phase 1: Complete Current Test Organization ✅

### ✅ Finish Test Organization (Completed)
- Processed all 117 test files through test analysis and documented in `test_analysis_results.txt`
- Finalized organizing tests by type (unit, wp-mock, integration)
- Verified all tests are in appropriate directories with correct namespaces
- Validated that the testing structure is fully functional
- Created comprehensive test-to-implementation mapping in `test_mapping_log.md`

### ⏳ Test Quality Assurance (In Progress)
- Resolve test class naming conflicts and duplicate declarations `scripts/find-duplicate-classes.sh` saved to `docs/analysis/duplicate-class-names.txt`
- Configure PHPStan to validate test files for errors
- Ensure method signatures in implementations match interface declarations
- Document test tooling setup in development documentation

### ✅ Apply Interface Fixes (Completed)
- Ran `bin/interface-test-fixer.sh` to identify interface issues
- Fixed interface implementations in test files
- Updated interface references for consistency
- Documented arch itectural insights in `interface_issues.md`

### ⏳ Comprehensive Test Quality Review (In Progress)
- Review all test files with the understanding that they define expected behavior for the rewrite
- Evaluate if tests are testing the right functionality (regardless of current implementation)
- Update test docblocks to clearly document expected behavior for the rewrite
- Identify any test files that should be deprecated or removed in the rebuild
- Create and review `implementation_files_needing_tests.md` to document files without test coverage
- Refactor tests to reflect the intended design, not the current implementation
- Standardize test method naming and organization across files
- Create a mapping between tests and planned features for the rewrite
- Document any conceptual or architectural changes needed in the tests

## Phase 2: Extract Reusable Testing Framework

### 1️⃣ Create Modular Testing Framework Repository
- Extract the PHPUnit testing structure to a standalone repository
- Design a modular architecture with clear separation of concerns:
  - Core testing framework (base classes, bootstrap files)
  - Directory structure templates for different test types (unit, wp-mock, integration)
  - Configuration templates (can be used separately)
  - Development tooling (optional)
- Create a minimal composer.json that only requires PHPUnit and essential dependencies
- Provide optional configuration files for PHPStan, PHPCS, etc.
- Document installation options:
  - Basic: Just the testing framework for experienced developers
  - Standard: Framework with recommended configurations
  - Complete: Full development environment setup
- Create comprehensive examples for each test type
- Design testing framework for installation via Git or via Composer
  - See documentation in `docs/guides/preparing-projects-for-dual-git-composer-deployment.md` and `docs/guides/git-submodule-vs-composer.md`
- Set up CI/CD to validate the framework itself

### 2️⃣ Document Testing Framework
- Create comprehensive documentation for the testing framework
- Include setup instructions for new projects
- Document test types and when to use each
- Provide examples of mocking strategies

## Phase 3: Prepare for Rebuild

### 3️⃣ Extract Specifications from Tests
- Review test files to understand expected functionality
- Create a test-to-feature mapping document
- Identify core vs. peripheral functionality
- Extract business rules and requirements from tests
- Document API integrations and their expected behaviors
- Analyze existing codebase for valuable components and libraries:
  - Identify external libraries worth keeping (e.g., color-namer for artist-inspired naming)
  - Document useful utility functions and helpers
  - Catalog reusable algorithms and patterns
  - Evaluate existing color manipulation code for potential reuse
  - Preserve well-structured constants and definitions:
    - Keep `includes/interfaces/interface-color-constants.php` (rename to `class-color-constants.php`)
    - Maintain color roles, schemes, and WCAG accessibility requirements

### 4️⃣ Preserve Valuable Documentation
- Identify which documentation is still relevant
- Update architectural and design documentation
- Create a documentation plan for the new implementation

### 5️⃣ Set Up New Project Structure
- Archive current repository:
  - Create final commit with message "Archive before rewrite"
  - Tag the final version (e.g., `v0.9.0-legacy`)
  - Push all changes and tags to remote repository
- Create new project structure:
  - Rename current local folder to `~/sites/gl-gpg-v0` for reference
  - Make folder and copy Git repository to `~/sites/gl-color-palette-generator`
  - Set up proper folder structure for a WordPress plugin with Composer
  - Make a "Preparing for code restructuring" commit
```bash
  # Copy Git repository (preserving history)
  mkdir -p ~/sites/gl-color-palette-generator
  git clone ~/sites/gl-gpg-v0 ~/sites/gl-color-palette-generator
  # Make the restructuring commit
  cd ~/sites/gl-color-palette-generator
  git commit -m "docs: Preparing for code restructuring"
```

### 6️⃣ Integrate Testing Framework and Configure Development Tools
- Integrate testing framework using Git submodules:
  - `git submodule add [testing-framework-repo-url] tests/framework`
  - This keeps the testing framework as a separate repository
- Import development tool configurations (PHPStan, PHPCS, etc.) from testing framework
- Extend Composer configuration with plugin-specific dependencies
- Set up JavaScript testing framework (Jest, etc.)
- Create build process for JS assets (webpack, etc.)
- Configure plugin-specific CI/CD pipeline
- Document the plugin development environment setup
- Migrate any additional configurations needed from original project
- Configure development environment (in User and plugin development folders)
- Commit restructured codebase

## Phase 4: Rebuild Implementation

### 7️⃣ Implement Core Functionality
- Start with essential color manipulation classes
  - Color generation (primary, secondary, tertiary, accent)
  - Color variations (lighter, light, dark, darker)
  - Neutral colors generation
  - Color properties (name, emotional attributes, hex codes)
  - WCAG AAA contrast validation and adjustment
- Follow WordPress coding standards from the beginning
- Use proper namespaces and class organization
- Write tests for each component as you build

### 8️⃣ Develop WordPress Integration
- Implement admin pages and settings
- Create basic user interface for plugin functionality
- Set up WordPress hooks and filters
- Implement shortcodes and blocks

### 9️⃣ Implement AI Color Palette Generation
- Build questionnaire interface for users to specify palette preferences
  - Business type/industry questions
  - Ideal client/audience questions
  - Brand personality questions
  - Color preference questions
- Create stub implementation that returns sample palettes
- Implement basic algorithm for generating palettes without AI
- Implement generating palettes from starting color(s)
- Add artist-inspired naming system for palettes
- Add emotional attribute tagging for palettes
- Add user feedback collection for palette suggestions

### 🔟 Implement Provider Integrations
- Build AI API integrations with proper abstraction
- Ensure consistent interface implementation
- Add comprehensive error handling
- Test each provider thoroughly

### 1️⃣1️⃣ Complete WordPress Integration
- Add shortcodes and block editor integration
- Set up proper hooks and filters
- Ensure proper asset management
- Implement front-end display components
- Add theme integration features
- Implement output formats:
  - WordPress color palette display
  - Theme Style Variations (theme.json)
  - Light and Dark themes with CSS light-dark() support
  - Color permutations generator
  - Downloadable .zip of all variations

### 1️⃣2️⃣ Develop JavaScript Components
- Implement front-end functionality with modern JS practices
- Ensure proper integration with WordPress enqueuing
- Test JavaScript functionality across browsers

### 1️⃣3️⃣ Finalize and Document
- Complete user documentation
- Finalize developer documentation
- Create deployment and update procedures
- Prepare for release

## Methodical Approach

Throughout all phases, we'll maintain the same methodical approach used for test organization and interface fixes:

1. **Analyze**: Create scripts to analyze the current state when needed
2. **Document**: Generate results files documenting issues or requirements
3. **Plan**: Create detailed plans for addressing each component
4. **Batch Process**: Work in manageable batches with clear boundaries
5. **Track**: Document progress and changes systematically
6. **Validate**: Test each component thoroughly as it's developed

## Benefits of This Approach

1. **Clean Architecture**: Start with proper WordPress coding standards
2. **Incremental Testing**: Test each component as it's built
3. **Clear Documentation**: Document as we go rather than retrofitting
4. **Reusable Components**: Create modular, reusable code
5. **Maintainable Codebase**: Easier to maintain and extend
6. **Valuable Testing Framework**: Create a reusable testing structure for future projects

This revised approach preserves the valuable work done on test organization while providing a clear path to a properly implemented plugin.

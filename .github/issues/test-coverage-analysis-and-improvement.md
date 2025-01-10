# Test Coverage Analysis and Improvement

## Problem
Need to identify classes without tests or with inadequate test coverage.

## Tools Available
1. **PHPUnit Code Coverage**
   - Generate with `--coverage-html reports/coverage`
   - Shows which classes/methods are untested
   - Requires Xdebug or PCOV

2. **Infection Testing**
   - Mutation testing tool
   - Checks quality of existing tests
   - Can identify weak/insufficient tests

3. **PHPStan**
   - Can be configured to warn about untested classes
   - Use with `phpstan/phpstan-phpunit`

## Existing Configuration
1. **PHPUnit Coverage Setup** (`phpunit.xml`):
   ```xml
   <coverage processUncoveredFiles="true">
	   <include>
		   <directory suffix=".php">includes</directory>
	   </include>
	   <exclude>
		   <directory suffix=".php">vendor</directory>
		   <directory suffix=".php">tests</directory>
	   </exclude>
	   <report>
		   <clover outputFile="build/logs/clover.xml"/>
		   <html outputDirectory="build/coverage"/>
	   </report>
   </coverage>
   ```

2. **Lando Coverage Commands** (.lando.yml):

   - `lando test:coverage` - Generates HTML coverage report
   - `lando test:unit` - Runs unit tests
   - Coverage enabled via Xdebug configuration:
     ```yaml
     XDEBUG_MODE: 'debug,develop,trace,coverage'
     ```

## Tasks
- [ ] Review existing coverage reports in `build/coverage`
- [ ] Set minimum coverage thresholds in `phpunit.xml`
- [ ] Add coverage checks to CI pipeline using existing `clover.xml` output
- [ ] Create report of untested classes based on current coverage data


## Success Criteria
- [ ] All core classes have at least basic test coverage
- [ ] Critical functionality has >80% coverage
- [ ] Automated reports in CI showing coverage metrics

## Current Gaps
- `class-contrast-checker.php` lacks comprehensive tests
- Need to identify other classes with missing/incomplete tests
- No automated coverage reporting in CI pipeline (`clover.xml` exists but not used)

## Priority Classes for Testing
1. Accessibility-related classes
   - `class-contrast-checker.php` - Core contrast calculation logic
   - `class-color-accessibility.php` - WCAG compliance checks
2. Core color management classes
   - Color generation
   - Palette management
3. API providers
   - REST endpoints
   - Data validation

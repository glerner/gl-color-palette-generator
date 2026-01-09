# Code Inventory Strategy

This document supplements the `PROJECT-ROADMAP.md` by outlining a strategy for tracking and organizing code components during the rewrite process.

## Core Principles

1. **Preserve Valuable Work**: Retain and leverage existing documentation and well-designed components
2. **Group by Functionality**: Organize classes and methods by functional groups
3. **Modular Development**: Enable focused development on one functional group at a time
4. **Documentation Integration**: Connect code inventory with existing documentation

## Documentation Preservation Strategy

1. **Documentation Audit**:
   - Review all files in the `/docs/` directory
   - Categorize documentation as:
     - Architecture references (preserve and update)
     - Problem documentation (preserve for historical context)
     - Implementation guides (evaluate for relevance)
     - Test specifications (preserve as requirements)
     - Developer guides (preserve and enhance)
     - User guides (preserve and update)

2. **Developer Documentation**:
   - Preserve and update development environment guides:
     - Lando setup for WordPress development
     - PHPUnit testing system configuration
     - Local development workflows
     - Contribution guidelines

3. **User Documentation**:
   - Preserve and enhance guides for:
     - Theme developers using the color palette system
     - End users implementing color palettes
     - Business owners understanding color psychology
     - Designers working with the generated palettes

4. **Documentation Index**:
   - Create a central index of all documentation files
   - Tag each with relevance to specific functional groups
   - Note which documents contain valuable references for the rewrite
   - Link related documentation across categories

## Functional Groups

We will organize the codebase into the following functional groups:

1. **User Interface**
   - Admin pages and forms
   - Color palette display components
   - User input validation
   - Interactive elements
   - Questionnaire interface
   - Gutenberg blocks for embedding palette displays

2. **Core Color Manipulation**
   - Color representation and conversion
   - Color scheme generation
   - Color accessibility validation
   - Color constants and relationships

3. **Palette Management**
   - Palette creation and modification
   - Palette storage and retrieval
   - Palette validation
   - Palette metadata

4. **AI Generation**
   - AI provider integration
   - Fallback algorithms
   - Result processing

5. **WordPress Integration**
   - Settings API integration
   - Shortcodes (as complement to blocks)
   - Hooks and filters
   - Data persistence

6. **External Providers**
   - Provider interfaces
   - Specific provider implementations
   - API communication
   - Error handling

7. **Output Formats**
   - CSS generation
   - JSON export
   - Visual representations
   - Documentation generation

## Code Inventory Structure

For each functional group, we will create:

1. **Class Inventory** (`/docs/code-inventory/[group]-classes.md`):
   ```markdown
   # [Group Name] Classes

   ## Overview
   Brief description of this functional group's purpose.

   ## Class Listing

   ### [ClassName]
   - **Purpose**: What this class does
   - **Relationships**: Parent classes, interfaces, composed classes
   - **Key Responsibilities**: Main tasks this class handles
   - **Notes**: Design decisions, special considerations

   ### [AnotherClass]
   ...
   ```

2. **Method Inventory** (`/docs/code-inventory/[group]-methods.md`):
   ```markdown
   # [Group Name] Methods

   ## [ClassName]

   ### methodName()
   - **Purpose**: What this method does
   - **Parameters**: Expected inputs
   - **Returns**: Expected outputs
   - **Exceptions**: Error conditions
   - **Notes**: Special considerations

   ### anotherMethod()
   ...
   ```

3. **Constants Inventory** (`/docs/code-inventory/[group]-constants.md`):
   ```markdown
   # [Group Name] Constants

   ## [ClassName or Namespace]

   ### CONSTANT_NAME
   - **Value**: The constant's value
   - **Purpose**: What this constant represents
   - **Usage**: Where/how this constant is used
   ```

## Implementation Workflow

1. **Preparation Phase**:
   - Create directory structure for code inventory
   - Audit existing documentation
   - Create documentation index
   - Design specifications for each functional group

2. **UI-First Development**:
   - Implement the user interface elements first
   - Create stub implementations that return sample data
   - Ensure the plugin activates and runs without errors
   - Validate UI elements match user requirements

3. **For Each Functional Group**:
   1. Review relevant existing documentation
   2. Design specifications based on requirements (not existing tests)
   3. Evaluate if any existing tests align with new specifications
   4. Create class, method, and constants inventories
   5. Implement the functional group with proper interfaces
   6. Create stub implementations of dependent classes
   7. Ensure the plugin remains functional throughout development
   8. Write tests for the new implementation
   9. Document design decisions and implementation notes

4. **Continuous Integration**:
   - Test each class with its dependencies as development progresses
   - Replace stub implementations with actual code incrementally
   - Maintain a functioning plugin at all stages of development
   - Regularly validate that all UI elements work with the evolving backend

## Benefits of This Approach

1. **User-Focused Development**: Early UI implementation ensures focus on user needs
2. **Always Functional Plugin**: Maintaining a working plugin throughout development
3. **Focused Development**: When implementing a specific functional group, you only need to reference the relevant inventory files
4. **Consistent Documentation**: Standardized format makes information easy to find
5. **Preservation of Knowledge**: Valuable insights from existing documentation are retained
6. **Modular Progress**: Clear tracking of completion status by functional group
7. **Simplified Collaboration**: Team members can work on different functional groups with minimal conflicts

## AI-Assisted Development Strategy

When working with AI assistants on this project, the code inventory structure provides significant advantages for managing context and focusing development efforts.

### Context Window Management

The AI context window (approximately 180,000 tokens for Claude 3.7 Sonnet) can be efficiently utilized by loading only the relevant functional group documentation when needed:

1. **Focused Context Loading**: When working on a specific functional group, load only the three inventory files for that group:
   ```
   /docs/code-inventory/[group]-classes.md
   /docs/code-inventory/[group]-methods.md
   /docs/code-inventory/[group]-constants.md
   ```

2. **Context Switching**: Use a simple command pattern to change context between functional groups:
   ```
   "Load context for [Group Name] functional group"
   ```

3. **Memory Efficiency**: Each functional group's documentation (approximately 5,000-10,000 tokens) leaves ample room for code, conversation, and additional context.

4. **Boundary Management**: The documented relationships between classes help manage cases where you need to understand interactions across functional groups.

### AI Development Workflow

1. **Initial Context Setup**:
   - Load the relevant functional group inventory files
   - Include any specific design documents or requirements
   - Reference related test specifications

2. **Implementation Process**:
   - Focus on one class or component at a time
   - Implement methods according to the inventory specifications
   - Validate against documented relationships and dependencies
   - Update inventory documentation as implementation progresses

3. **Cross-Group Integration**:
   - When working across functional groups, temporarily load both inventories
   - Focus on interface points and dependency management
   - Document any changes to cross-group relationships

4. **Documentation Updates**:
   - Update inventory files as implementation progresses
   - Mark completed components in the inventory
   - Document any design decisions or implementation notes

## Blocks and Shortcodes Strategy

### Primary: Gutenberg Blocks
Gutenberg blocks will be the primary interface for embedding plugin functionality in posts and pages:

1. **Modern WordPress Integration**: Aligns with WordPress core development direction
2. **Rich Interactive Experience**: Provides visual editing and live previews
3. **Enhanced User Control**: Offers intuitive UI controls for customization
4. **Responsive Design**: Better mobile compatibility and responsive behavior

We will implement the following blocks:
- **GL Color Palette Block**: Display a specific palette with customization options
- **GL Color Palette Generator Block**: Embed the palette generator form
- **GL Color Palette Preview Block**: Show a live preview with interactive elements

### Complementary: Shortcodes
WordPress shortcodes will complement blocks for specific use cases:

1. **Classic Theme Compatibility**: Support for people not using Gutenberg editor, and for sites with Classic themes
2. **Legacy Content Support**: Easy migration path for existing content
3. **Programmatic Embedding**: Allow developers to embed palettes in classic-theme templates
4. **Simpler Implementation**: Quick implementation for certain features
5. **Compatible with any Page Builder**: Though most builders work with Blocks, there are some where shortcodes are easier

We will implement shortcodes that mirror block functionality:
- `[gl_color_palette id="123"]` - Display a specific palette
- `[gl_color_palette_generator]` - Embed the palette generator form
- `[gl_color_palette_preview]` - Show a live preview area

### Palette Display Page
Both blocks and shortcodes will support a comprehensive palette display that includes:

1. **Complete Color Information**: All colors in the palette with their properties
2. **Mood and Business Context**: How the palette fits the business needs and emotional attributes
3. **Color Samples**: Visual representation of each color
4. **Accessibility Information**: Examples of text on background colors with adequate color contrast
5. **Color Variables**: Generated CSS variables for theme integration
6. **Palette Variations**: Display of all palette variations (6 or 24 options)
7. **Usage Examples**: Practical applications of the palette

## Testing and Quality Assurance Strategy

### Testing Stub Implementations

During the incremental development process, we'll use a hybrid approach for testing stub implementations:

1. **Passing Tests with Clear Markers**:
   - Write tests that pass but clearly indicate they're testing stub implementations
   - Use PHPUnit annotations to mark stub tests: `@group stub` or `@group incomplete`
   - Include docblock comments with `@todo Implement actual functionality`
   - Assert that stub methods return expected placeholder data

   ```php
   /**
    * @test
    * @group stub
    * @todo Implement actual color generation functionality
    */
   public function test_generate_palette_returns_expected_stub_data(): void
   {
       $generator = new Palette_Generator();
       $palette = $generator->generate_palette(['primary' => '#336699']);

       // Assert stub returns expected structure with placeholder data
       $this->assertInstanceOf(Palette::class, $palette);
       $this->assertNotEmpty($palette->get_colors());

       // Document expected real behavior
       $this->markTestIncomplete('Stub implementation - will eventually generate proper palette');
   }
   ```

2. **Skipped Tests for Unimplemented Features**:
   - Use `markTestSkipped()` for features not yet implemented
   - Document expected behavior in skipped tests
   - Create a clear roadmap of what needs to be implemented

   ```php
   /**
    * @test
    */
   public function test_palette_accessibility_validation(): void
   {
       $this->markTestSkipped('Accessibility validation not yet implemented');

       // Document expected behavior even though test is skipped
       // $palette = new Palette(['primary' => '#336699']);
       // $this->assertTrue($palette->meets_wcag_aa_requirements());
   }
   ```

3. **Test Organization**:
   - Group tests by functional area
   - Use the same test class for both stub and final implementations
   - Tests evolve alongside the implementation, gradually removing stub markers
   - Run stub tests separately with `--group stub` when needed for progress tracking

This approach maintains a passing test suite while clearly documenting what's implemented vs. stubbed.

### PHPStan Integration

We'll integrate PHPStan into our development workflow to ensure type safety and catch potential issues early:

1. **Progressive Level Approach**:
   - Start with PHPStan level 3 for initial development
   - Increase to level 5 as the codebase matures
   - Target level 8 for final production code

2. **Integration Points**:
   - Run PHPStan after implementing each class
   - Configure Continuous Integration (CI) to automatically run PHPStan on all Pull Requests (PRs)
     - This means GitHub Actions or similar CI tool will automatically check code quality
     - PRs that don't meet PHPStan requirements will be flagged before merging
   - Maintain zero PHPStan errors from the beginning of the rewrite
     - Start with a clean slate and strict standards
     - No need for a baseline since we're writing new code

3. **Custom Rules**:
   - Add WordPress-specific PHPStan rules
   - Create custom rules for plugin-specific patterns
   - Configure strict type checking for new code

4. **Documentation**:
   - Document PHPStan exceptions with clear reasoning
   - Include PHPStan level in class documentation
   - Track PHPStan compliance in code inventory

By running PHPStan frequently throughout development, we'll catch type-related issues early and maintain a high level of code quality.

## Next Steps

1. Create the code inventory directory structure
2. Begin the documentation audit
3. Start with the User Interface group as the foundation
4. Implement stub functionality for all UI components
5. Proceed through remaining functional groups with continuous integration

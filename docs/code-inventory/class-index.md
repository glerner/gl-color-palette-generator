# Class Index

This document provides a master index of all classes in the GL Color Palette Generator plugin, with links to their documentation and information about their implementation location.

## Naming Conventions

Our class naming follows these patterns to indicate a class's role:

- **Models**: Base name alone (e.g., `Palette`) - represents data structures
- **Views/UI**: Suffix with `_Display`, `_Block`, or `_Page` (e.g., `Palette_Display`) - handles rendering
- **Services**: Suffix with specific action (e.g., `Palette_Generator`, `Palette_Storage`) - handles business logic
- **Controllers**: Suffix with `_Controller` (e.g., `Admin_Controller`) - coordinates between models, views, and services

## File Structure

File paths follow WordPress conventions:
- Class files are prefixed with `class-` and use kebab-case
- Files are organized by functional group, not by MVC role
- Namespaces reflect the directory structure

## [User Interface Classes](/docs/code-inventory/user-interface/)

These classes handle rendering and user interaction. They typically have suffixes like `_Display`, `_Block`, `_Page`, or `_Form`.

| Class Name | Documentation | Namespace | Implementation File |
|------------|--------------|-----------|---------------------|
| Admin_Page | [User Interface Classes](/docs/code-inventory/user-interface/classes.md#admin_page) | `GL_Color_Palette_Generator\UI` | `/includes/ui/class-admin-page.php` |
| Settings_Page | [User Interface Classes](/docs/code-inventory/user-interface/classes.md#settings_page) | `GL_Color_Palette_Generator\UI` | `/includes/ui/class-settings-page.php` |
| Palette_Display | [User Interface Classes](/docs/code-inventory/user-interface/classes.md#palette_display) | `GL_Color_Palette_Generator\UI\Components` | `/includes/ui/components/class-palette-display.php` |
| Business_Questionnaire | [User Interface Classes](/docs/code-inventory/user-interface/classes.md#business_questionnaire) | `GL_Color_Palette_Generator\UI\Components` | `/includes/ui/components/class-business-questionnaire.php` |
| Palette_Block | [User Interface Classes](/docs/code-inventory/user-interface/classes.md#palette_block) | `GL_Color_Palette_Generator\UI\Blocks` | `/includes/ui/blocks/class-palette-block.php` |
| Palette_Generator_Block | [User Interface Classes](/docs/code-inventory/user-interface/classes.md#palette_generator_block) | `GL_Color_Palette_Generator\UI\Blocks` | `/includes/ui/blocks/class-palette-generator-block.php` |

## [Color Manipulation Classes](/docs/code-inventory/color-manipulation/)

These classes handle color operations, transformations, and accessibility calculations. They form the foundation of the plugin's color functionality.

| Class Name | Documentation | Namespace | Implementation File |
|------------|--------------|-----------|---------------------|
| Color | [Color Classes](/docs/code-inventory/color-manipulation/classes.md#color) | `GL_Color_Palette_Generator\Color` | `/includes/color/class-color.php` |
| Color_Accessibility | [Color Classes](/docs/code-inventory/color-manipulation/classes.md#color_accessibility) | `GL_Color_Palette_Generator\Color` | `/includes/color/class-color-accessibility.php` |
| Color_Converter | [Color Classes](/docs/code-inventory/color-manipulation/classes.md#color_converter) | `GL_Color_Palette_Generator\Color` | `/includes/color/class-color-converter.php` |
| Color_Namer | [Color Classes](/docs/code-inventory/color-manipulation/classes.md#color_namer) | `GL_Color_Palette_Generator\Color` | `/includes/color/class-color-namer.php` |

## [Palette Management Classes](/docs/code-inventory/palette-management/)

These classes handle the creation, storage, and manipulation of color palettes. They serve as the core business logic of the plugin.

| Class Name | Documentation | Namespace | Implementation File |
|------------|--------------|-----------|---------------------|
| Palette | [Palette Management Classes](/docs/code-inventory/palette-management/classes.md#palette) | `GL_Color_Palette_Generator\Palette` | `/includes/palette/class-palette.php` |
| Palette_Generator | [Palette Management Classes](/docs/code-inventory/palette-management/classes.md#palette_generator) | `GL_Color_Palette_Generator\Palette` | `/includes/palette/class-palette-generator.php` |
| Palette_Storage | [Palette Management Classes](/docs/code-inventory/palette-management/classes.md#palette_storage) | `GL_Color_Palette_Generator\Palette` | `/includes/palette/class-palette-storage.php` |
| Palette_Transformer | [Palette Management Classes](/docs/code-inventory/palette-management/classes.md#palette_transformer) | `GL_Color_Palette_Generator\Palette` | `/includes/palette/class-palette-transformer.php` |
| Color_Harmony | [Palette Management Classes](/docs/code-inventory/palette-management/classes.md#color_harmony) | `GL_Color_Palette_Generator\Palette` | `/includes/palette/class-color-harmony.php` |

## [AI Generation Classes](/docs/code-inventory/ai-generation/)

These classes handle interaction with AI services for generating color palettes based on business context and design requirements.

| Class Name | Documentation | Namespace | Implementation File |
|------------|--------------|-----------|---------------------|
| AI_Provider | [AI Generation Classes](/docs/code-inventory/ai-generation/classes.md#ai_provider) | `GL_Color_Palette_Generator\AI` | `/includes/ai/class-ai-provider.php` |
| OpenAI_Provider | [AI Generation Classes](/docs/code-inventory/ai-generation/classes.md#openai_provider) | `GL_Color_Palette_Generator\AI\Providers` | `/includes/ai/providers/class-openai-provider.php` |
| Claude_Provider | [AI Generation Classes](/docs/code-inventory/ai-generation/classes.md#claude_provider) | `GL_Color_Palette_Generator\AI\Providers` | `/includes/ai/providers/class-claude-provider.php` |
| Prompt_Generator | [AI Generation Classes](/docs/code-inventory/ai-generation/classes.md#prompt_generator) | `GL_Color_Palette_Generator\AI` | `/includes/ai/class-prompt-generator.php` |
| AI_Response_Parser | [AI Generation Classes](/docs/code-inventory/ai-generation/classes.md#ai_response_parser) | `GL_Color_Palette_Generator\AI` | `/includes/ai/class-ai-response-parser.php` |
| AI_Controller | [AI Generation Classes](/docs/code-inventory/ai-generation/classes.md#ai_controller) | `GL_Color_Palette_Generator\AI` | `/includes/ai/class-ai-controller.php` |

## [Interfaces](/docs/code-inventory/interfaces/)

These interfaces define constants and contracts that classes must implement, ensuring consistency across the plugin.

| Interface Name | Documentation | Namespace | Implementation File |
|---------------|--------------|-----------|---------------------|
| Color_Constants | [Color Interfaces](/docs/code-inventory/interfaces/color.md#color_constants) | `GL_Color_Palette_Generator\Color\Interfaces` | `/includes/interfaces/interface-color-constants.php` |
| Color_Harmony_Constants | [Color Interfaces](/docs/code-inventory/interfaces/color.md#color_harmony_constants) | `GL_Color_Palette_Generator\Color\Interfaces` | `/includes/interfaces/interface-color-harmony-constants.php` |
| Palette_Provider | [Palette Interfaces](/docs/code-inventory/interfaces/palette.md#palette_provider) | `GL_Color_Palette_Generator\Palette\Interfaces` | `/includes/interfaces/interface-palette-provider.php` |
| AI_Provider_Interface | [AI Interfaces](/docs/code-inventory/interfaces/ai.md#ai_provider_interface) | `GL_Color_Palette_Generator\AI\Interfaces` | `/includes/interfaces/interface-ai-provider.php` |

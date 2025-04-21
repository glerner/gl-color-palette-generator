# User Interface Methods

## Admin_Page

### register_menu()
- **Purpose**: Registers the admin menu items for the plugin
- **Parameters**: None
- **Returns**: void
- **Notes**: Adds main menu item and subpages

### enqueue_assets()
- **Purpose**: Loads CSS and JavaScript for the admin interface
- **Parameters**: None
- **Returns**: void
- **Notes**: Uses WordPress enqueue functions

### render_admin_page()
- **Purpose**: Renders the main admin interface
- **Parameters**: None
- **Returns**: void
- **Notes**: Uses template parts for different sections

### handle_form_submission()
- **Purpose**: Processes form submissions from the admin page
- **Parameters**:
  - `$post_data` (array): Form data from $_POST
- **Returns**: void
- **Notes**: Validates input before processing

## Palette_Generator_Form

### render()
- **Purpose**: Renders the palette generation form
- **Parameters**:
  - `$atts` (array, optional): Additional attributes
- **Returns**: string - HTML output
- **Notes**: Can be used in admin or embedded in content

### process_form()
- **Purpose**: Processes the submitted form data
- **Parameters**:
  - `$form_data` (array): Submitted form data
- **Returns**: array - Generated palette or error information
- **Notes**: Initially returns stub data

### validate_input()
- **Purpose**: Validates user input from the form
- **Parameters**:
  - `$input` (array): Form input to validate
- **Returns**: array - Validated data or errors
- **Notes**: Sanitizes and validates all fields

## Palette_Display

### render()
- **Purpose**: Renders a color palette visually
- **Parameters**:
  - `$palette` (Palette): Palette object to display
  - `$display_options` (array, optional): Display options
- **Returns**: string - HTML output
- **Notes**: Handles both real and stub palette data

### render_color_information()
- **Purpose**: Renders detailed information for each color (display only)
- **Parameters**:
  - `$color` (Color): Color object from the Palette_Storage class
- **Returns**: string - HTML output
- **Notes**:
  - Displays color data: hex, rgb, name, and accessibility info
  - Retrieves all data from the Color object
  - Uses render_accessibility_info() to display WCAG compliance data

### render_accessibility_info()
- **Purpose**: Renders accessibility information for colors (display only)
- **Parameters**:
  - `$color` (Color): Color object from the Palette_Storage class
- **Returns**: string - HTML output
- **Notes**:
  - Displays WCAG compliance data from the Color object
  - Simply formats and displays the accessibility data provided by the Color object

### render_palette_variations()
- **Purpose**: Renders alternative color harmony variations based on the same primary color
- **Parameters**:
  - `$palette` (Palette): Original palette
  - `$variation_type` (string): Type of color harmony to show (e.g., 'analogous', 'triadic', 'monochromatic', 'complementary')
- **Returns**: string - HTML output
- **Notes**: 
  - Shows how the same primary color would look in different color harmony arrangements
  - Color_Harmony_Constants interface provides the list of available harmony types
  - Palette_Generator class handles the creation of alternative palettes
  - This method only handles the display of already-generated variations

## Business_Questionnaire

### render()
- **Purpose**: Renders the business questionnaire form for gathering brand context and color preferences
- **Parameters**:
  - `$atts` (array, optional): Additional attributes
- **Returns**: string - HTML output
- **Notes**: Multi-step form that collects key information about the business, target audience, and color preferences

### process_form()
- **Purpose**: Processes the submitted form data
- **Parameters**:
  - `$form_data` (array): Submitted form data
- **Returns**: array - Sanitized and structured form data
- **Notes**:
  - Validates and sanitizes user inputs
  - Returns structured data ready for submission to palette generation
  - Does not generate palettes itself

### process_responses()
- **Purpose**: Processes questionnaire responses into a structured format
- **Parameters**:
  - `$responses` (array): Form responses from the business questionnaire
- **Returns**: array - Structured data with prompt and metadata
- **Notes**:
  - Sanitizes and validates user inputs for security
  - Constructs a well-structured prompt similar to:
    ```
    Generate a color palette for a [business type] targeting [audience].
    Primary emotional attributes: [attributes]
    Industry: [industry]
    Existing brand colors to incorporate: [colors]
    Competitor colors to avoid: [colors]
    Include primary, secondary, tertiary, and accent colors, in any color harmony that would suit the audience.
    ```
  - Returns the structured data to the calling class
  - The calling class would then:
    - Pass the prompt to an AI_Provider class
    - Process the AI response
    - Call save_responses() to store the questionnaire data
    - Use Palette_Storage to store the generated palette

### save_responses()
- **Purpose**: Saves questionnaire responses for later use
- **Parameters**:
  - `$responses` (array): Processed responses
- **Returns**: int - ID of saved response set
- **Notes**: Stores in database for reuse

## Palette_Block

### register_block()
- **Purpose**: Registers the Gutenberg block
- **Parameters**: None
- **Returns**: void
- **Notes**: Uses register_block_type()

### render_callback()
- **Purpose**: Callback for rendering the block
- **Parameters**:
  - `$attributes` (array): Block attributes
  - `$content` (string): Block content
- **Returns**: string - Rendered HTML
- **Notes**: Uses Palette_Display to render palette

### editor_assets()
- **Purpose**: Enqueues block editor assets
- **Parameters**: None
- **Returns**: void
- **Notes**: Loads JS/CSS for the block editor

## Settings_Page

### render()
- **Purpose**: Renders the settings page
- **Parameters**: None
- **Returns**: void
- **Notes**: Uses WordPress Settings API

### register_settings()
- **Purpose**: Registers plugin settings
- **Parameters**: None
- **Returns**: void
- **Notes**: Defines settings fields and sections

### validate_settings()
- **Purpose**: Validates settings before saving
- **Parameters**:
  - `$input` (array): Settings input
- **Returns**: array - Validated settings
- **Notes**: Sanitizes and validates all settings

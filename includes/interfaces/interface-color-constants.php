<?php
/**
 * Color Constants Interface
 *
 * Defines constants for color management, including:
 * - Color roles and variations
 * - WCAG accessibility requirements
 * - Color scheme definitions
 * - Design constraints for visual comfort
 *
 * @todo This is not actually an interface, but a constants container.
 * Should be refactored to class-color-constants.php in includes/types/.
 * See .github/issues/refactor-color-constants.md
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Interfaces
 * @bootstrap wp-mock
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Interface Color_Constants
 *
 * Defines constants for color management, including:
 * - Color roles and variations
 * - WCAG accessibility requirements
 * - Color scheme definitions
 * - Design constraints for visual comfort
 */
interface Color_Constants {
    /**
     * Color roles
     */
    public const COLOR_ROLE_PRIMARY = 'primary';
    public const COLOR_ROLE_SECONDARY = 'secondary';
    public const COLOR_ROLE_ACCENT = 'accent';
    public const COLOR_ROLE_BACKGROUND = 'background';
    public const COLOR_ROLE_TEXT = 'text';

    /**
     * Color scheme types
     */
    public const SCHEME_MONOCHROMATIC = 'monochromatic';
    public const SCHEME_ANALOGOUS = 'analogous';
    public const SCHEME_COMPLEMENTARY = 'complementary';
    public const SCHEME_ANALOGOUS_COMPLEMENT = 'analogous-complement';
    public const SCHEME_MONOCHROMATIC_ACCENT = 'monochromatic-accent';
    public const SCHEME_DUAL_TONE = 'dual-tone';
    public const SCHEME_NEUTRAL_POP = 'neutral-pop';
    public const SCHEME_SPLIT_COMPLEMENTARY = 'split-complementary';
    public const SCHEME_TRIADIC = 'triadic';
    public const SCHEME_TETRADIC = 'tetradic';
    public const SCHEME_SQUARE = 'square';
    public const SCHEME_AI_GENERATED = 'ai-generated';
    public const SCHEME_CUSTOM = 'custom';
    public const SCHEME_FROM_IMAGE = 'from-image';

    /**
     * Color Wheel Relationships
     * Defines standard color theory relationships
     */
    const COLOR_WHEEL_ROLES = [
        'primary'       => 'Base color, main brand identity',
        'secondary'     => 'Adjacent color (±30°)',
        'tertiary'     => 'Second adjacent color (±60°)',
        'accent'       => 'Opposite on color wheel (180°)',
        'analogous'    => 'Colors adjacent on the wheel (±30°)',
        'complementary' => 'Colors opposite each other (180°)',
        'triadic'      => 'Three colors equally spaced (120°)',
        'tetradic'     => 'Four colors equally spaced (90°)',
        'split'        => 'Primary plus one analogous (+30°) and its complement'
    ];

    /**
     * Color Role Relationships
     * Maps semantic roles to color wheel positions.
     * Only used for color-wheel-based schemes (analogous, complementary, etc.).
     * Other schemes (AI-generated, neutral-pop, etc.) determine relationships differently.
     */
    const COLOR_ROLE_RELATIONSHIPS = [
        'monochromatic' => [
            'primary'   => 'primary'
        ],
        'complementary' => [
            'primary'   => 'primary',
            'secondary' => 'complementary'
        ],
        'analogous' => [
            'primary'   => 'primary',
            'secondary' => 'analogous +30°',
            'tertiary'  => 'analogous -30°'
        ],
        'split-complementary' => [
            'primary'   => 'primary',
            'secondary' => 'analogous to primary',
            'accent'    => 'complementary to primary'
        ],
        'triadic' => [
            'primary'   => 'primary',
            'secondary' => 'triadic +120°',
            'tertiary'  => 'triadic -120°'
        ],
        'tetradic' => [
            'primary'   => 'primary',
            'secondary' => 'complementary to primary',
            'tertiary'  => 'tetradic +90°',
            'accent'    => 'complementary to tertiary'
        ]
    ];

    /**
     * Color Roles
     * Core semantic roles for theme colors
     */
    const COLOR_ROLES = [
        'primary',   // Main brand/identity color
        'secondary', // Supporting color
        'tertiary',  // Additional accent
        'accent',    // Highlights and CTAs
        'neutral',   // Text and UI elements
        'contrast'   // Text on background
    ];

    /**
     * Required minimum roles for each scheme type
     * Default is used when no specific requirements are defined
     */
    const REQUIRED_ROLES = [
        'default' => ['primary', 'secondary', 'accent', 'contrast'],
        'monochromatic' => ['primary', 'contrast'],
        'complementary' => ['primary', 'accent', 'contrast'],
        'analogous'     => ['primary', 'secondary', 'tertiary', 'contrast'],
        'triadic'       => ['primary', 'secondary', 'tertiary', 'contrast'],
        'tetradic'      => ['primary', 'secondary', 'tertiary', 'accent', 'contrast'],
        'split-complementary' => ['primary', 'secondary', 'accent', 'contrast']
    ];

    /**
     * Color Variations
     * Standard variations for each color role, all WCAG compliant
     */
    const COLOR_VARIATIONS = [
        'lighter'  => 'Very light version (+20% brightness, AAA contrast)',
        'light'    => 'Light version (+10% brightness, AAA contrast)',
        'dark'     => 'Dark version (-10% brightness, AAA contrast)',
        'darker'   => 'Very dark version (-20% brightness, AAA contrast)'
    ];

    /**
     * Common Color Applications
     * Maps semantic roles to typical WordPress theme elements
     * Descriptions are for light-mode (plugin automatically adds dark-mode)
     */
    const COLOR_APPLICATIONS = [
        'primary' => [
            'lighter'  => 'Section backgrounds, form backgrounds',
            'light'   => 'Sidebar backgrounds',
            'dark'    => 'Footer backgrounds',
            'darker'  => 'Header backgrounds'
        ],
        'secondary' => [
            'lighter' => 'Block backgrounds',
            'light'   => 'Code block backgrounds',
            'dark'    => 'Blockquote borders',
            'darker'  => 'Emphasis text'
        ],
        'accent' => [
            'lighter' => 'Link background on hover',
            'light'   => 'Focus outlines',
            'dark'    => 'Links, buttons',
            'darker'  => 'Active button states'
        ]
    ];

    /**
     * Text Color Applications
     * Mapping of semantic text roles to color variations
     */
    const TEXT_COLOR_APPLICATIONS = [
        'headings' => [
            'h1'    => 'contrast',         // Maximum contrast with background
            'h2'    => 'contrast',         // Maximum contrast with background
            'h3'    => 'neutral-800',      // Slightly softer than contrast
            'h4'    => 'neutral-700'       // Even softer
        ],
        'content' => [
            'body'      => 'neutral-900',   // High contrast body text
            'secondary' => 'neutral-800',   // Less prominent text
            'tertiary'  => 'neutral-700',   // Less important information
            'disabled'  => 'neutral-600'    // Meets WCAG AA (4.5:1 minimum)
        ]
    ];

    /**
     * System Color Generation Rules
     * Rules for generating system colors from the theme palette
     */
    const SYSTEM_COLOR_RULES = [
        'success' => [
            'hue' => 120,           // Green
            'variants' => [
                'default' => [
                    'saturation' => 0.8,    // 80% of primary saturation
                    'lightness_light' => 45, // For light mode
                    'lightness_dark' => 55   // For dark mode
                ],
                'light' => [
                    'saturation' => 0.3,    // 30% of primary saturation
                    'lightness_light' => 95, // Very light for backgrounds
                    'lightness_dark' => 20   // Darker for dark mode
                ]
            ]
        ],
        'error' => [
            'hue' => 0,             // Red
            'variants' => [
                'default' => [
                    'saturation' => 0.8,
                    'lightness_light' => 45,
                    'lightness_dark' => 55
                ],
                'light' => [
                    'saturation' => 0.3,
                    'lightness_light' => 95,
                    'lightness_dark' => 20
                ]
            ]
        ],
        'warning' => [
            'hue' => 30,            // Orange
            'variants' => [
                'default' => [
                    'saturation' => 0.8,
                    'lightness_light' => 45,
                    'lightness_dark' => 55
                ],
                'light' => [
                    'saturation' => 0.3,
                    'lightness_light' => 95,
                    'lightness_dark' => 20
                ]
            ]
        ]
    ];

    /**
     * Theme Palette Structure
     * Recommended organization for theme.json
     */
    const THEME_PALETTE_STRUCTURE = [
        'core' => [
            'primary',
            'secondary',
            'accent',
            'contrast'
        ],
        'variations' => [
            'primary-lighter',
            'primary-light',
            'primary',      // original "user- or AI-generated" color, not output in final palette
            'primary-dark',
            'primary-darker',
            // Repeat for secondary, tertiary, and accent
        ],
        'neutrals' => [
            'neutral-100',
            'neutral-200',
            'neutral-300',
            'neutral-400',
            'neutral-500',
            'neutral-600',
            'neutral-700',
            'neutral-800',
            'neutral-900'
        ],
        'system' => [
            'highlight-selection',
            'highlight-marker',
            'status-success',
            'status-error',
            'status-warning',
            'status-info'
        ],
        'utility' => [
            'white',
            'black',
            'transparent'
        ]
    ];

    /**
     * Color Schemes
     * Maps scheme types to their properties
     */
    const COLOR_SCHEMES = [
        'monochromatic' => [
            'type'  => 'monochromatic',
            'name'  => 'Single Color',
            'label' => 'Monochromatic',
            'roles' => ['primary', 'secondary', 'tertiary']
        ],
        'monochromatic-accent' => [
            'type'  => 'monochromatic-accent',
            'name'  => 'Minimalist',
            'label' => 'Monochromatic with Accent',
            'roles' => ['primary', 'secondary', 'accent', 'background', 'text']
        ],
        'analogous' => [
            'type'  => 'analogous',
            'name'  => 'Harmony',
            'label' => 'Analogous',
            'roles' => ['primary', 'secondary', 'tertiary']
        ],
        'analogous-complement' => [
            'type'  => 'analogous-complement',
            'name'  => 'Modern Theme',
            'label' => 'Analogous with Complement',
            'roles' => ['primary', 'secondary', 'accent', 'background', 'text']
        ],
        'complementary' => [
            'type'  => 'complementary',
            'name'  => 'Contrast',
            'label' => 'Complementary',
            'roles' => ['primary', 'secondary']
            ],
        'split-complementary' => [
            'type'  => 'split-complementary',
            'name'  => 'Balance',
            'label' => 'Split Complementary',
            'roles' => ['primary', 'secondary', 'tertiary']
        ],
        'triadic' => [
            'type'  => 'triadic',
            'name'  => 'Triad',
            'label' => 'Triadic',
            'roles' => ['primary', 'secondary', 'tertiary']
            ],
        'tetradic' => [
            'type'  => 'tetradic',
            'name'  => 'Double Complement',
            'label' => 'Tetradic',
            'roles' => ['primary', 'secondary', 'tertiary', 'quaternary']
        ],
        'square' => [
            'type'  => 'square',
            'name'  => 'Four Point',
            'label' => 'Square',
            'roles' => ['primary', 'secondary', 'tertiary', 'quaternary']
        ],
        'dual-tone' => [
            'type'  => 'dual-tone',
            'name'  => 'Professional',
            'label' => 'Dual Tone',
            'roles' => ['primary', 'secondary', 'background', 'text']
            ],
        'neutral-pop' => [
            'type'  => 'neutral-pop',
            'name'  => 'Business',
            'label' => 'Neutral with Pop',
            'roles' => ['primary', 'secondary', 'accent', 'background', 'text']
        ],
        'ai-generated' => [
            'type'  => 'ai-generated',
            'name'  => 'AI Design',
            'label' => 'AI-Generated Palette',
            'roles' => ['primary', 'secondary', 'accent', 'background', 'text'],
            'description' => 'Let AI generate a custom color palette optimized for your specific needs. Can work from themes (e.g., "Valentine\'s Day"), business goals (e.g., "trustworthy finance site"), or even photos (adjusting colors for optimal web usage).'
        ]
    ];

    /**
     * Neutral Color Usage
     * Defines how neutral colors should be used in themes
     */
    const NEUTRAL_COLOR_ROLES = [
        'text'       => 'Default text color, slightly softer than pure contrast',
        'heading'    => 'Heading text, stronger than body text',
        'subtle'     => 'Subtle text like captions or metadata',
        'border'     => 'Border colors and dividers',
        'surface'    => 'Background surfaces and cards',
        'hover'      => 'Hover states and interactions',
        'disabled'   => 'Disabled elements and placeholder text'
    ];

    /**
     * Neutral Color Steps
     * Defines the steps in the neutral color scale
     * All steps maintain WCAG AA minimum contrast (4.5:1) with their background
     */
    const NEUTRAL_STEPS = [
        900 => 'Primary content - Maximum contrast',     // Body text
        800 => 'Secondary content - High contrast',      // Secondary text
        700 => 'Tertiary content - Good contrast',      // Less important text
        600 => 'Disabled content - AA contrast',        // Meets 4.5:1 minimum
        300 => 'Borders - Visible but subtle',          // 3:1 with background
        200 => 'Hover backgrounds',                     // Subtle interaction
        100 => 'Surface backgrounds'                    // Very subtle
    ];

    /**
     * WCAG Accessibility Standards
     * These constants define the Web Content Accessibility Guidelines (WCAG) 2.0
     * requirements for contrast ratios
     */
    const WCAG_CONTRAST_AAA = 7.0;      // Level AAA - Enhanced
    const WCAG_CONTRAST_AA = 4.5;       // Level AA - Minimum
    const WCAG_CONTRAST_AA_LARGE = 3.0; // Level AA for large text
    const WCAG_CONTRAST_MIN = 4.7;      // Our minimum requirement (AA)
    const WCAG_CONTRAST_TARGET = 7.0;   // Our target (AAA)
    const CONTRAST_MAX = 12.0;          // Maximum for visual comfort

    /**
     * Accessibility standards and thresholds
     * Consolidates all accessibility-related constants including WCAG requirements
     *
     * @var array
     */
    public const ACCESSIBILITY_CONFIG = [
        'contrast' => [
            'min_ratio' => self::WCAG_CONTRAST_MIN,       // Minimum required contrast (AA)
            'enhanced_ratio' => self::WCAG_CONTRAST_AAA,  // Enhanced contrast (AAA)
            'large_text_ratio' => self::WCAG_CONTRAST_AA_LARGE, // Large text minimum (AA)
            'target_ratio' => self::WCAG_CONTRAST_TARGET, // Our target contrast
            'max_ratio' => self::CONTRAST_MAX,            // Maximum comfortable contrast
            'levels' => [
                'aa' => self::WCAG_CONTRAST_AA,           // WCAG 2.0 AA standard
                'aaa' => self::WCAG_CONTRAST_AAA,         // WCAG 2.0 AAA standard
                'aa_large' => self::WCAG_CONTRAST_AA_LARGE // WCAG 2.0 AA for large text
            ]
        ],
        'brightness' => [
            'min_difference' => 125,    // Minimum brightness difference for readability
            'target_value' => 128       // Target brightness value for adjustments
        ],
        'color_blind' => [
            'simulation_strength' => 1.0, // Strength of color blindness simulation
            'adaptation_threshold' => 0.05 // Threshold for color adaptation
        ],
        'adjustment' => [
            'lightness_step' => 5,      // Step size for lightness adjustments
            'saturation_step' => 10,    // Step size for saturation adjustments
            'max_attempts' => 20        // Maximum attempts for color adjustments
        ]
    ];

    /**
     * Luminance and Perceptual Thresholds
     * Used for determining light/dark mode colors and ensuring sufficient contrast
    */
    const LIGHT_LUMINANCE_THRESHOLD = 0.85;  // Light mode backgrounds
    const DARK_LUMINANCE_THRESHOLD = 0.15;   // Dark mode backgrounds
    const READABLE_CONTRAST_MIN = 0.40;      // Minimum for readable text
    const DECORATIVE_CONTRAST_MIN = 0.30;    // Minimum for decorative elements

    /**
     * Off-white and near-black luminance ranges
     */
    const OFF_WHITE_LUMINANCE_MIN = 0.85;  // Must be very close to white
    const OFF_WHITE_LUMINANCE_MAX = 0.95;  // But not pure white for visual comfort
    const NEAR_BLACK_LUMINANCE_MIN = 0.05; // Not pure black for visual comfort
    const NEAR_BLACK_LUMINANCE_MAX = 0.15; // Must be very close to black

    /**
     * Primary Colors - RGB and CMY
     */
    const COLOR_PRIMARY_RED = ['#FF0000', 'Red'];
    const COLOR_PRIMARY_GREEN = ['#00FF00', 'Green'];
    const COLOR_PRIMARY_BLUE = ['#0000FF', 'Blue'];
    const COLOR_PRIMARY_CYAN = ['#00FFFF', 'Cyan'];
    const COLOR_PRIMARY_MAGENTA = ['#FF00FF', 'Magenta'];
    const COLOR_PRIMARY_YELLOW = ['#FFFF00', 'Yellow'];

    /**
     * Neutral Colors
     */
    const COLOR_BLACK = '#000000';      // Pure black
    const COLOR_WHITE = '#FFFFFF';      // Pure white
    const COLOR_OFF_WHITE = '#F8F9FA';  // Light mode base
    const COLOR_NEAR_BLACK = '#1A1A1A'; // Dark mode base
    const COLOR_DARK_GRAY = '#333333';  // Strong contrast
    const COLOR_MID_GRAY = '#666666';   // Medium contrast
    const COLOR_LIGHT_GRAY = '#CCCCCC'; // Light contrast

    /**
     * Lightness levels for color variations
     */
    const HIGH_LIGHTNESS = 85;         // High lightness for light variations
    const LOW_LIGHTNESS = 15;          // Low lightness for dark variations
    const LIGHTNESS_STEP = 5;          // Step size for lightness adjustments

    /**
     * Color wheel configuration
     *
     * @var array
     */
    const COLOR_WHEEL_CONFIG = [
        'segments' => 12,
        'min_hue_step' => 30,
        'max_hue_step' => 60,
        'monochromatic_threshold' => 30,
        'hue_category_size' => 30,
        'hue_calculation_base' => 60,
        'max_angle_difference' => 180,
    ];

    /**
     * Color harmony rules defining the geometric relationships between colors
     * on the color wheel
     *
     * @var array
     */
    const COLOR_HARMONY_RULES = [
        'complementary' => [
            'angle' => 180,
            'description' => 'Colors that are opposite each other on the color wheel'
        ],
        'analogous' => [
            'angle' => 30,
            'description' => 'Colors that are next to each other on the color wheel'
        ],
        'triadic' => [
            'angle' => 120,
            'description' => 'Three colors equally spaced around the color wheel'
        ],
        'tetradic' => [
            'angle' => 90,
            'description' => 'Four colors arranged into two complementary pairs'
        ],
        'split-complementary' => [
            'angle' => 150,
            'description' => 'A base color and two colors adjacent to its complement'
        ]
    ];

    /**
     * Harmony Score Thresholds
     * Defines minimum scores for different aspects of color harmony
     */
    public const HARMONY_THRESHOLDS = [
        'overall' => 0.7,      // Minimum overall harmony score
        'harmony' => 0.6,      // Minimum harmony angle score
        'balance' => 0.5,      // Minimum color balance score
        'vibrance' => 0.4      // Minimum vibrance score
    ];

    /**
     * Palette Demonstration Sections
     * Defines how to showcase the palette in the preview
     */
    const PALETTE_DEMO_SECTIONS = [
        'typography' => [
            'heading1' => ['color' => 'contrast', 'background' => 'primary-lighter'],
            'heading2' => ['color' => 'contrast', 'background' => 'primary-light'],
            'heading3' => ['color' => 'neutral-800'],
            'heading4' => ['color' => 'neutral-700'],
            'body'     => ['color' => 'neutral-900'],
            'caption'  => ['color' => 'neutral-700']
        ],
        'interactive' => [
            'button' => [
                'default' => ['color' => 'contrast', 'background' => 'accent-dark'],
                'hover'   => ['color' => 'contrast', 'background' => 'accent-darker']
            ],
            'link' => [
                'default' => ['color' => 'accent-dark'],
                'hover'   => ['color' => 'accent-dark', 'background' => 'neutral-200']
            ]
        ],
        'blocks' => [
            'card' => [
                'background' => 'primary-lighter',
                'border'    => 'neutral-300'
            ],
            'quote' => [
                'border'    => 'secondary-dark',
                'background' => 'secondary-lighter'
            ],
            'code' => [
                'background' => 'secondary-lighter',
                'border'    => 'neutral-300'
            ]
        ],
        'feedback' => [
            'success' => [
                'default' => ['color' => 'system-success-text', 'background' => 'system-success'],
                'subtle'  => ['color' => 'system-success', 'background' => 'system-success-light']
            ],
            'error' => [
                'default' => ['color' => 'system-error-text', 'background' => 'system-error'],
                'subtle'  => ['color' => 'system-error', 'background' => 'system-error-light']
            ],
            'warning' => [
                'default' => ['color' => 'system-warning-text', 'background' => 'system-warning'],
                'subtle'  => ['color' => 'system-warning', 'background' => 'system-warning-light']
            ]
        ]
    ];

    /**
     * Color perception and conversion thresholds
     *
     * @var array
     */
    public const COLOR_PERCEPTION = [
        'luminance' => [
            'min' => 0.05,             // Minimum perceivable luminance
            'max' => 0.95              // Maximum perceivable luminance
        ],
        'saturation' => [
            'min' => 0,                // Minimum saturation
            'max' => 100,              // Maximum saturation
            'step' => 5,               // Standard saturation adjustment step
            'min_range' => 20,         // Minimum range between most and least saturated colors
            'neutral_threshold' => 10   // Below this value, color is considered neutral/grayscale
        ],
        'lightness' => [
            'min' => 0,                // Minimum lightness
            'max' => 100,              // Maximum lightness
            'step' => 5                // Standard lightness adjustment step
        ],
        'temperature' => [
            'warm_hue_start' => 0,     // Start of warm hues (red)
            'warm_hue_end' => 60,      // End of warm hues (yellow)
            'cool_hue_start' => 180,   // Start of cool hues (cyan)
            'cool_hue_end' => 240      // End of cool hues (blue)
        ]
    ];

    /**
     * Color space conversion matrices and coefficients
     *
     * @var array
     */
    public const COLOR_SPACE_CONVERSION = [
        'formats' => [
            'hex' => '/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/',
            'rgb' => '/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/',
            'hsl' => '/^hsl\(\s*(\d{1,3})\s*,\s*(\d{1,3})%\s*,\s*(\d{1,3})%\s*\)$/'
        ],
        'rgb_to_xyz' => [
            [0.4124564, 0.3575761, 0.1804375],
            [0.2126729, 0.7151522, 0.0721750],
            [0.0193339, 0.1191920, 0.9503041]
        ],
        'xyz_to_rgb' => [
            [3.2404542, -1.5371385, -0.4985314],
            [-0.9692660, 1.8760108, 0.0415560],
            [0.0556434, -0.2040259, 1.0572252]
        ],
        'perceived_brightness' => [
            'r' => 0.2126,  // Red coefficient (ITU-R BT.709)
            'g' => 0.7152,  // Green coefficient
            'b' => 0.0722   // Blue coefficient
        ]
    ];

    /**
     * Color metric thresholds and ranges
     *
     * @var array
     */
    public const COLOR_METRICS = [
        'lightness' => [
            'step' => 5,              // Small incremental adjustment
            'initial_shift' => 20,    // Initial shift for variations
            'large_shift' => 40,      // Larger shift for extreme variations
            'max_attempts' => 10,     // Maximum adjustment attempts
            'min' => 0,              // Minimum lightness value
            'max' => 100,            // Maximum lightness value
            'threshold' => 50,       // Threshold for light/dark determination
            'off_white_min' => 90,   // Minimum lightness for off-white
            'off_white_max' => 97,   // Maximum lightness for off-white
            'near_black_min' => 3,   // Minimum lightness for near-black
            'near_black_max' => 10,  // Maximum lightness for near-black
            'spacing_min' => 15,     // Minimum lightness difference between variations
        ],
        'brightness' => [
            'min' => 0.0,
            'max' => 1.0,
            'light_threshold' => 0.6,
            'dark_threshold' => 0.3
        ],
        'saturation' => [
            'min' => 0,              // Minimum saturation value
            'max' => 100,            // Maximum saturation value
            'low_threshold' => 20,   // Threshold for low saturation
            'high_threshold' => 80,  // Threshold for high saturation
            'min_range' => 20,       // Minimum range between most and least saturated colors
            'step' => 10,            // Standard step size for saturation adjustments
            'neutral_threshold' => 10 // Below this value, color is considered neutral/grayscale
        ],
        'contrast' => [
            'min_ratio' => 4.5,      // Minimum WCAG AA contrast ratio
            'large_text_ratio' => 3.0, // WCAG AA ratio for large text
            'enhanced_ratio' => 7.0,   // Enhanced contrast ratio
            'target' => 4.5,          // Target contrast ratio
            'max' => 21.0,            // Maximum achievable contrast
            'step' => 0.1             // Step size for contrast adjustments
        ],
        'difference' => [
            'just_noticeable' => 2.3,
            'distinct' => 5.0,
            'obvious' => 10.0
        ],
        'value' => [
            'min' => 0,              // Minimum value (0-100)
            'max' => 100,            // Maximum value (0-100)
            'min_range' => 20,       // Minimum range between lightest and darkest colors
            'step' => 10             // Standard step size for value adjustments
        ]
    ];

    // Convenience constants for direct access
    public const MIN_SATURATION_RANGE = self::COLOR_METRICS['saturation']['min_range'];
    public const MAX_SATURATION = self::COLOR_METRICS['saturation']['max'];
    public const SATURATION_STEP = self::COLOR_METRICS['saturation']['step'];
    public const MIN_VALUE_RANGE = self::COLOR_METRICS['value']['min_range'];
    public const MAX_VALUE = self::COLOR_METRICS['value']['max'];
    public const VALUE_STEP = self::COLOR_METRICS['value']['step'];

    /**
     * Color Psychological Effects
     * Maps color ranges to their psychological and business effects
     */
    const COLOR_PSYCHOLOGICAL_EFFECTS = [
        'red' => [
            'range' => [0, 15],
            'effects' => [
                'primary' => ['energy', 'passion', 'excitement'],
                'negative' => ['aggression', 'danger'],
                'business_contexts' => ['food', 'entertainment', 'sports']
            ]
        ],
        'orange' => [
            'range' => [15, 45],
            'effects' => [
                'primary' => ['creativity', 'adventure', 'confidence'],
                'negative' => ['frivolity', 'immaturity'],
                'business_contexts' => ['youth', 'arts', 'food']
            ]
        ],
        'yellow' => [
            'range' => [45, 75],
            'effects' => [
                'primary' => ['optimism', 'clarity', 'warmth'],
                'negative' => ['caution', 'cowardice'],
                'business_contexts' => ['education', 'children', 'leisure']
            ]
        ],
        'green' => [
            'range' => [75, 165],
            'effects' => [
                'primary' => ['growth', 'harmony', 'nature'],
                'negative' => ['envy', 'boredom'],
                'business_contexts' => ['environment', 'health', 'finance']
            ]
        ],
        'blue' => [
            'range' => [165, 255],
            'effects' => [
                'primary' => ['trust', 'stability', 'professionalism'],
                'negative' => ['coldness', 'aloofness'],
                'business_contexts' => ['technology', 'finance', 'healthcare']
            ]
        ],
        'purple' => [
            'range' => [255, 315],
            'effects' => [
                'primary' => ['luxury', 'creativity', 'mystery'],
                'negative' => ['decadence', 'moodiness'],
                'business_contexts' => ['luxury', 'beauty', 'spirituality']
            ]
        ],
        'pink' => [
            'range' => [315, 360],
            'effects' => [
                'primary' => ['love', 'nurturing', 'sensitivity'],
                'negative' => ['weakness', 'immaturity'],
                'business_contexts' => ['beauty', 'fashion', 'romance']
            ]
        ]
    ];

    /**
     * AI Service Configuration
     * Default settings for AI color generation
     */
    const AI_CONFIG = [
        'temperature' => 0.7,      // Controls randomness (0.0-1.0)
        'max_tokens' => 500,       // Increased to accommodate color descriptions
        'top_p' => 0.9,           // Nucleus sampling threshold
        'frequency_penalty' => 0.0 // Repetition penalty
    ];

    /**
     * AI Response Format
     * Template for AI service responses
     */
    const AI_RESPONSE_FORMAT = [
        'colors' => [
            'primary' => [
                'hex' => '#HEXCODE',
                'name' => 'Artistic color name',
                'emotion' => 'Emotional impact description'
            ],
            'secondary' => [
                'hex' => '#HEXCODE',
                'name' => 'Artistic color name',
                'emotion' => 'Emotional impact description'
            ],
            'tertiary' => [
                'hex' => '#HEXCODE',
                'name' => 'Artistic color name',
                'emotion' => 'Emotional impact description'
            ],
            'accent' => [
                'hex' => '#HEXCODE',
                'name' => 'Artistic color name',
                'emotion' => 'Emotional impact description'
            ]
        ],
        'palette_story' => 'Overall description of how the palette works together to achieve the business goals and appeal to the target audience'
    ];

    /**
     * Palette Description Schema
     * Defines the structure for describing a color palette
     */
    public const PALETTE_DESCRIPTION_SCHEMA = [
        'palette_name' => 'Name of the color palette',
        'brand_identity' => [
            'primary_colors' => 'Description of how primary colors reflect brand values',
            'secondary_colors' => 'Description of how secondary colors support the brand',
            'accent_colors' => 'Description of how accent colors add visual interest'
        ],
        'color_psychology' => [
            'emotional_impact' => 'Intended emotional response from the target audience',
            'cultural_significance' => 'Cultural considerations and meanings',
            'industry_relevance' => 'How colors align with industry standards'
        ],
        'palette_story' => 'Overall description of how the palette works together to achieve the business goals and appeal to the target audience'
    ];
}

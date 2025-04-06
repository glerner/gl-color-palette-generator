<?php
namespace GL_Color_Palette_Generator\Generators;

class PromptEngineer {
	private $settings;
	private $color_analyzer;
	private $context_processor;
	private $emotion_mapper;

	// Creative direction templates
	private $creative_directions = array(
		'natural'   => array(
			'style'      => 'organic, earthy, inspired by nature',
			'references' => 'flora, fauna, minerals, weather, landscapes',
			'tone'       => 'authentic, grounded, harmonious',
		),
		'technical' => array(
			'style'      => 'precise, systematic, scientific',
			'references' => 'technology, chemistry, physics, mathematics',
			'tone'       => 'analytical, exact, innovative',
		),
		'emotional' => array(
			'style'      => 'evocative, sensory, mood-driven',
			'references' => 'feelings, memories, experiences, sensations',
			'tone'       => 'emotive, personal, resonant',
		),
		'luxurious' => array(
			'style'      => 'sophisticated, premium, refined',
			'references' => 'precious materials, haute couture, fine art',
			'tone'       => 'elegant, exclusive, aspirational',
		),
		'playful'   => array(
			'style'      => 'fun, whimsical, imaginative',
			'references' => 'candy, toys, games, fantasy',
			'tone'       => 'lighthearted, energetic, surprising',
		),
	);

	public function __construct() {
		$this->settings          = new SettingsManager();
		$this->color_analyzer    = new ColorAnalyzer();
		$this->context_processor = new ContextProcessor();
		$this->emotion_mapper    = new EmotionMapper();
	}

	/**
	 * Generate sophisticated AI prompt
	 */
	public function create_prompt( $color_data, $context = array(), $options = array() ) {
		$base_color_analysis = $this->analyze_color_properties( $color_data );
		$context_analysis    = $this->analyze_context( $context );
		$creative_direction  = $this->get_creative_direction( $context );
		$emotional_mapping   = $this->map_emotional_qualities( $color_data, $context );

		return array(
			'system'   => $this->generate_system_prompt( $context_analysis, $creative_direction ),
			'user'     => $this->generate_user_prompt( $base_color_analysis, $context_analysis, $emotional_mapping ),
			'examples' => $this->generate_examples( $base_color_analysis, $context_analysis ),
		);
	}

	/**
	 * Generate system prompt
	 */
	private function generate_system_prompt( $context, $creative_direction ) {
		return <<<PROMPT
You are a world-class color naming expert with deep knowledge in:
- Color theory and psychology
- {$context['industry']} industry terminology
- {$context['culture']} cultural references
- {$creative_direction['style']} styling
- Brand voice and marketing psychology

Your task is to generate unique, memorable, and contextually appropriate color names.

CREATIVE DIRECTION:
Style: {$creative_direction['style']}
References: {$creative_direction['references']}
Tone: {$creative_direction['tone']}

BRAND VOICE:
{$this->format_brand_voice($context['brand_voice'])}

CONSTRAINTS:
- Names must be {$context['length_preference']} words maximum
- Avoid: {$this->format_restrictions($context['restrictions'])}
- Must be appropriate for: {$context['audience']}
- Should evoke: {$context['desired_emotions']}

OUTPUT FORMAT:
PRIMARY_NAME: [primary color name]
RATIONALE: [brief explanation of naming choice]
ALTERNATIVES: [2-3 alternative names]
EMOTIONAL_RESPONSE: [intended emotional response]
CULTURAL_NOTES: [relevant cultural considerations]
PROMPT;
	}

	/**
	 * Generate user prompt
	 */
	private function generate_user_prompt( $color_analysis, $context, $emotional_mapping ) {
		return <<<PROMPT
Generate a color name with the following specifications:

COLOR PROPERTIES:
{$this->format_color_properties($color_analysis)}

EMOTIONAL QUALITIES:
{$this->format_emotional_mapping($emotional_mapping)}

CONTEXTUAL REQUIREMENTS:
Industry: {$context['industry']}
Usage: {$context['usage']}
Target Market: {$context['market']}
Season: {$context['season']}
Product Category: {$context['category']}

SEMANTIC RELATIONSHIPS:
{$this->format_semantic_relationships($color_analysis, $context)}

INSPIRATION SOURCES:
{$this->generate_inspiration_sources($color_analysis, $context)}

ADDITIONAL CONSIDERATIONS:
{$this->format_additional_considerations($context)}
PROMPT;
	}

	/**
	 * Analyze color properties
	 */
	private function analyze_color_properties( $color_data ) {
		$analysis = $this->color_analyzer->analyze( $color_data['hex'] );

		return array(
			'base'            => array(
				'hex' => $color_data['hex'],
				'rgb' => $analysis['rgb'],
				'hsl' => $analysis['hsl'],
				'lab' => $analysis['lab'],
			),
			'characteristics' => array(
				'temperature' => $analysis['temperature'],
				'brightness'  => $analysis['brightness'],
				'saturation'  => $analysis['saturation'],
				'contrast'    => $analysis['contrast'],
			),
			'relationships'   => array(
				'family'        => $analysis['color_family'],
				'analogous'     => $analysis['analogous_colors'],
				'complementary' => $analysis['complementary_color'],
				'triadic'       => $analysis['triadic_colors'],
			),
			'perceptual'      => array(
				'weight'   => $analysis['perceived_weight'],
				'depth'    => $analysis['perceived_depth'],
				'movement' => $analysis['perceived_movement'],
			),
		);
	}

	/**
	 * Map emotional qualities
	 */
	private function map_emotional_qualities( $color_data, $context ) {
		return $this->emotion_mapper->map(
			array(
				'color'          => $color_data,
				'industry'       => $context['industry'],
				'culture'        => $context['culture'],
				'season'         => $context['season'],
				'target_emotion' => $context['desired_emotions'],
			)
		);
	}

	/**
	 * Generate inspiration sources
	 */
	private function generate_inspiration_sources( $color_analysis, $context ) {
		$sources = array();

		// Nature-based inspirations
		if ( $this->should_include_nature_references( $color_analysis, $context ) ) {
			$sources[] = $this->get_nature_inspirations( $color_analysis );
		}

		// Cultural references
		if ( $context['include_cultural_references'] ) {
			$sources[] = $this->get_cultural_inspirations( $context['culture'] );
		}

		// Industry-specific references
		if ( $context['industry'] !== 'general' ) {
			$sources[] = $this->get_industry_inspirations( $context['industry'] );
		}

		// Emotional associations
		$sources[] = $this->get_emotional_inspirations( $color_analysis['characteristics'] );

		// Seasonal influences
		if ( $context['season'] !== 'any' ) {
			$sources[] = $this->get_seasonal_inspirations( $context['season'] );
		}

		return implode(
			"\n",
			array_map(
				function ( $source ) {
					return '- ' . $source;
				},
				$sources
			)
		);
	}

	/**
	 * Format semantic relationships
	 */
	private function format_semantic_relationships( $color_analysis, $context ) {
		$relationships = array();

		// Color family relationships
		$relationships[] = sprintf(
			'Primary Color Family: %s (%.1f%% confidence)',
			$color_analysis['relationships']['family'],
			$color_analysis['relationships']['family_confidence']
		);

		// Contextual relationships
		if ( $context['category'] ) {
			$relationships[] = sprintf(
				'Common in %s: %s',
				$context['category'],
				implode( ', ', $this->get_category_associations( $context['category'] ) )
			);
		}

		// Cultural significance
		if ( $context['culture'] ) {
			$relationships[] = sprintf(
				'Cultural Significance in %s: %s',
				$context['culture'],
				implode( ', ', $this->get_cultural_significance( $context['culture'] ) )
			);
		}

		return implode(
			"\n",
			array_map(
				function ( $relationship ) {
					return '- ' . $relationship;
				},
				$relationships
			)
		);
	}

	/**
	 * Format brand voice guidelines
	 */
	private function format_brand_voice( $voice ) {
		$voice_characteristics = array(
			'professional' => array(
				'tone'  => 'refined, authoritative, precise',
				'style' => 'clear, sophisticated, measured',
			),
			'friendly'     => array(
				'tone'  => 'warm, approachable, personal',
				'style' => 'conversational, engaging, relatable',
			),
			'innovative'   => array(
				'tone'  => 'forward-thinking, dynamic, bold',
				'style' => 'modern, distinctive, pioneering',
			),
			'playful'      => array(
				'tone'  => 'fun, energetic, lighthearted',
				'style' => 'creative, surprising, memorable',
			),
		);

		$voice_guide = $voice_characteristics[ $voice ] ?? $voice_characteristics['professional'];

		return <<<VOICE
Tone: {$voice_guide['tone']}
Style: {$voice_guide['style']}
VOICE;
	}

	/**
	 * Generate example color names
	 */
	private function generate_examples( $color_analysis, $context ) {
		$examples = array();

		// Find similar colors and their successful names
		$similar_colors = $this->find_similar_successful_colors(
			$color_analysis['base']['lab'],
			$context
		);

		foreach ( $similar_colors as $color ) {
			$examples[] = array(
				'hex'             => $color['hex'],
				'name'            => $color['name'],
				'rationale'       => $color['rationale'],
				'success_metrics' => $color['metrics'],
			);
		}

		return $examples;
	}

	/**
	 * Generate optimized prompts
	 */
	public function generate_prompt( $context, $parameters = array() ) {
		$base_prompt     = $this->get_base_prompt( $context );
		$enhanced_prompt = $this->enhance_prompt( $base_prompt, $parameters );

		return array(
			'prompt'       => $enhanced_prompt,
			'tokens'       => $this->estimate_tokens( $enhanced_prompt ),
			'context_data' => $this->extract_context_data( $parameters ),
			'metadata'     => array(
				'prompt_type'       => $context,
				'enhancement_level' => $this->calculate_enhancement_level( $enhanced_prompt ),
				'complexity_score'  => $this->calculate_complexity_score( $enhanced_prompt ),
			),
		);
	}

	/**
	 * Optimize prompt structure
	 */
	public function optimize_prompt( $prompt, $target_model ) {
		$structured_prompt = $this->structure_prompt( $prompt );
		$optimized_prompt  = $this->apply_model_specific_optimizations( $structured_prompt, $target_model );

		return array(
			'original_prompt'       => $prompt,
			'optimized_prompt'      => $optimized_prompt,
			'optimization_steps'    => $this->get_optimization_steps(),
			'expected_improvements' => $this->calculate_expected_improvements( $prompt, $optimized_prompt ),
		);
	}

	/**
	 * Generate context-aware prompts
	 */
	public function generate_context_aware_prompt( $base_color, $requirements ) {
		$context_data = array(
			'color_analysis'             => $this->analyze_color_context( $base_color ),
			'user_preferences'           => $this->extract_user_preferences( $requirements ),
			'industry_standards'         => $this->get_industry_standards( $requirements ),
			'accessibility_requirements' => $this->get_accessibility_requirements( $requirements ),
		);

		return array(
			'prompt'            => $this->build_contextual_prompt( $context_data ),
			'context_variables' => $this->extract_variables( $context_data ),
			'prompt_variations' => $this->generate_prompt_variations( $context_data ),
			'fallback_prompts'  => $this->generate_fallback_prompts( $context_data ),
		);
	}
}

<div class="wrap gl-color-palette-components">
	<!-- Color Preview -->
	<div class="color-preview">
		<div class="color-swatch" style="background-color: #2C3E50;"></div>
		<div class="color-info">
			<div class="color-name">Primary Color</div>
			<div class="color-hex">#2C3E50</div>
		</div>
	</div>

	<!-- Color Variation Slider -->
	<div class="variation-slider">
		<div class="variation-track" style="--color-darkest: #1a252f; --color-base: #2C3E50; --color-lightest: #3e5771;">
			<div class="variation-handle" style="left: 50%;"></div>
		</div>
	</div>

	<!-- Color Harmony Wheel -->
	<div class="harmony-wheel">
		<div class="harmony-marker" style="left: 50%; top: 20%;"></div>
		<div class="harmony-marker" style="left: 80%; top: 50%;"></div>
		<div class="harmony-marker" style="left: 50%; top: 80%;"></div>
	</div>

	<!-- Color Scheme Preview -->
	<div class="scheme-preview">
		<?php
		// Get current palette and renderer
		$palette  = new Color_Palette(
			array(
				'primary'   => '#2C3E50',
				'secondary' => '#E74C3C',
				'tertiary'  => '#3498DB',
				'accent'    => '#2ECC71',
			)
		);
		$renderer = new Color_Palette_Renderer();

		// Render palette info (includes AI story)
		echo $renderer->render_palette_info( $palette );

		// Render color swatches with enhanced info
		echo $renderer->render(
			$palette,
			array(
				'layout'      => 'cards',
				'show_info'   => true,
				'show_names'  => true,
				'show_values' => true,
				'size'        => 'large',
			)
		);
		?>
	</div>

	<!-- Analysis Card -->
	<div class="analysis-card">
		<div class="analysis-header">
			<span class="analysis-icon dashicons dashicons-visibility"></span>
			<h3 class="analysis-title">Accessibility Analysis</h3>
		</div>
		<div class="analysis-content">
			<div class="accessibility-indicator wcag-aaa">AAA Pass</div>
			<div class="accessibility-indicator wcag-aa">AA Pass</div>
			<p>All color combinations meet WCAG 2.1 guidelines for accessibility.</p>
		</div>
	</div>
</div> 

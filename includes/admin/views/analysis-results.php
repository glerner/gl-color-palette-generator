<?php defined('ABSPATH') || exit; ?>

<div class="gl-analysis-results">
    <?php if (isset($analysis['contrast'])): ?>
        <div class="gl-analysis-section">
            <h3><?php esc_html_e('Contrast Analysis', 'gl-color-palette-generator'); ?></h3>

            <div class="gl-contrast-ratios">
                <h4><?php esc_html_e('Best Text Combinations', 'gl-color-palette-generator'); ?></h4>
                <?php foreach ($analysis['contrast']['best_text_combinations'] as $combo): ?>
                    <div class="gl-contrast-pair">
                        <div class="gl-color-preview">
                            <span style="background-color: <?php echo esc_attr($combo['colors'][0]); ?>"></span>
                            <span style="background-color: <?php echo esc_attr($combo['colors'][1]); ?>"></span>
                        </div>
                        <div class="gl-contrast-info">
                            <span class="ratio"><?php echo esc_html(number_format($combo['ratio'], 2)); ?>:1</span>
                            <?php if ($combo['passes_aaa_normal']): ?>
                                <span class="badge aaa"><?php esc_html_e('AAA', 'gl-color-palette-generator'); ?></span>
                            <?php elseif ($combo['passes_aa_normal']): ?>
                                <span class="badge aa"><?php esc_html_e('AA', 'gl-color-palette-generator'); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($analysis['harmony'])): ?>
        <div class="gl-analysis-section">
            <h3><?php esc_html_e('Color Harmony', 'gl-color-palette-generator'); ?></h3>

            <?php if (!empty($analysis['harmony']['harmony_type']['patterns'])): ?>
                <div class="gl-harmony-patterns">
                    <?php foreach ($analysis['harmony']['harmony_type']['patterns'] as $pattern): ?>
                        <span class="gl-harmony-pattern">
                            <?php echo esc_html(ucfirst($pattern)); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="gl-harmony-strength">
                <label><?php esc_html_e('Harmony Strength', 'gl-color-palette-generator'); ?></label>
                <div class="gl-strength-meter">
                    <div class="gl-strength-bar" style="width: <?php echo esc_attr($analysis['harmony']['harmony_type']['strength'] * 100); ?>%"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($analysis['accessibility'])): ?>
        <div class="gl-analysis-section">
            <h3><?php esc_html_e('Accessibility', 'gl-color-palette-generator'); ?></h3>

            <?php if (!empty($analysis['accessibility']['recommendations'])): ?>
                <div class="gl-recommendations">
                    <?php foreach ($analysis['accessibility']['recommendations'] as $rec): ?>
                        <div class="gl-recommendation <?php echo esc_attr($rec['severity']); ?>">
                            <?php echo esc_html($rec['message']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="gl-color-blindness">
                <h4><?php esc_html_e('Color Blindness Simulation', 'gl-color-palette-generator'); ?></h4>
                <div class="gl-simulation-grid">
                    <?php foreach ($analysis['accessibility']['color_blindness'] as $simulation): ?>
                        <div class="gl-simulation-item">
                            <div class="gl-color-preview">
                                <span style="background-color: <?php echo esc_attr($simulation['color']); ?>"></span>
                                <div class="gl-simulation-types">
                                    <?php foreach (['protanopia', 'deuteranopia', 'tritanopia'] as $type): ?>
                                        <span style="background-color: rgb(<?php echo esc_attr(implode(',', $simulation[$type])); ?>)"></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div> 

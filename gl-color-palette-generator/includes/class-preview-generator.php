<?php

class PreviewGenerator {
    /**
     * Generate HTML preview for a color set
     */
    public function generate_palette_preview($colors) {
        ob_start();
        ?>
        <div class="color-palette-preview">
            <?php foreach ($colors as $role => $color_set): ?>
                <div class="color-group">
                    <h3><?php echo ucfirst($role); ?> Colors</h3>
                    <div class="color-swatches">
                        <?php foreach (['lighter', 'light', 'dark', 'darker'] as $variation): ?>
                            <div class="color-swatch"
                                 style="background-color: <?php echo esc_attr($color_set['hex'][$variation]); ?>">
                                <span class="color-label"
                                      style="color: <?php echo $this->get_contrast_color($color_set['hex'][$variation]); ?>">
                                    <?php echo esc_html($color_set['names'][$variation]); ?>
                                    <br>
                                    <?php echo esc_html($color_set['hex'][$variation]); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Generate comprehensive preview of WordPress elements
     */
    public function generate_variation_preview($variation) {
        ob_start();
        ?>
        <div class="style-variation-preview">
            <!-- Header Area -->
            <header class="preview-header"
                    style="background-color: var(--preview-primary-light);">
                <div class="site-branding">
                    <h1 style="color: var(--preview-primary-darker);">Site Title</h1>
                    <p class="site-description"
                       style="color: var(--preview-primary-dark);">Site tagline goes here</p>
                </div>

                <nav class="primary-navigation">
                    <ul class="menu-items">
                        <li><a href="#" style="color: var(--preview-secondary-dark);">Home</a></li>
                        <li><a href="#" style="color: var(--preview-secondary-dark);">About</a></li>
                        <li class="current-menu-item">
                            <a href="#"
                               style="color: var(--preview-secondary-darker);
                                      background-color: var(--preview-secondary-lighter);">
                                Blog
                            </a>
                        </li>
                        <li><a href="#" style="color: var(--preview-secondary-dark);">Contact</a></li>
                    </ul>
                </nav>
            </header>

            <!-- Main Content Area -->
            <main class="preview-content"
                  style="background-color: var(--preview-primary-lighter);
                         color: var(--preview-primary-darker);">

                <!-- Featured Content -->
                <div class="featured-content"
                     style="background-color: var(--preview-primary-dark);
                            color: var(--preview-primary-lighter);">
                    <h2>Featured Content</h2>
                    <p>Hero section with contrasting background</p>
                    <button class="preview-button-inverse"
                            style="background-color: var(--preview-primary-lighter);
                                   color: var(--preview-primary-darker);">
                        Learn More
                    </button>
                </div>

                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Main Article -->
                    <article class="main-article">
                        <h2 style="color: var(--preview-primary-dark);">Main Article Title</h2>

                        <!-- Paragraph Block -->
                        <p>Regular paragraph text with <a href="#"
                           style="color: var(--preview-secondary-dark);">inline link</a>
                           styling demonstration.</p>

                        <!-- Quote Block -->
                        <blockquote class="wp-block-quote"
                                  style="border-left-color: var(--preview-secondary-light);">
                            <p style="color: var(--preview-primary-dark);">
                                This is a blockquote example to demonstrate quote styling
                            </p>
                            <cite style="color: var(--preview-secondary-dark);">
                                — Quote Attribution
                            </cite>
                        </blockquote>

                        <!-- List Blocks -->
                        <div class="wp-block-columns">
                            <div class="wp-block-column">
                                <h4 style="color: var(--preview-primary-dark);">
                                    Unordered List
                                </h4>
                                <ul>
                                    <li>First list item</li>
                                    <li>Second list item</li>
                                    <li>Third list item</li>
                                </ul>
                            </div>
                            <div class="wp-block-column">
                                <h4 style="color: var(--preview-primary-dark);">
                                    Ordered List
                                </h4>
                                <ol>
                                    <li>First numbered item</li>
                                    <li>Second numbered item</li>
                                    <li>Third numbered item</li>
                                </ol>
                            </div>
                        </div>

                        <!-- Button Group -->
                        <div class="wp-block-buttons">
                            <button class="preview-button primary"
                                    style="background-color: var(--preview-primary-dark);
                                           color: var(--preview-primary-lighter);">
                                Primary Button
                            </button>
                            <button class="preview-button secondary"
                                    style="background-color: var(--preview-secondary-dark);
                                           color: var(--preview-primary-lighter);">
                                Secondary Button
                            </button>
                            <button class="preview-button outline"
                                    style="border-color: var(--preview-primary-dark);
                                           color: var(--preview-primary-dark);">
                                Outline Button
                            </button>
                        </div>

                        <!-- Table Block -->
                        <figure class="wp-block-table">
                            <table style="border-color: var(--preview-primary-light);">
                                <thead style="background-color: var(--preview-primary-dark);
                                           color: var(--preview-primary-lighter);">
                                    <tr>
                                        <th>Header 1</th>
                                        <th>Header 2</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Cell 1</td>
                                        <td>Cell 2</td>
                                    </tr>
                                    <tr style="background-color: var(--preview-primary-light);">
                                        <td>Cell 3</td>
                                        <td>Cell 4</td>
                                    </tr>
                                </tbody>
                            </table>
                        </figure>
                    </article>

                    <!-- Sidebar -->
                    <aside class="sidebar">
                        <!-- Search Widget -->
                        <div class="widget search-widget"
                             style="background-color: var(--preview-primary-light);">
                            <h3 style="color: var(--preview-primary-darker);">Search</h3>
                            <div class="search-form">
                                <input type="text"
                                       placeholder="Search..."
                                       style="border-color: var(--preview-primary-dark);">
                                <button style="background-color: var(--preview-primary-dark);
                                           color: var(--preview-primary-lighter);">
                                    Search
                                </button>
                            </div>
                        </div>

                        <!-- Categories Widget -->
                        <div class="widget"
                             style="background-color: var(--preview-primary-light);">
                            <h3 style="color: var(--preview-primary-darker);">Categories</h3>
                            <ul class="category-list">
                                <li>
                                    <a href="#"
                                       style="color: var(--preview-secondary-dark);">
                                        Category 1
                                    </a>
                                </li>
                                <li>
                                    <a href="#"
                                       style="color: var(--preview-secondary-dark);">
                                        Category 2
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Call to Action Widget -->
                        <div class="widget cta-widget"
                             style="background-color: var(--preview-secondary-light);
                                    color: var(--preview-secondary-darker);">
                            <h3>Newsletter</h3>
                            <p>Sign up for updates</p>
                            <button style="background-color: var(--preview-secondary-dark);
                                         color: var(--preview-secondary-lighter);">
                                Subscribe
                            </button>
                        </div>
                    </aside>
                </div>
            </main>

            <!-- Footer -->
            <footer class="preview-footer"
                    style="background-color: var(--preview-primary-dark);
                           color: var(--preview-primary-lighter);">
                <div class="footer-widgets">
                    <div class="footer-widget">
                        <h4>About Us</h4>
                        <p>Footer widget content</p>
                    </div>
                    <div class="footer-widget">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="#"
                                   style="color: var(--preview-primary-lighter);">
                                    Privacy Policy
                                </a>
                            </li>
                            <li><a href="#"
                                   style="color: var(--preview-primary-lighter);">
                                    Terms of Service
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom"
                     style="border-top-color: var(--preview-primary-light);">
                    <p>© 2024 Your Website. All rights reserved.</p>
                </div>
            </footer>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get contrasting text color (black or white)
     */
    private function get_contrast_color($hex) {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $luminance = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $luminance > 128 ? '#000000' : '#ffffff';
    }
} 

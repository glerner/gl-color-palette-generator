Contributing to GL Color Palette Generator

Prerequisites

PHP 8.0 or higher
WordPress 6.2 or higher
Composer
Git
MySQL/MariaDB
PHPUnit

Setup

1. Clone the Repository
git clone https://github.com/GeorgeLerner/gl-color-palette-generator.git
cd gl-color-palette-generator

2. Install Dependencies
composer install

3. Configure Environment
cp .env.example .env
nano .env

4. Setup WordPress Test Environment
bash bin/install-wp-tests.sh wordpress_test root 'your_password' localhost latest

Testing

1. Run All Tests
composer test

2. Run Unit Tests Only
composer test:unit

3. Run Integration Tests Only
composer test:integration

4. Check Code Standards
composer phpcs
composer phpcbf

Development

1. Create Feature Branch
git checkout -b feature/your-feature-name

2. Enable Debug Mode in wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

3. Test Changes
Run unit tests
Test multiple WordPress versions
Test multiple PHP versions
Verify API integrations

4. Submit Changes
git add .
git commit -m "Description of changes"
git push origin feature/your-feature-name

API Setup

1. OpenAI
Get key: https://platform.openai.com/
Add to .env: OPENAI_API_KEY=your_key_here

2. Anthropic
Get key: https://console.anthropic.com/
Add to .env: ANTHROPIC_API_KEY=your_key_here

3. Azure OpenAI
Get credentials from Azure Portal
Add to .env:
AZURE_OPENAI_API_KEY=your_key_here
AZURE_OPENAI_RESOURCE=your_resource_here
AZURE_OPENAI_DEPLOYMENT=your_deployment_here

4. Cohere
Get key: https://dashboard.cohere.ai/
Add to .env:
COHERE_API_KEY=your_key_here

5. HuggingFace
Get key: https://huggingface.co/settings/tokens
Add to .env:
HUGGINGFACE_API_KEY=your_key_here
HUGGINGFACE_MODEL_ID=your_model_here

6. PaLM
Get key: https://makersuite.google.com/app/apikey
Add to .env:
PALM_API_KEY=your_key_here

Troubleshooting

1. Database Issues
mysql -u root -p -e "DROP DATABASE IF EXISTS wordpress_test"
bash bin/install-wp-tests.sh wordpress_test root 'your_password' localhost latest

2. Composer Issues
composer clear-cache
composer update

3. Test Suite Issues
rm -rf /tmp/wordpress-tests-lib
rm -rf /tmp/wordpress
bash bin/install-wp-tests.sh wordpress_test root 'your_password' localhost latest

Resources

WordPress Plugin Handbook: https://developer.wordpress.org/plugins/
WordPress Coding Standards: https://developer.wordpress.org/coding-standards/
PHPUnit Documentation: https://phpunit.de/documentation.html

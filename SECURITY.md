# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability within GL Color Palette Generator, please send an email to security-glcpg[at]glerner.com. All security vulnerabilities will be promptly addressed.

Please do not report security vulnerabilities through public GitHub issues.

## Security Measures

1. **API Key Protection**
   - All API keys are encrypted before storage
   - Keys are only accessible to administrators
   - Keys are never exposed in client-side code

2. **WordPress Integration**
   - Proper nonce verification for all forms
   - Capability checks for administrative functions
   - Input sanitization and validation
   - Prepared SQL statements

3. **Frontend Security**
   - XSS prevention
   - CSRF protection
   - Content Security Policy headers
   - Sanitized React props

4. **API Endpoints**
   - Rate limiting
   - Request validation
   - Authentication checks
   - CORS configuration

## Best Practices

When using this plugin:

1. Keep WordPress core, themes, and plugins updated
2. Use strong passwords, kept in a secure password manager software
3. Implement proper file permissions
4. Enable SSL/HTTPS
5. Regular security audits
6. Monitor error logs
7. Backup regularly

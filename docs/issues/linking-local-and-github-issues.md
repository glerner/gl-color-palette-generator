# Linking Local and GitHub Issues

## Overview
Document the simple process for linking local issues (in `.github/issues/`) with GitHub web interface issues.

## Process

### When Creating a New Issue
1. Create the detailed technical documentation in `.github/issues/[descriptive-name].md`
2. Create the GitHub issue through web interface at github.com/glerner/gl-color-palette-generator/issues
3. Add these two lines near the top of your local markdown file:
   ```markdown
   GitHub Issue: #123
   Status: Open
   ```
4. Add this line in the GitHub web issue description:
   ```markdown
   Technical Documentation: [Full Details](.github/issues/[descriptive-name].md)
   ```

That's it! No complex synchronization needed. The local markdown files serve as detailed technical documentation, while GitHub issues handle tracking and project management.

## Example
```markdown
# Implement Color Blindness Testing
GitHub Issue: #4
Status: Open

## Overview
Implement comprehensive color blindness simulation...
```

## Note
- Don't worry about keeping status in sync - GitHub is source of truth for status
- Local files are for technical details and requirements
- GitHub issues are for tracking and discussion

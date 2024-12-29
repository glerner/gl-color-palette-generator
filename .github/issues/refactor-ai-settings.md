# Refactor AI Provider Settings

Currently, AI provider settings like temperature and max_tokens are stored in `interface-color-constants.php`. Consider moving these to environment variables or WordPress options for more flexibility.

## Background
- AI provider settings are currently hardcoded in `Color_Constants::AI_CONFIG`
- Different providers may need different optimal settings
- Users might want to tune these settings for their specific needs

## Proposed Changes
1. Move provider-specific settings to environment variables with fallbacks:
   ```php
   GL_CPG_AI_TEMPERATURE=0.7
   GL_CPG_AI_MAX_TOKENS=500
   GL_CPG_AI_TOP_P=0.9
   GL_CPG_AI_FREQ_PENALTY=0.0
   ```

2. Add WordPress options UI for advanced users to tune settings

3. Keep `AI_RESPONSE_FORMAT` in `Color_Constants` as it's part of our core functionality

## Benefits
- More flexible configuration per environment
- Easier testing with different settings
- Better separation of concerns

## Implementation Notes
- Maintain backward compatibility
- Add validation for setting ranges
- Document recommended values
- Consider provider-specific setting profiles

# Add Support for Llama and Ollama AI Providers

## Description
Add support for additional AI providers:
1. Llama (via cloud.llamaindex.ai)
2. Ollama (local LLM support)

This will expand the plugin's capabilities to include both cloud-based and local LLM options.

## Requirements

### Llama Provider
- Integration with cloud.llamaindex.ai API
- API key configuration support
- Response parsing for color palette generation
- Unit and integration tests

### Ollama Provider
- Local LLM support
- Configuration for model selection
- Proper prompt engineering for color generation
- Support for different model types
- Unit and integration tests

## Technical Considerations
1. Provider Classes:
   - Create `Llama_Provider` extending `AI_Provider_Base`
   - Create `Ollama_Provider` extending `AI_Provider_Base`
   
2. Configuration:
   - Add environment variables for API keys
   - Add settings for Ollama endpoint configuration
   - Update `.env.sample.testing` with new provider options

3. Testing:
   - Add integration tests for both providers
   - Include mock responses for testing without API access
   - Test local Ollama connectivity

## Implementation Steps
1. [ ] Create provider classes
2. [ ] Implement API integration
3. [ ] Add configuration options
4. [ ] Create tests
5. [ ] Update documentation

## Labels
- enhancement
- providers
- testing
- documentation

## Priority
Medium - Adding new provider options without disrupting existing functionality

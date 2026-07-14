#!/bin/bash

# Google Gemini Model Tester
# Tests ALL models available for the API key by:
# 1. Fetching the complete model list dynamically
# 2. Testing each model that supports generateContent with a "Hi" prompt
# 3. Saving results to academy/output.txt

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

OUTPUT_FILE="$SCRIPT_DIR/output.txt"

# --- Extract API key ---
if [ ! -f .env ]; then
    echo "ERROR: .env file not found in $SCRIPT_DIR" | tee "$OUTPUT_FILE"
    exit 1
fi

API_KEY=$(grep -E '^GEMINI_API_KEY=' .env | head -1 | cut -d '=' -f2- | tr -d '"' | tr -d "'" | xargs)

if [ -z "$API_KEY" ]; then
    echo "ERROR: GEMINI_API_KEY not found in .env" | tee "$OUTPUT_FILE"
    exit 1
fi

# Mask key for logs
MASKED_KEY="${API_KEY:0:6}...${API_KEY: -4}"
echo "API Key found: $MASKED_KEY"

# --- Start output ---
{
    echo "========================================"
    echo "Google Gemini Model Test - $(date '+%Y-%m-%d %H:%M:%S')"
    echo "========================================"
    echo ""
} > "$OUTPUT_FILE"

# --- Fetch all models ---
echo ""
echo "Fetching model list from Google API..."
MODELS_JSON=$(curl -s -f "https://generativelanguage.googleapis.com/v1beta/models?key=$API_KEY" 2>/dev/null || true)

# Check if the response has an error
if echo "$MODELS_JSON" | grep -q '"error"'; then
    ERROR_MSG=$(echo "$MODELS_JSON" | python3 -c "import sys,json; print(json.load(sys.stdin).get('error',{}).get('message','Unknown error'))" 2>/dev/null || echo "$MODELS_JSON")
    {
        echo "ERROR: Failed to fetch models from API"
        echo "API Response: $ERROR_MSG"
        echo ""
    } >> "$OUTPUT_FILE"
    echo "Failed to fetch models: $ERROR_MSG"
    exit 1
fi

# Parse model names using jq (preferred) or python3 fallback
if command -v jq &> /dev/null; then
    # Extract model names (just the short name after "models/")
    MODEL_NAMES=$(echo "$MODELS_JSON" | jq -r '.models[].name' 2>/dev/null | sed 's|^models/||')
else
    # Fallback: use python3
    MODEL_NAMES=$(python3 -c "
import json, sys
data = json.load(sys.stdin)
for m in data.get('models', []):
    print(m['name'].replace('models/', ''))
" 2>/dev/null <<< "$MODELS_JSON" || echo "")
fi

if [ -z "$MODEL_NAMES" ]; then
    {
        echo "ERROR: No models found in API response"
        echo "Raw response (first 500 chars): ${MODELS_JSON:0:500}"
    } >> "$OUTPUT_FILE"
    echo "No models returned. Check output.txt for details."
    exit 1
fi

MODEL_COUNT=$(echo "$MODEL_NAMES" | wc -l)
echo "Found $MODEL_COUNT models to test"
echo ""

# --- Test each model ---
TOTAL=0
AVAILABLE=0
UNAVAILABLE=0
GENERATIVE=0
EMBEDDING=0
OTHER=0

# Write column headers
{
    printf "%-45s %-14s %s\n" "MODEL" "STATUS" "RESPONSE"
    printf "%-45s %-14s %s\n" "---------------------------------------------" "--------------" "----------------------------------------"
} >> "$OUTPUT_FILE"

echo "Testing models..."
echo ""

while IFS= read -r model; do
    [ -z "$model" ] && continue
    TOTAL=$((TOTAL + 1))
    
    # Get supported generation methods for this model
    if command -v jq &> /dev/null; then
        METHODS_JSON=$(echo "$MODELS_JSON" | jq -c ".models[] | select(.name == \"models/$model\") | .supportedGenerationMethods" 2>/dev/null || echo "[]")
    else
        METHODS_JSON=$(python3 -c "
import json, sys
data = json.load(sys.stdin)
for m in data.get('models', []):
    if m['name'] == 'models/$model':
        print(json.dumps(m.get('supportedGenerationMethods', [])))
        break
" 2>/dev/null <<< "$MODELS_JSON" || echo "[]")
    fi
    
    HAS_GENERATE_CONTENT=false
    HAS_EMBED_CONTENT=false
    
    if echo "$METHODS_JSON" | grep -q 'generateContent'; then
        HAS_GENERATE_CONTENT=true
    fi
    if echo "$METHODS_JSON" | grep -q 'embedContent'; then
        HAS_EMBED_CONTENT=true
    fi
    
    if [ "$HAS_GENERATE_CONTENT" = true ]; then
        # Test with generateContent
        GENERATIVE=$((GENERATIVE + 1))
        
        RESPONSE=$(curl -s -w "\n%{http_code}" -X POST \
            "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$API_KEY" \
            -H "Content-Type: application/json" \
            -d '{"contents":[{"parts":[{"text":"Hi"}]}]}' 2>/dev/null)
        
        HTTP_CODE=$(echo "$RESPONSE" | tail -1)
        BODY=$(echo "$RESPONSE" | sed '$d')
        
        if [ "$HTTP_CODE" = "200" ]; then
            AVAILABLE=$((AVAILABLE + 1))
            # Extract response text using jq or python3
            if command -v jq &> /dev/null; then
                RESPONSE_TEXT=$(echo "$BODY" | jq -r '.candidates[0].content.parts[0].text // "empty"' 2>/dev/null)
            else
                RESPONSE_TEXT=$(python3 -c "
import json, sys
data = json.load(sys.stdin)
try:
    t = data['candidates'][0]['content']['parts'][0]['text']
    print(t[:40])
except:
    print('empty')
" 2>/dev/null <<< "$BODY" || echo "empty")
            fi
            
            if [ -z "$RESPONSE_TEXT" ] || [ "$RESPONSE_TEXT" = "null" ]; then
                RESPONSE_TEXT="(empty or blocked)"
            fi
            # Truncate to 40 chars
            if [ ${#RESPONSE_TEXT} -gt 40 ]; then
                RESPONSE_TEXT="${RESPONSE_TEXT:0:37}..."
            fi
            printf "%-45s %-14s %s\n" "$model" "RESPONDING" "$RESPONSE_TEXT" >> "$OUTPUT_FILE"
            echo "[✓] $model -> RESPONDING (200): $RESPONSE_TEXT"
        else
            UNAVAILABLE=$((UNAVAILABLE + 1))
            # Extract error message
            if command -v jq &> /dev/null; then
                ERROR_MSG=$(echo "$BODY" | jq -r '.error.message // "HTTP '"$HTTP_CODE"'"' 2>/dev/null)
            else
                ERROR_MSG=$(python3 -c "
import json, sys
data = json.load(sys.stdin)
print(data.get('error', {}).get('message', 'HTTP $HTTP_CODE'))
" 2>/dev/null <<< "$BODY" || echo "HTTP $HTTP_CODE")
            fi
            if [ ${#ERROR_MSG} -gt 45 ]; then
                ERROR_MSG="${ERROR_MSG:0:42}..."
            fi
            printf "%-45s %-14s %s\n" "$model" "NOT AVAILABLE" "$ERROR_MSG" >> "$OUTPUT_FILE"
            echo "[✗] $model -> NOT AVAILABLE ($HTTP_CODE): $ERROR_MSG"
        fi
    elif [ "$HAS_EMBED_CONTENT" = true ]; then
        # Embedding-only model
        EMBEDDING=$((EMBEDDING + 1))
        RESPONSE=$(curl -s -w "\n%{http_code}" -X POST \
            "https://generativelanguage.googleapis.com/v1beta/models/$model:embedContent?key=$API_KEY" \
            -H "Content-Type: application/json" \
            -d '{"model":"models/'"$model"'","content":{"parts":[{"text":"Hi"}]}}' 2>/dev/null)
        HTTP_CODE=$(echo "$RESPONSE" | tail -1)
        
        if [ "$HTTP_CODE" = "200" ]; then
            AVAILABLE=$((AVAILABLE + 1))
            printf "%-45s %-14s %s\n" "$model" "AVAILABLE" "(embedding model)" >> "$OUTPUT_FILE"
            echo "[✓] $model -> AVAILABLE (embedding, $HTTP_CODE)"
        else
            UNAVAILABLE=$((UNAVAILABLE + 1))
            printf "%-45s %-14s %s\n" "$model" "NOT AVAILABLE" "(embedding - HTTP $HTTP_CODE)" >> "$OUTPUT_FILE"
            echo "[✗] $model -> NOT AVAILABLE (embedding, $HTTP_CODE)"
        fi
    else
        # Other type of model
        OTHER=$((OTHER + 1))
        printf "%-45s %-14s %s\n" "$model" "SKIPPED" "(no generate/embed method)" >> "$OUTPUT_FILE"
        echo "[⬜] $model -> SKIPPED (no generateContent/embedContent method)"
    fi
    
done <<< "$MODEL_NAMES"

# --- Summary ---
{
    echo ""
    echo "========================================"
    echo "SUMMARY"
    echo "========================================"
    echo "Total models found:          $TOTAL"
    echo "Text generation models:      $GENERATIVE"
    echo "  - Responding:              $AVAILABLE"
    echo "  - Not responding:          $((GENERATIVE - (AVAILABLE < GENERATIVE ? AVAILABLE : GENERATIVE) + (UNAVAILABLE > 0 ? 1 : 0) ))"
    echo "Embedding models:            $EMBEDDING"
    echo "Other (skipped):             $OTHER"
    echo "----------------------------------------"
    echo "Total responding:            $AVAILABLE"
    echo "Total not available/skipped: $((TOTAL - AVAILABLE))"
    echo "========================================"
    echo ""
    echo "Test completed at: $(date '+%Y-%m-%d %H:%M:%S')"
} >> "$OUTPUT_FILE"

echo ""
echo "========================================"
echo "DONE! Results saved to: $OUTPUT_FILE"
echo "========================================"
echo ""
cat "$OUTPUT_FILE"
#!/bin/bash
# enhanced-process-test-results.sh - Processes test analysis results and generates Bash scripts for moving and text files for editing based on test analysis results

# Configuration
PROJECT_ROOT="/home/george/sites/gl-color-palette-generator"
RESULTS_FILE="$PROJECT_ROOT/test_analysis_results.txt"
PROGRESS_FILE="$PROJECT_ROOT/test_processing_progress.txt"
SCRIPTS_DIR="$PROJECT_ROOT/test_processing_scripts"
BATCH_SIZE=5

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Create scripts directory if it doesn't exist
mkdir -p "$SCRIPTS_DIR"

# Function to display script header
display_header() {
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE}   Enhanced Test Results Processing Script${NC}"
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE}This script generates executable scripts for:${NC}"
    echo -e "${BLUE}- Moving files to correct locations${NC}"
    echo -e "${BLUE}- and text files how to fix namespace issues${NC}"
    echo -e "${BLUE}============================================${NC}"
    echo ""
}

# Function to check if required files exist
check_files() {
    if [ ! -f "$RESULTS_FILE" ]; then
        echo -e "${RED}Error: Results file '$RESULTS_FILE' not found.${NC}"
        exit 1
    fi
}

# Function to get the last processed state
get_last_processed_state() {
    if [ -f "$PROGRESS_FILE" ]; then
        # Read batch and line number from progress file
        read -r last_batch last_line < "$PROGRESS_FILE"
        next_batch=$((last_batch + 1))
        echo -e "${YELLOW}Resuming from batch $next_batch (after completing batch $last_batch at line $last_line)${NC}"
        return $next_batch
    else
        echo -e "${YELLOW}Starting from the beginning${NC}"
        return 0
    fi
}

# Function to save the current batch and line progress
save_progress() {
    batch_num=$1
    line_num=$2
    echo "$batch_num $line_num" > "$PROGRESS_FILE"
}

# Function to generate a move script for a batch
generate_move_script() {
    batch_num=$1
    move_script="$SCRIPTS_DIR/batch_${batch_num}_move.sh"

    echo "#!/bin/bash" > "$move_script"
    echo "# Batch $batch_num - File Move Script" >> "$move_script"
    echo "# Generated on $(date)" >> "$move_script"
    echo "" >> "$move_script"
    echo "# Configuration" >> "$move_script"
    echo "PROJECT_ROOT=\"$PROJECT_ROOT\"" >> "$move_script"
    echo "" >> "$move_script"
    echo "# Create target directories if they don't exist" >> "$move_script"

    # Add directory creation commands
    for move in "${@:2}"; do
        IFS=':' read -r _ source_path dest_path <<< "$move"
        dest_dir=$(dirname "$dest_path")
        echo "mkdir -p \"$dest_dir\"" >> "$move_script"
    done

    echo "" >> "$move_script"
    echo "# Move files using git mv for proper tracking" >> "$move_script"

    # Add move commands
    # If already moved, will give a message you can ignore
    for move in "${@:2}"; do
        IFS=':' read -r _ source_path dest_path <<< "$move"
        echo "git mv \"$source_path\" \"$dest_path\"" >> "$move_script"
    done

    # Make the script executable
    chmod +x "$move_script"

    echo -e "${GREEN}Move script generated: $move_script${NC}"
}

# Function to generate an edit reference file for a batch
generate_edit_script() {
    batch_num=$1
    edit_file="$SCRIPTS_DIR/batch_${batch_num}_edits.txt"

    echo "# Batch $batch_num - File Edit Script" > "$edit_file"
    echo "# Generated on $(date)" >> "$edit_file"
    echo "" >> "$edit_file"
    echo "# IMPORTANT: This is a reference file only. Do NOT execute directly." >> "$edit_file"
    echo "# Review each edit carefully before implementing." >> "$edit_file"
    echo "" >> "$edit_file"

    # Add edit commands using sed
    for edit in "${@:2}"; do
        # echo -e "${GREEN}DEBUG: Edit entry: $edit${NC}"
        # First, include the original EDIT line as a comment
        echo "# Original instruction: $edit" >> "$edit_file"
        echo "" >> "$edit_file"

        IFS=':' read -r _ file_path edit_instruction <<< "$edit"

        # Handle different types of edits based on the instruction
        if [[ "$edit_instruction" == *"Update namespace"* ]]; then
            # Extract the old and new namespace from the instruction
            # Look for the pattern "Update namespace from X to Y" in the edit instructions
            if [[ "$edit_instruction" =~ from\ ([^\ ]+)\ to\ ([^\ ]+) ]]; then
                old_namespace="${BASH_REMATCH[1]}"
                new_namespace="${BASH_REMATCH[2]}"

                echo "# Update namespace in $file_path" >> "$edit_file"
                # Escape backslashes for sed
                old_namespace_escaped=$(echo "$old_namespace" | sed 's/\\/\\\\/g')
                new_namespace_escaped=$(echo "$new_namespace" | sed 's/\\/\\\\/g')
                echo "sed -i 's|namespace $old_namespace_escaped|namespace $new_namespace_escaped|g' "$file_path"" >> "$edit_file"
            fi
            echo "" >> "$edit_file"
        elif [[ "$edit_instruction" == *"Change use"* ]]; then
            # Extract the old and new use statement using regex pattern matching
            if [[ "$edit_instruction" =~ Change\ use\ ([^\ ]+)\ to\ use\ ([^\ ;]+) ]]; then
                old_use="${BASH_REMATCH[1]}"
                new_use="${BASH_REMATCH[2]}"

                echo "# Update use statement in $file_path" >> "$edit_file"
                # Escape backslashes for sed
                old_use_escaped=$(echo "$old_use" | sed 's/\\/\\\\/g')
                new_use_escaped=$(echo "$new_use" | sed 's/\\/\\\\/g')
                echo "sed -i 's|use $old_use_escaped|use $new_use_escaped|g' "$file_path"" >> "$edit_file"
            fi
            echo "" >> "$edit_file"
        elif [[ "$edit_instruction" == *"Rename class"* ]]; then
            # Extract the old and new class name using regex pattern matching
            if [[ "$edit_instruction" =~ Rename\ class\ from\ ([^\ ]+)\ to\ ([^\ ]+) ]]; then
                old_class="${BASH_REMATCH[1]}"
                new_class="${BASH_REMATCH[2]}"

                echo "# Rename class in $file_path" >> "$edit_file"
                echo "sed -i 's|class $old_class|class $new_class|g' "$file_path"" >> "$edit_file"
            fi
            echo "" >> "$edit_file"
        elif [[ "$edit_instruction" == *"@subpackage"* ]]; then
            # Handle docblock @subpackage changes using regex pattern matching
            echo "# Update @subpackage in docblock for $file_path" >> "$edit_file"

            # Try to extract using regex pattern matching
            if [[ "$edit_instruction" =~ @subpackage\ from\ ([^\ ]+)\ to\ ([^\ ]+) ]]; then
                old_subpackage="${BASH_REMATCH[1]}"
                new_subpackage="${BASH_REMATCH[2]}"
                echo "sed -i 's|@subpackage $old_subpackage|@subpackage $new_subpackage|g' "$file_path"" >> "$edit_file"
            else
                # If we can't extract the exact pattern, provide a reasonable default based on common patterns
                echo "# Unable to extract exact subpackage details, manual review required" >> "$edit_file"
                echo "# Try this command but verify first:" >> "$edit_file"
                echo "sed -i 's|@subpackage Tests\\\\Integration|@subpackage Tests\\\\Integration\\\\Providers|g' "$file_path"" >> "$edit_file"
            fi
            echo "" >> "$edit_file"
        else
            # For other edits, add a comment with the instruction
            echo "# TODO: Manual edit needed for $file_path" >> "$edit_file"
            echo "# $edit_instruction" >> "$edit_file"
            echo "echo \"Manual edit needed for $file_path: $edit_instruction\"" >> "$edit_file"
            echo "" >> "$edit_file"
        fi
    done

    # No need to make the file executable as it's just a reference

    echo -e "${GREEN}Edit reference file generated: $edit_file${NC}"
}


# Main function
main() {
    display_header
    check_files

    # Get the last processed state
    get_last_processed_state
    last_batch=$?

    # Get total number of lines in the results file
    total_lines=$(wc -l < "$RESULTS_FILE")
    total_non_comment_lines=$(grep -v "^#" "$RESULTS_FILE" | grep -v "^$" | wc -l)

    echo -e "${BLUE}Total non-comment lines in results file: $total_non_comment_lines${NC}"

    # Calculate number of batches (each batch processes BATCH_SIZE entries)
    total_batches=$(( (total_non_comment_lines + BATCH_SIZE - 1) / BATCH_SIZE ))
    echo -e "${BLUE}Estimated batches: $total_batches${NC}"

    # Process batches
    current_batch=$last_batch

    # Determine starting line
    if [ $current_batch -gt 0 ]; then
        # Read the last processed line from the progress file
        read -r _ current_line < "$PROGRESS_FILE"

        # Start from the next line
        current_line=$((current_line + 1))
    else
        # Start from the beginning, but skip comment lines at the top
        current_line=1
        while IFS= read -r line && ([[ "$line" =~ ^#.*$ ]] || [[ -z "$line" ]]) && [ $current_line -lt $total_lines ]; do
            current_line=$((current_line + 1))
        done < <(head -n 20 "$RESULTS_FILE")
    fi


    # Arrays to track all files across all batches (for debugging only)
    declare -a all_files_to_move
    declare -a all_files_to_edit

    while [ $current_line -le $total_lines ]; do
        # Track the starting line for this batch
        batch_start_line=$current_line

        echo -e "${GREEN}DEBUG: Batch $current_batch Starting from line: $current_line${NC}"

        # Process a fixed number of entries (BATCH_SIZE) in this batch
        entries_processed=0
        last_processed_line=$current_line

        # Reset arrays for this batch only - ensure they're completely empty
        unset files_to_move
        unset files_to_edit
        declare -a files_to_move=()
        declare -a files_to_edit=()

        echo -e "${BLUE}============================================${NC}"
        echo -e "${BLUE}   Processing Batch $current_batch${NC}"
        echo -e "${BLUE}============================================${NC}"

        # Process lines until we've found BATCH_SIZE entries or reached end of file
        while [ $entries_processed -lt $BATCH_SIZE ] && [ $current_line -le $total_lines ]; do
            # Read the current line
            line=$(sed -n "${current_line}p" "$RESULTS_FILE")

            # Skip comments and empty lines
            if [[ "$line" =~ ^#.*$ ]] || [[ -z "$line" ]]; then
                current_line=$((current_line + 1))
                continue
            fi

            # Process MOVE lines - store with line number
            if [[ "$line" == MOVE:* ]]; then
                files_to_move+=("$current_line:$line")
                entries_processed=$((entries_processed + 1))
                last_processed_line=$current_line
            fi

            # Process EDIT lines - store with line number
            if [[ "$line" == EDIT:* ]]; then
                files_to_edit+=("$current_line:$line")
                entries_processed=$((entries_processed + 1))
                last_processed_line=$current_line
            fi

            # Move to next line
            current_line=$((current_line + 1))

            # If we've reached the end of the file, break out of the loop
            if [ $current_line -gt $total_lines ]; then
                break
            fi
        done

        # Display files being processed with their line numbers
        echo -e "${YELLOW}Files to move:${NC}"
        for move_entry in "${files_to_move[@]}"; do
            # Split the line number from the rest of the entry
            line_num=$(echo "$move_entry" | cut -d':' -f1)
            move_line=$(echo "$move_entry" | cut -d':' -f2-)

            # Parse the move line to get source and target paths
            IFS=':' read -r _ source_path target_path <<< "$move_line"
            echo -e "${YELLOW}  Line $line_num: $source_path -> $target_path${NC}"
        done

        echo -e "${YELLOW}Files to edit:${NC}"
        for edit_entry in "${files_to_edit[@]}"; do
            # Split the line number from the rest of the entry
            line_num=$(echo "$edit_entry" | cut -d':' -f1)
            edit_line=$(echo "$edit_entry" | cut -d':' -f2-)

            # Parse the edit line to get file path
            IFS=':' read -r _ file_path edit_instruction <<< "$edit_line"
            echo -e "${YELLOW}  Line $line_num: $file_path${NC}"
        done

        # Generate move script if there are files to move
        if [ ${#files_to_move[@]} -gt 0 ]; then
            echo -e "${YELLOW}Generating move script for batch $current_batch with ${#files_to_move[@]} files...${NC}"
            # Strip line numbers before passing to generate_move_script
            declare -a move_lines_without_line_nums
            for move_entry in "${files_to_move[@]}"; do
                # Remove the line number prefix
                move_line=$(echo "$move_entry" | cut -d':' -f2-)
                move_lines_without_line_nums+=("$move_line")
            done
            generate_move_script $current_batch "${move_lines_without_line_nums[@]}"
        else
            echo -e "${YELLOW}No files to move in batch $current_batch.${NC}"
        fi

        # Generate edit script if there are files to edit
        if [ ${#files_to_edit[@]} -gt 0 ]; then
            echo -e "${YELLOW}Generating edit script for batch $current_batch with ${#files_to_edit[@]} edits...${NC}"
            # Strip line numbers before passing to generate_edit_script
            # Properly reset the array to ensure we don't accumulate entries from previous batches
            unset edit_lines_without_line_nums
            declare -a edit_lines_without_line_nums=()
            for edit_entry in "${files_to_edit[@]}"; do
                # Remove the line number prefix
                edit_line=$(echo "$edit_entry" | cut -d':' -f2-)
                edit_lines_without_line_nums+=("$edit_line")
            done
            generate_edit_script $current_batch "${edit_lines_without_line_nums[@]}"
        else
            echo -e "${YELLOW}No files to edit in batch $current_batch.${NC}"
        fi

        # Save progress with both batch number and last processed line
        save_progress $current_batch $last_processed_line

        # Display message showing the current batch was completed
        echo -e "${GREEN}Batch $current_batch completed${NC}"
        echo -e "${GREEN}Last processed line: $last_processed_line${NC}"

        # Increment batch counter for the next iteration
        current_batch=$((current_batch + 1))

        echo -e "${YELLOW}Continue to next batch? [Y/n]${NC}"
        read -r answer
        if [[ "$answer" =~ ^[Nn]$ ]]; then
            echo -e "${BLUE}Processing paused. Resume later by running the script again.${NC}"
            break
        fi
    done

    # Check if we reached the end of the file or user interrupted
    if [ $current_line -gt $total_lines ]; then
        echo -e "${GREEN}Processing complete, with $current_batch batches.${NC}"
        # Remove progress file when complete
        if [ -f "$PROGRESS_FILE" ]; then
            rm "$PROGRESS_FILE"
            echo -e "${GREEN}Progress file removed.${NC}"
        fi
    else
        echo -e "${YELLOW}Processing paused at line $last_processed_line.${NC}"
        echo -e "${YELLOW}Run the script again to continue.${NC}"
    fi

    echo -e "${GREEN}Generated scripts are in: $SCRIPTS_DIR${NC}"
    echo -e "${YELLOW}Review and run these scripts to apply the changes.${NC}"
}

# Run the main function
main

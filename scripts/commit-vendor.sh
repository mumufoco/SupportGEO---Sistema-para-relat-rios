#!/usr/bin/env bash
################################################################################
# Vendor Directory Commit Script
################################################################################
#
# This script safely adds the vendor/ directory to the Git repository with
# appropriate safety checks and validations.
#
# IMPORTANT NOTES:
# - This script does NOT automatically push changes to remote
# - Review all changes carefully before pushing
# - Consider using Git LFS for files larger than 100MB
# - Verify all licenses are compatible with your project
#
# USAGE:
#   ./scripts/commit-vendor.sh
#
# PREREQUISITES:
#   - Run 'composer install' to generate vendor/ directory
#   - Ensure you're on the correct branch
#   - Have clean working directory (commit other changes first)
#
################################################################################

set -e  # Exit on error

# Configuration
readonly VENDOR_DIR="vendor"
readonly SIZE_WARNING_MB=100
readonly SIZE_BLOCK_MB=500
readonly COMMIT_MESSAGE="chore: add vendor dependencies to repo"
readonly RED='\033[0;31m'
readonly YELLOW='\033[1;33m'
readonly GREEN='\033[0;32m'
readonly BLUE='\033[0;34m'
readonly NC='\033[0m' # No Color

################################################################################
# Helper Functions
################################################################################

print_header() {
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

confirm() {
    local prompt="$1"
    local response
    while true; do
        read -r -p "$prompt [y/N]: " response
        case "$response" in
            [yY][eE][sS]|[yY]) 
                return 0
                ;;
            [nN][oO]|[nN]|"") 
                return 1
                ;;
            *) 
                echo "Please answer yes or no."
                ;;
        esac
    done
}

################################################################################
# Validation Functions
################################################################################

check_vendor_exists() {
    if [ ! -d "$VENDOR_DIR" ]; then
        print_error "Vendor directory does not exist!"
        print_info "Please run 'composer install' first to generate the vendor directory."
        exit 1
    fi
    
    if [ ! "$(ls -A $VENDOR_DIR)" ]; then
        print_error "Vendor directory is empty!"
        print_info "Please run 'composer install' to populate the vendor directory."
        exit 1
    fi
}

check_git_repository() {
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        print_error "Not a git repository!"
        print_info "Please run this script from the root of your git repository."
        exit 1
    fi
}

check_working_directory() {
    if ! git diff-index --quiet HEAD -- 2>/dev/null; then
        print_warning "You have uncommitted changes in your working directory."
        print_info "It's recommended to commit or stash other changes before committing vendor."
        if ! confirm "Do you want to continue anyway?"; then
            print_info "Aborted by user."
            exit 0
        fi
    fi
}

calculate_vendor_size() {
    local size_bytes
    local size_mb
    
    print_info "Calculating vendor directory size..."
    
    if command -v du > /dev/null 2>&1; then
        size_bytes=$(du -sb "$VENDOR_DIR" 2>/dev/null | cut -f1)
        size_mb=$((size_bytes / 1024 / 1024))
        echo "$size_mb"
    else
        print_warning "Cannot calculate directory size (du command not available)"
        echo "0"
    fi
}

check_large_files() {
    print_info "Checking for large files (>5MB)..."
    
    local large_files
    large_files=$(find "$VENDOR_DIR" -type f -size +5M 2>/dev/null || true)
    
    if [ -n "$large_files" ]; then
        print_warning "Found files larger than 5MB:"
        echo "$large_files" | while read -r file; do
            file_size=$(du -h "$file" 2>/dev/null | cut -f1 || echo "unknown")
            echo "  - $file ($file_size)"
        done
        echo ""
        print_warning "Consider using Git LFS for large files."
        print_info "See docs/COMMIT_VENDOR.md for Git LFS setup instructions."
        echo ""
    else
        print_success "No files larger than 5MB found."
    fi
}

validate_size() {
    local size_mb=$1
    
    print_info "Vendor directory size: ${size_mb}MB"
    echo ""
    
    if [ "$size_mb" -ge "$SIZE_BLOCK_MB" ]; then
        print_error "Vendor directory is too large (${size_mb}MB >= ${SIZE_BLOCK_MB}MB)!"
        print_info "This will significantly increase repository size and clone times."
        print_info "STRONGLY RECOMMENDED: Use Git LFS or consider alternative approaches."
        print_info "See docs/COMMIT_VENDOR.md for guidance."
        echo ""
        if ! confirm "Are you ABSOLUTELY SURE you want to proceed?"; then
            print_info "Aborted by user. Please review docs/COMMIT_VENDOR.md for alternatives."
            exit 0
        fi
    elif [ "$size_mb" -ge "$SIZE_WARNING_MB" ]; then
        print_warning "Vendor directory is large (${size_mb}MB >= ${SIZE_WARNING_MB}MB)."
        print_info "Consider using Git LFS for better performance."
        print_info "See docs/COMMIT_VENDOR.md for Git LFS setup instructions."
        echo ""
    else
        print_success "Vendor directory size is acceptable (${size_mb}MB)."
    fi
}

show_preview() {
    print_header "PREVIEW: Files to be Added"
    
    print_info "Sample of files to be committed:"
    find "$VENDOR_DIR" -type f 2>/dev/null | head -n 20 | sed 's/^/  /'
    
    local file_count
    file_count=$(find "$VENDOR_DIR" -type f | wc -l)
    
    print_info "Total files to be added: $file_count"
    echo ""
    
    if [ "$file_count" -gt 20 ]; then
        print_info "(Showing first 20 files only. Run 'git status' after adding for full list.)"
        echo ""
    fi
}

check_gitignore() {
    if grep -q "^vendor/" .gitignore 2>/dev/null; then
        print_error "vendor/ is still in .gitignore!"
        print_info "Please remove or comment out 'vendor/' from .gitignore first."
        print_info "See docs/COMMIT_VENDOR.md for instructions."
        exit 1
    fi
}

check_licenses() {
    print_info "License verification reminder:"
    print_warning "Please verify that all vendor package licenses are compatible with your project."
    print_info "Review composer.json license fields and vendor package LICENSE files."
    print_info "Common licenses: MIT, BSD, Apache-2.0, LGPL (review compatibility)"
    echo ""
}

################################################################################
# Main Script
################################################################################

main() {
    print_header "Vendor Directory Commit Script"
    echo ""
    
    # Validations
    check_git_repository
    check_vendor_exists
    check_gitignore
    check_working_directory
    
    # Size analysis
    local vendor_size_mb
    vendor_size_mb=$(calculate_vendor_size)
    validate_size "$vendor_size_mb"
    
    # Large file check
    check_large_files
    
    # License reminder
    check_licenses
    
    # Preview
    show_preview
    
    # Final confirmation
    print_header "FINAL CONFIRMATION"
    print_warning "This will:"
    echo "  1. Add all files in vendor/ directory to git staging"
    echo "  2. Create a commit with message: '$COMMIT_MESSAGE'"
    echo "  3. NOT automatically push to remote (you must push manually)"
    echo ""
    print_info "After this script completes, you should:"
    echo "  1. Review the commit: git show HEAD"
    echo "  2. Verify changes: git log --stat"
    echo "  3. Push when ready: git push origin <branch-name>"
    echo ""
    
    if ! confirm "Do you want to proceed with adding vendor/ to the repository?"; then
        print_info "Aborted by user."
        exit 0
    fi
    
    # Execute git operations
    print_info "Adding vendor/ directory to git..."
    git add "$VENDOR_DIR"
    
    print_info "Creating commit..."
    git commit -m "$COMMIT_MESSAGE"
    
    # Success
    print_header "SUCCESS"
    print_success "Vendor directory has been committed successfully!"
    echo ""
    print_info "Next steps:"
    echo "  1. Review the commit:"
    echo "     git show HEAD --stat"
    echo ""
    echo "  2. If everything looks good, push to remote:"
    echo "     git push origin \$(git branch --show-current)"
    echo ""
    echo "  3. Monitor repository size:"
    echo "     du -sh .git"
    echo ""
    print_warning "Remember: This commit will increase repository size by ~${vendor_size_mb}MB"
}

# Run main function
main "$@"

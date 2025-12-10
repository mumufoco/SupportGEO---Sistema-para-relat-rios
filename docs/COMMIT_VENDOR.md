# Committing Vendor Directory to Repository

This document provides comprehensive guidance for safely adding the `vendor/` directory to your Git repository.

---

## ⚠️ Important Considerations

### Risks and Trade-offs

**Adding vendor/ to your repository:**

✅ **Benefits:**
- Guaranteed reproducibility (exact dependency versions)
- No dependency on external package repositories
- Faster deployment (no composer install needed)
- Historical record of all dependencies

❌ **Risks:**
- Significantly larger repository size
- Slower clone and fetch operations
- Merge conflicts in generated code
- Increased storage costs
- Potential license compliance issues

### When to Commit Vendor

Consider committing vendor/ if:
- Your deployment environment has no internet access
- You need guaranteed reproducibility across years
- External package repositories are unreliable in your context
- Your deployment process cannot run composer install

Consider NOT committing vendor/ if:
- You have normal internet access during deployment
- Your repository is already large
- You have many contributors (merge conflicts)
- You deploy frequently

---

## 📋 Prerequisites

Before committing vendor directory:

1. **Composer installed and configured**
2. **Clean working directory** (commit other changes first)
3. **Updated composer.lock** (run `composer update` or `composer install`)
4. **Removed vendor/ from .gitignore** (already done in this repository)
5. **Reviewed licenses** (see License Compliance section below)

---

## 🚀 Step-by-Step Process

### Step 1: Generate Vendor Directory

If you don't have a vendor directory yet:

```bash
# Using Docker (recommended for this project)
docker compose exec app composer install --no-dev --optimize-autoloader

# Or locally if you have PHP/Composer
composer install --no-dev --optimize-autoloader
```

### Step 2: Inspect Vendor Directory

Before committing, inspect the size and contents:

```bash
# Check total size
du -sh vendor/

# Check file count
find vendor -type f | wc -l

# Find large files (>5MB)
find vendor -type f -size +5M -exec ls -lh {} \;

# Check largest directories
du -h vendor/ | sort -h | tail -20
```

### Step 3: Review Licenses

Verify license compatibility:

```bash
# List all licenses from composer
docker compose exec app composer licenses

# Or manually check LICENSE files
find vendor -name "LICENSE*" -o -name "license*" | head -20
```

**Common licenses and their compatibility:**
- ✅ MIT, BSD, Apache-2.0: Generally permissive and safe
- ⚠️ LGPL: Review carefully, may have linking restrictions
- ⚠️ GPL: Strong copyleft, ensure compatibility with your project
- ❌ Proprietary: May not be allowed in your repository

### Step 4: Run the Commit Script

Use the provided script for safe vendor commit:

```bash
# Make sure you're on the correct branch
git branch --show-current

# Run the script
./scripts/commit-vendor.sh
```

The script will:
1. Verify vendor directory exists and is not empty
2. Check that vendor/ is not in .gitignore
3. Calculate total size and warn if too large
4. Show preview of files to be added
5. Ask for confirmation before proceeding
6. Create a standardized commit

### Step 5: Review and Push

After the script completes:

```bash
# Review the commit
git show HEAD --stat

# Check commit size
git log --oneline --stat -1

# Push to remote (when ready)
git push origin $(git branch --show-current)
```

---

## 🔍 Advanced: Dividing by Vendor

For easier code review, you can commit vendors separately:

```bash
# Commit specific vendor packages
git add vendor/codeigniter4/
git commit -m "chore: add codeigniter4 framework to vendor"

git add vendor/psr/
git commit -m "chore: add PSR packages to vendor"

# Continue for other large vendors...
```

This approach:
- Makes PR diffs more reviewable
- Allows selective merging
- Simplifies troubleshooting
- Better commit history

---

## 📦 Git LFS (Large File Storage)

For files larger than 100MB, consider using Git LFS.

### Installing Git LFS

```bash
# Install Git LFS (if not already installed)
# Ubuntu/Debian
sudo apt-get install git-lfs

# macOS
brew install git-lfs

# Navigate to your repository directory
cd /path/to/SupportGEO

# Initialize Git LFS in the repository (one-time setup)
git lfs install

# This creates Git hooks and configures LFS for this repository
```

### Configuring Git LFS for Vendor

Add to `.gitattributes`:

```gitattributes
# Track large vendor files with Git LFS
vendor/**/*.zip filter=lfs diff=lfs merge=lfs -text
vendor/**/*.tar.gz filter=lfs diff=lfs merge=lfs -text
vendor/**/*.phar filter=lfs diff=lfs merge=lfs -text
vendor/**/docs/**/*.pdf filter=lfs diff=lfs merge=lfs -text
```

### Using Git LFS

```bash
# Track large file patterns
git lfs track "vendor/**/*.phar"

# Add and commit
git add .gitattributes
git commit -m "chore: configure Git LFS for large vendor files"

# Then commit vendor as normal
./scripts/commit-vendor.sh
```

**Note:** Git LFS requires server support and may have storage costs.

---

## 🔒 Security and License Compliance

### License Verification

Always verify licenses before committing:

```bash
# Generate license report
docker compose exec app composer licenses --format=json > vendor-licenses.json

# Review the report
cat vendor-licenses.json | jq '.[] | {name: .name, license: .license}'
```

### Security Scanning

Scan for known vulnerabilities:

```bash
# Using Composer
docker compose exec app composer audit

# Or use local security checker
docker compose exec app composer require --dev roave/security-advisories:dev-latest
```

### Sensitive Data Check

Before committing, ensure no sensitive data in vendor:

```bash
# Check for potential secrets (API keys, tokens, passwords)
grep -r -i "password\|secret\|api_key\|token" vendor/ --include="*.php" | grep -v "vendor/test" | head -20

# Check for .env files that shouldn't be there
find vendor/ -name ".env" -o -name ".env.*"
```

---

## 🛠️ Troubleshooting

### Vendor Already in .gitignore

If you get an error about vendor/ still being ignored:

```bash
# Check .gitignore
grep vendor .gitignore

# If still present, remove or comment it out
sed -i 's/^vendor\//# vendor\//g' .gitignore
```

### Commit Too Large

If GitHub rejects your push:

```bash
# Option 1: Use Git LFS (recommended)
# See Git LFS section above

# Option 2: Split into multiple commits
# See "Dividing by Vendor" section above

# Option 3: Filter large files
find vendor -type f -size +100M
# Remove or LFS-track these files specifically
```

### Merge Conflicts in Vendor

If you get conflicts in vendor/:

```bash
# Option 1: Accept incoming (usually safe for generated code)
git checkout --theirs vendor/

# Option 2: Regenerate vendor completely
rm -rf vendor/
composer install
git add vendor/
```

### Undoing Vendor Commit

If you need to remove vendor from history:

```bash
# WARNING: This rewrites history - coordinate with team!

# Remove vendor from last commit only
git rm -r --cached vendor/
git commit --amend -m "chore: revert vendor commit"

# If already pushed (dangerous!)
# git push origin +branch-name
```

---

## 📊 Repository Size Management

### Check Repository Size

```bash
# Check total repository size
du -sh .git

# Check size growth after vendor commit
git count-objects -vH
```

### Cleanup Old History (Advanced)

If repository becomes too large:

```bash
# Cleanup and compress
git gc --aggressive --prune=now

# For extreme cases, consider BFG Repo-Cleaner
# https://rtyley.github.io/bfg-repo-cleaner/
```

---

## 🔄 Alternative Approaches

### 1. Private Packagist

Use a private Composer repository:
- Maintains small repo size
- Still guarantees availability
- Requires infrastructure

### 2. Composer Archive

Create composer archive for deployment:

```bash
composer archive --format=zip --dir=builds/
```

Deploy the archive instead of committing vendor.

### 3. Docker Images

Build Docker images with dependencies:
- Faster deployments
- Better isolation
- No need to version vendor

### 4. Submodules (Not Recommended)

Using git submodules for vendor is generally **not recommended** due to complexity.

---

## 📚 References

- [Composer Documentation](https://getcomposer.org/doc/)
- [Git LFS Documentation](https://git-lfs.github.com/)
- [GitHub Repository Size Limits](https://docs.github.com/en/repositories/working-with-files/managing-large-files)
- [Open Source Licenses Guide](https://choosealicense.com/)

---

## ✅ Checklist

Before pushing vendor commit:

- [ ] Composer dependencies installed (`composer install`)
- [ ] Composer.lock is up to date
- [ ] Licenses reviewed and compatible
- [ ] Security audit passed (`composer audit`)
- [ ] No sensitive data in vendor/
- [ ] Size is acceptable (<500MB) or Git LFS configured
- [ ] Script executed successfully (`./scripts/commit-vendor.sh`)
- [ ] Commit reviewed (`git show HEAD --stat`)
- [ ] Team notified about repository size increase
- [ ] Documentation updated (if needed)

---

**© 2025 Support Solo Sondagens Ltda**

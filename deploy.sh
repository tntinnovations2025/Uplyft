#!/usr/bin/env bash
# ==============================================================================
# UPLYFT Production / Staging Deployment Script
# ==============================================================================
# Usage:
#   ./deploy.sh            # Deploys the default branch (main)
#   ./deploy.sh staging    # Deploys a specific branch (e.g. staging)
# ==============================================================================

set -eo pipefail

# --- Configuration -----------------------------------------------------------
BRANCH="${1:-main}"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"

# --- Terminal Colors ---------------------------------------------------------
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# --- Helper Functions --------------------------------------------------------
log_info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] ℹ️  ${1}${NC}"
}

log_success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] ✅ ${1}${NC}"
}

log_warn() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] ⚠️  ${1}${NC}"
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ❌ ${1}${NC}" >&2
}

# --- Error Trap --------------------------------------------------------------
on_error() {
    local exit_code=$?
    log_error "Deployment failed at line $1 with exit code $exit_code."
    log_warn "Attempting to bring application back online..."
    "$PHP_BIN" "$PROJECT_ROOT/artisan" up 2>/dev/null || true
    exit $exit_code
}
trap 'on_error $LINENO' ERR

# --- Start Deployment --------------------------------------------------------
cd "$PROJECT_ROOT"

echo -e "${CYAN}======================================================${NC}"
echo -e "${CYAN}  UPLYFT Automated Deployment Pipeline                ${NC}"
echo -e "${CYAN}  Target Branch: ${BRANCH}                             ${NC}"
echo -e "${CYAN}  Directory    : ${PROJECT_ROOT}                      ${NC}"
echo -e "${CYAN}======================================================${NC}"

# 1. Environment and Prerequisite Checks
log_info "Verifying prerequisites..."
if [ ! -f "$PROJECT_ROOT/.env" ]; then
    log_error ".env file missing in $PROJECT_ROOT! Aborting deployment."
    exit 1
fi

command -v "$PHP_BIN" >/dev/null 2>&1 || { log_error "PHP binary '$PHP_BIN' not found."; exit 1; }
command -v "$COMPOSER_BIN" >/dev/null 2>&1 || { log_error "Composer binary '$COMPOSER_BIN' not found."; exit 1; }
command -v "$NPM_BIN" >/dev/null 2>&1 || { log_error "NPM binary '$NPM_BIN' not found."; exit 1; }

log_success "Prerequisites verified."

# 2. Put Application into Maintenance Mode
log_info "Putting application into maintenance mode..."
"$PHP_BIN" artisan down --retry=60 --secret="uplyft-deploy-bypass" || log_warn "App was already in maintenance mode."

# 3. Pull Latest Code
log_info "Fetching and updating code from branch '${BRANCH}'..."
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"
log_success "Source code updated to latest commit."

# 4. Install / Update PHP Dependencies
log_info "Installing PHP dependencies via Composer..."
"$COMPOSER_BIN" install \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-dev
log_success "Composer dependencies installed."

# 5. Install Front-End Dependencies & Build Assets
log_info "Installing Node packages and compiling production assets..."
if [ -f "package-lock.json" ]; then
    "$NPM_BIN" ci --no-audit --prefer-offline 2>/dev/null || "$NPM_BIN" install --no-audit
else
    "$NPM_BIN" install --no-audit
fi

"$NPM_BIN" run build
log_success "Front-end assets compiled successfully."

# 6. Database Migrations
log_info "Running database migrations..."
"$PHP_BIN" artisan migrate --force
log_success "Database migrations up to date."

# 7. Ensure Storage Directories & Permissions Exist
log_info "Verifying storage and cache directories..."
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/framework/testing \
         storage/logs \
         bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
log_success "Storage and cache directory structure verified."

# 8. Optimize & Cache Configuration / Routes / Views
log_info "Clearing stale cache and optimizing application..."
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan optimize
log_success "Application cached and optimized."

# 9. Ensure Storage Symlink Exists
log_info "Checking public storage symlink..."
"$PHP_BIN" artisan storage:link 2>/dev/null || true

# 10. Restart Background Queue Workers
log_info "Restarting queue workers..."
"$PHP_BIN" artisan queue:restart 2>/dev/null || true

# 11. Reload PHP-FPM / OPcache (if running in systemd service environment)
if command -v systemctl >/dev/null 2>&1; then
    for fpm in php8.4-fpm php8.3-fpm php8.2-fpm php-fpm; do
        if systemctl is-active --quiet "$fpm" 2>/dev/null; then
            log_info "Reloading $fpm..."
            sudo -n systemctl reload "$fpm" 2>/dev/null || log_warn "Could not reload $fpm without password, skipping."
            break
        fi
    done
fi

# 12. Bring Application Back Online
log_info "Bringing application out of maintenance mode..."
"$PHP_BIN" artisan up
log_success "Application is online!"

echo -e "${GREEN}======================================================${NC}"
echo -e "${GREEN}  Deployment Completed Successfully!                  ${NC}"
echo -e "${GREEN}======================================================${NC}"

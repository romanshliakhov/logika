#!/usr/bin/env bash

set -euo pipefail

: "${DEPLOY_HOST:?DEPLOY_HOST is required}"
: "${DEPLOY_USER:?DEPLOY_USER is required}"
: "${DEPLOY_SITE_ROOT:?DEPLOY_SITE_ROOT is required}"

deploy_port="${DEPLOY_PORT:-22}"
wp_cli_bin="${WP_CLI_BIN:-wp}"
remote="$DEPLOY_USER@$DEPLOY_HOST"

ssh -p "$deploy_port" "$remote" \
  "DEPLOY_SITE_ROOT='$DEPLOY_SITE_ROOT' WP_CLI_BIN='$wp_cli_bin' bash -s" <<'REMOTE_SCRIPT'
set -euo pipefail

wp_cli() {
  read -r -a wp_cli_parts <<<"$WP_CLI_BIN"
  "${wp_cli_parts[@]}" "$@"
}

wp_cli --path="$DEPLOY_SITE_ROOT" core version
wp_cli --path="$DEPLOY_SITE_ROOT" theme is-active logika-theme
wp_cli --path="$DEPLOY_SITE_ROOT" plugin is-active logika-core
wp_cli --path="$DEPLOY_SITE_ROOT" plugin is-active logika-leads
wp_cli --path="$DEPLOY_SITE_ROOT" rest route list --format=json
REMOTE_SCRIPT

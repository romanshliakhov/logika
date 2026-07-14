<?php
/**
 * Enable ACF AI/Abilities support for local MCP workflows.
 *
 * This file is loaded automatically by WordPress before regular plugins.
 */

add_filter( 'acf/settings/enable_acf_ai', '__return_true' );

<?php
// Loaded by public review pages to get branding config from DB
// Safe to call without auth — Config is bootstrapped from DB in index.php

use App\Core\Config;

$_cfg = Config::getInstance();

$brandColor        = $_cfg->get('brand_primary_color', '#0078d4');
$brandLogoUrl      = $_cfg->get('brand_logo_url', '');
$brandLogoText     = $_cfg->get('brand_logo_text', '') ?: mb_strtoupper(substr($_cfg->get('app_name', 'M'), 0, 1));
$brandAppName      = $_cfg->get('app_name', 'M365 Tenant Tool');
$brandSupportEmail = $_cfg->get('brand_review_support_email', '');
$brandFooter       = $_cfg->get('brand_review_footer', '');

// Derive a slightly darker shade for hover
function brandDarken(string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return '#005fa3';
    [$r, $g, $b] = [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
    return sprintf('#%02x%02x%02x', max(0,$r-25), max(0,$g-25), max(0,$b-25));
}
$brandColorDark = brandDarken($brandColor);

// Determine text color on brand background (white or dark)
function brandTextColor(string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return '#ffffff';
    [$r, $g, $b] = [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
    $luminance = (0.299*$r + 0.587*$g + 0.114*$b) / 255;
    return $luminance > 0.6 ? '#1a1a1a' : '#ffffff';
}
$brandTextColor = brandTextColor($brandColor);

<?php
$name  = substr(strip_tags($_GET['name'] ?? 'Product'), 0, 20);
$id    = intval($_GET['id'] ?? 1);
$hues  = [20, 35, 200, 160, 280, 320, 50, 180];
$hue   = $hues[$id % count($hues)];
$bg    = "hsl($hue,30%,92%)";
$clr   = "hsl($hue,40%,50%)";
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="200" viewBox="0 0 300 200">';
echo '<rect width="300" height="200" fill="'.$bg.'"/>';
echo '<rect x="110" y="50" width="80" height="65" rx="8" fill="none" stroke="'.$clr.'" stroke-width="2.5"/>';
echo '<rect x="122" y="62" width="56" height="38" rx="4" fill="'.$clr.'" opacity="0.15"/>';
echo '<circle cx="150" cy="81" r="9" fill="'.$clr.'" opacity="0.35"/>';
echo '<text x="150" y="140" font-family="Arial" font-size="12" fill="'.$clr.'" text-anchor="middle" opacity="0.9">'.htmlspecialchars($name).'</text>';
echo '<text x="150" y="158" font-family="Arial" font-size="9" fill="'.$clr.'" text-anchor="middle" opacity="0.5">Replace: assets/images/products/'.$id.'.jpg</text>';
echo '</svg>';

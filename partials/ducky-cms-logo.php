<?php
/**
 * Renders the Ducky CMS logo
 *
 * Contains optional sunset gradient for duck body
 * Stroke gradient for page outline and fold
 *
 * @param array $options
 * @return string
 */

namespace DuckyCMS;

function dcms_render_ducky_logo(array $options = []): string
{
  $ducky_gradient  = $options['ducky_gradient'] ?? ['#d9803d', '#E87927', '#E8BD3B', '#00FFB2'];
  $stroke_gradient = $options['stroke_gradient'] ?? ['#d9803d', '#ff7800'];
  $width           = $options['width'] ?? null;
  $height          = $options['height'] ?? null;
  $width_attr      = $width ? "width=\"$width\"" : "";
  $height_attr     = $height ? "height=\"$height\"" : "";

  return <<<SVG
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill-rule="evenodd"
    stroke-linecap="round"
    stroke-linejoin="round"
    stroke-miterlimit="1.5"
    clip-rule="evenodd"
    viewBox="0 0 400 400"
    aria-hidden="true"
    $width_attr
    $height_attr
  >
    <defs>
      <linearGradient id="duckyGradient" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="$ducky_gradient[0]" />
        <stop offset="50%" stop-color="$ducky_gradient[1]" />
        <stop offset="80%" stop-color="$ducky_gradient[2]" />
        <stop offset="100%" stop-color="$ducky_gradient[3]" />
      </linearGradient>
      <linearGradient id="strokeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
        <stop offset="0%" stop-color="$stroke_gradient[0]" />
        <stop offset="100%" stop-color="$stroke_gradient[1]" />
      </linearGradient>
    </defs>

    <!-- Page fold -->
    <path fill="url(#strokeGradient)" d="M270 23 L340 96 H270 V23 Z"/>

    <!-- Page outline -->
    <path fill="none" stroke="url(#duckyGradient)" stroke-width="14" d="M60 209V23h210l70 73v280l-280 1v-025"/>

    <!-- Ducky body -->
    <path fill="url(#duckyGradient)"
          d="M122 204c-20-33-20-77 16-108 27-20 65-21 92 1 36 37 27 74 0 103-4 4-1 13 3 16 22 22 32 42 33 65-2 53-42 83-100 82-96-2-144-49-145-137 0-4 1-9 5-7 17 8 20 12 37 13 17 0 31-9 64-8 42 1 68 34 68 34-3-15-19-34-31-38-26-9-37-7-43-16Zm83-77a16 16 0 1 0 0 32 16 16 0 0 0 0-32ZM78 275c7 21 21 32 42 40 23 6 42 4 59-6-56 2-77-7-101-34Z"/>

    <!-- Beak -->
    <path fill="#ff7800" d="M262 142c24 6 20 17 47 22-7 17-50 25-67 34 13-17 18-29 20-47Z"/>
  </svg>
  SVG;
}
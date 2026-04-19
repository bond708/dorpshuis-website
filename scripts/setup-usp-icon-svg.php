<?php

/**
 * @file
 * Vervangt field_usp_icon (string) door een image-veld met SVG-ondersteuning.
 *
 * Gebruik: ddev drush php:script scripts/setup-usp-icon-svg.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

// ── Verwijder oude string-veld ────────────────────────────────────────────────

$old_config = FieldConfig::loadByName('paragraph', 'usp', 'field_usp_icon');
if ($old_config) {
  $old_config->delete();
  echo "FieldConfig 'field_usp_icon' (string) verwijderd.\n";
}

$old_storage = FieldStorageConfig::loadByName('paragraph', 'field_usp_icon');
if ($old_storage) {
  $old_storage->delete();
  echo "FieldStorageConfig 'field_usp_icon' verwijderd.\n";
}

// ── Nieuw image-veld aanmaken (met SVG-support via svg_image) ─────────────────

if (!FieldStorageConfig::loadByName('paragraph', 'field_usp_icon')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_usp_icon',
    'entity_type' => 'paragraph',
    'type'        => 'image',
    'cardinality' => 1,
  ])->save();
  echo "FieldStorageConfig 'field_usp_icon' (image) aangemaakt.\n";
}

if (!FieldConfig::loadByName('paragraph', 'usp', 'field_usp_icon')) {
  FieldConfig::create([
    'field_name'  => 'field_usp_icon',
    'entity_type' => 'paragraph',
    'bundle'      => 'usp',
    'label'       => 'Icoon (SVG)',
    'description' => 'Upload een SVG-icoon dat bij dit voordeel hoort. Gebruik een eenvoudig, enkelvoudig icoon op transparante achtergrond. Aanbevolen formaat: 24×24 px of 48×48 px viewBox. Het icoon wordt wit weergegeven op de donkere achtergrond.',
    'required'    => FALSE,
    'settings'    => [
      'file_extensions'    => 'svg',
      'max_filesize'       => '512 KB',
      'alt_field'          => TRUE,
      'alt_field_required' => FALSE,
      'title_field'        => FALSE,
    ],
  ])->save();
  echo "FieldConfig 'field_usp_icon' (image/SVG) aangemaakt.\n";
}

// ── Formulier-display ─────────────────────────────────────────────────────────

/** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repo */
$display_repo = \Drupal::service('entity_display.repository');

$form_display = $display_repo->getFormDisplay('paragraph', 'usp', 'default');
$form_display
  ->setComponent('field_usp_icon', [
    'type'     => 'image_image',
    'weight'   => 0,
    'settings' => [
      'preview_image_style' => 'thumbnail',
      'progress_indicator'  => 'throbber',
    ],
  ])
  ->save();
echo "Formulierdisplay opgeslagen.\n";

// ── View display: inline SVG ──────────────────────────────────────────────────

$view_display = $display_repo->getViewDisplay('paragraph', 'usp', 'default');
$view_display
  ->setComponent('field_usp_icon', [
    // 'image' = svg_image module overschrijft de core image formatter.
    // svg_render_as_image FALSE = inline <svg> i.p.v. <img>, zodat CSS de
    // fill-kleur kan aanpassen.
    'type'     => 'image',
    'weight'   => 0,
    'label'    => 'hidden',
    'settings' => [
      'image_style'         => '',
      'image_link'          => '',
      'svg_render_as_image' => FALSE,
    ],
  ])
  ->save();
echo "Weergavedisplay ingesteld op inline SVG.\n";

echo "\nKlaar! Redacteuren kunnen nu een SVG uploaden via:\n";
echo "Admin → Inhoud → Paragrafen → USP → Icoon (SVG)\n";

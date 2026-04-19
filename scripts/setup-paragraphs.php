<?php

/**
 * Setup script: paragraph types USP + Testimonial en block content types.
 * Uitvoeren via: ddev drush php:script scripts/setup-paragraphs.php
 */

use Drupal\block_content\Entity\BlockContentType;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

// ── 1. Paragraph type: USP ────────────────────────────────────────────────

if (!ParagraphsType::load('usp')) {
  ParagraphsType::create([
    'id'    => 'usp',
    'label' => 'USP',
  ])->save();
  echo "✓ Paragraph type 'usp' aangemaakt\n";
}

_ensure_paragraph_field('field_usp_icon',  'string',      'usp', 'Icoon (emoji of tekst)');
_ensure_paragraph_field('field_usp_title', 'string',      'usp', 'Titel');
_ensure_paragraph_field('field_usp_text',  'string_long', 'usp', 'Omschrijving');

_ensure_form_display('paragraph', 'usp', [
  'field_usp_icon'  => ['type' => 'string_textfield', 'weight' => 0],
  'field_usp_title' => ['type' => 'string_textfield', 'weight' => 1],
  'field_usp_text'  => ['type' => 'string_textarea',  'weight' => 2],
]);

_ensure_view_display('paragraph', 'usp', [
  'field_usp_icon'  => ['type' => 'string', 'weight' => 0, 'label' => 'hidden'],
  'field_usp_title' => ['type' => 'string', 'weight' => 1, 'label' => 'hidden'],
  'field_usp_text'  => ['type' => 'basic_string', 'weight' => 2, 'label' => 'hidden'],
]);

// ── 2. Paragraph type: Testimonial ───────────────────────────────────────

if (!ParagraphsType::load('testimonial')) {
  ParagraphsType::create([
    'id'    => 'testimonial',
    'label' => 'Referentie / Testimonial',
  ])->save();
  echo "✓ Paragraph type 'testimonial' aangemaakt\n";
}

_ensure_paragraph_field('field_quote',   'string_long', 'testimonial', 'Quote');
_ensure_paragraph_field('field_author',  'string',      'testimonial', 'Auteur');
_ensure_paragraph_field('field_company', 'string',      'testimonial', 'Bedrijf / Plaats');

_ensure_form_display('paragraph', 'testimonial', [
  'field_quote'   => ['type' => 'string_textarea',  'weight' => 0],
  'field_author'  => ['type' => 'string_textfield', 'weight' => 1],
  'field_company' => ['type' => 'string_textfield', 'weight' => 2],
]);

_ensure_view_display('paragraph', 'testimonial', [
  'field_quote'   => ['type' => 'basic_string', 'weight' => 0, 'label' => 'hidden'],
  'field_author'  => ['type' => 'string',       'weight' => 1, 'label' => 'hidden'],
  'field_company' => ['type' => 'string',       'weight' => 2, 'label' => 'hidden'],
]);

// ── 3. Block content type: USPs ───────────────────────────────────────────

if (!BlockContentType::load('usps')) {
  BlockContentType::create([
    'id'          => 'usps',
    'label'       => 'Homepage USPs',
    'description' => 'Blok met USP-items (paragraph)',
  ])->save();
  echo "✓ Block content type 'usps' aangemaakt\n";
}

_ensure_paragraph_reference_field('block_content', 'field_usps', 'usps', 'USP items', ['usp']);

_ensure_form_display('block_content', 'usps', [
  'field_usps' => ['type' => 'paragraphs', 'weight' => 1],
]);

_ensure_view_display('block_content', 'usps', [
  'field_usps' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 1, 'label' => 'hidden'],
]);

// ── 4. Block content type: Testimonials ──────────────────────────────────

if (!BlockContentType::load('testimonials')) {
  BlockContentType::create([
    'id'          => 'testimonials',
    'label'       => 'Homepage Referenties',
    'description' => 'Blok met referenties/testimonials (paragraph)',
  ])->save();
  echo "✓ Block content type 'testimonials' aangemaakt\n";
}

_ensure_paragraph_reference_field('block_content', 'field_testimonials', 'testimonials', 'Referenties', ['testimonial']);

_ensure_form_display('block_content', 'testimonials', [
  'field_testimonials' => ['type' => 'paragraphs', 'weight' => 1],
]);

_ensure_view_display('block_content', 'testimonials', [
  'field_testimonials' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 1, 'label' => 'hidden'],
]);

// ── Hulpfuncties ──────────────────────────────────────────────────────────

function _ensure_paragraph_field(string $field_name, string $type, string $bundle, string $label): void {
  if (!FieldStorageConfig::loadByName('paragraph', $field_name)) {
    FieldStorageConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'paragraph',
      'type'        => $type,
    ])->save();
  }
  if (!FieldConfig::loadByName('paragraph', $bundle, $field_name)) {
    FieldConfig::create([
      'field_name'  => $field_name,
      'entity_type' => 'paragraph',
      'bundle'      => $bundle,
      'label'       => $label,
    ])->save();
    echo "  ✓ Veld '$field_name' aangemaakt op paragraph.$bundle\n";
  }
}

function _ensure_paragraph_reference_field(string $entity_type, string $field_name, string $bundle, string $label, array $target_bundles): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name'  => $field_name,
      'entity_type' => $entity_type,
      'type'        => 'entity_reference_revisions',
      'cardinality' => -1,
      'settings'    => ['target_type' => 'paragraph'],
    ])->save();
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name'  => $field_name,
      'entity_type' => $entity_type,
      'bundle'      => $bundle,
      'label'       => $label,
      'settings'    => [
        'handler'          => 'default:paragraph',
        'handler_settings' => [
          'target_bundles'               => array_combine($target_bundles, $target_bundles),
          'target_bundles_drag_drop'     => array_fill_keys($target_bundles, ['enabled' => TRUE, 'weight' => 0]),
          'negate'                       => FALSE,
        ],
      ],
    ])->save();
    echo "  ✓ Paragraph-referentieveld '$field_name' aangemaakt op $entity_type.$bundle\n";
  }
}

function _ensure_form_display(string $entity_type, string $bundle, array $components): void {
  $display = EntityFormDisplay::load("$entity_type.$bundle.default")
    ?? EntityFormDisplay::create([
        'targetEntityType' => $entity_type,
        'bundle'           => $bundle,
        'mode'             => 'default',
        'status'           => TRUE,
      ]);

  foreach ($components as $field => $options) {
    $display->setComponent($field, $options);
  }
  $display->save();
}

function _ensure_view_display(string $entity_type, string $bundle, array $components): void {
  $display = EntityViewDisplay::load("$entity_type.$bundle.default")
    ?? EntityViewDisplay::create([
        'targetEntityType' => $entity_type,
        'bundle'           => $bundle,
        'mode'             => 'default',
        'status'           => TRUE,
      ]);

  foreach ($components as $field => $options) {
    $display->setComponent($field, $options);
  }
  $display->save();
}

echo "\n✅ Klaar! Ga naar /admin/block/add om blokken aan te maken.\n";

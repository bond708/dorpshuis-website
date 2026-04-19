<?php

/**
 * Setup: Zaal content type, paragraph types (text_block, cta_block, rooms_block)
 * en paragraph field op de standaard pagina.
 *
 * ddev drush php:script scripts/setup-content-types.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

// ── 1. Content type: Zaal ─────────────────────────────────────────────────

if (!NodeType::load('zaal')) {
  NodeType::create([
    'type'        => 'zaal',
    'name'        => 'Zaal',
    'description' => 'Een verhuurbare ruimte of zaal.',
  ])->save();
  node_add_body_field(NodeType::load('zaal'), 'Omschrijving');
  echo "✓ Content type 'zaal' aangemaakt\n";
}

_node_field('field_zaal_image',    'image',   'zaal', 'Afbeelding',    ['cardinality' => 1]);
_node_field('field_zaal_teaser',   'string_long', 'zaal', 'Korte omschrijving (teaser)', ['cardinality' => 1]);
_node_field('field_zaal_capacity', 'integer', 'zaal', 'Max. personen', ['cardinality' => 1]);

// Teaser view mode voor zaal
_ensure_view_display('node', 'zaal', [
  'field_zaal_image'    => ['type' => 'image',       'weight' => 0, 'label' => 'hidden', 'settings' => ['image_style' => 'medium', 'image_link' => 'content']],
  'field_zaal_teaser'   => ['type' => 'basic_string', 'weight' => 1, 'label' => 'hidden'],
  'field_zaal_capacity' => ['type' => 'number_integer', 'weight' => 2, 'label' => 'inline'],
], 'teaser');

echo "✓ Zaal view modes geconfigureerd\n";

// ── 2. Paragraph type: Tekstblok (WYSIWYG) ───────────────────────────────

if (!ParagraphsType::load('text_block')) {
  ParagraphsType::create([
    'id'          => 'text_block',
    'label'       => 'Tekstblok',
    'description' => 'Vrije tekst met opmaak (WYSIWYG).',
    'icon_uuid'   => NULL,
  ])->save();
  echo "✓ Paragraph type 'text_block' aangemaakt\n";
}

_para_field('field_body', 'text_long', 'text_block', 'Inhoud');

_ensure_form_display('paragraph', 'text_block', [
  'field_body' => ['type' => 'text_textarea', 'weight' => 0, 'settings' => ['rows' => 10]],
]);
_ensure_view_display('paragraph', 'text_block', [
  'field_body' => ['type' => 'text_default', 'weight' => 0, 'label' => 'hidden'],
]);

// ── 3. Paragraph type: CTA item (herbruikbaar) ────────────────────────────

if (!ParagraphsType::load('cta_item')) {
  ParagraphsType::create([
    'id'    => 'cta_item',
    'label' => 'CTA item',
  ])->save();
  echo "✓ Paragraph type 'cta_item' aangemaakt\n";
}

_para_field('field_cta_title',  'string',      'cta_item', 'Titel');
_para_field('field_cta_text',   'string_long', 'cta_item', 'Tekst');
_para_field('field_cta_url',    'link',        'cta_item', 'Link (URL)');
_para_field('field_cta_label',  'string',      'cta_item', 'Knoplabel');

_ensure_form_display('paragraph', 'cta_item', [
  'field_cta_title' => ['type' => 'string_textfield', 'weight' => 0],
  'field_cta_text'  => ['type' => 'string_textarea',  'weight' => 1],
  'field_cta_url'   => ['type' => 'link_default',     'weight' => 2],
  'field_cta_label' => ['type' => 'string_textfield', 'weight' => 3],
]);
_ensure_view_display('paragraph', 'cta_item', [
  'field_cta_title' => ['type' => 'string',       'weight' => 0, 'label' => 'hidden'],
  'field_cta_text'  => ['type' => 'basic_string', 'weight' => 1, 'label' => 'hidden'],
  'field_cta_url'   => ['type' => 'link',         'weight' => 2, 'label' => 'hidden'],
  'field_cta_label' => ['type' => 'string',       'weight' => 3, 'label' => 'hidden'],
]);

// ── 4. Paragraph type: CTA blok (3 CTA items) ────────────────────────────

if (!ParagraphsType::load('cta_block')) {
  ParagraphsType::create([
    'id'    => 'cta_block',
    'label' => 'CTA blok (3 kolommen)',
  ])->save();
  echo "✓ Paragraph type 'cta_block' aangemaakt\n";
}

_para_field('field_cta_block_title', 'string', 'cta_block', 'Sectietitel (optioneel)');
_para_paragraph_ref('field_cta_items', 'cta_block', 'CTA items', ['cta_item'], 3);

_ensure_form_display('paragraph', 'cta_block', [
  'field_cta_block_title' => ['type' => 'string_textfield', 'weight' => 0],
  'field_cta_items'       => ['type' => 'paragraphs',       'weight' => 1],
]);
_ensure_view_display('paragraph', 'cta_block', [
  'field_cta_block_title' => ['type' => 'string',                                  'weight' => 0, 'label' => 'hidden'],
  'field_cta_items'       => ['type' => 'entity_reference_revisions_entity_view',  'weight' => 1, 'label' => 'hidden'],
]);

// ── 5. Paragraph type: Zalenblok (3 zaal-nodes als teaser) ───────────────

if (!ParagraphsType::load('rooms_block')) {
  ParagraphsType::create([
    'id'    => 'rooms_block',
    'label' => 'Zalenblok (3 zalen)',
  ])->save();
  echo "✓ Paragraph type 'rooms_block' aangemaakt\n";
}

_para_field('field_rooms_title', 'string', 'rooms_block', 'Sectietitel (optioneel)');

if (!FieldStorageConfig::loadByName('paragraph', 'field_rooms')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_rooms',
    'entity_type' => 'paragraph',
    'type'        => 'entity_reference',
    'cardinality' => 3,
    'settings'    => ['target_type' => 'node'],
  ])->save();
}
if (!FieldConfig::loadByName('paragraph', 'rooms_block', 'field_rooms')) {
  FieldConfig::create([
    'field_name'  => 'field_rooms',
    'entity_type' => 'paragraph',
    'bundle'      => 'rooms_block',
    'label'       => 'Zalen (max. 3)',
    'settings'    => [
      'handler'          => 'default:node',
      'handler_settings' => [
        'target_bundles' => ['zaal' => 'zaal'],
        'sort'           => ['field' => '_none'],
        'auto_create'    => FALSE,
      ],
    ],
  ])->save();
  echo "  ✓ Veld 'field_rooms' aangemaakt op paragraph.rooms_block\n";
}

_ensure_form_display('paragraph', 'rooms_block', [
  'field_rooms_title' => ['type' => 'string_textfield',        'weight' => 0],
  'field_rooms'       => ['type' => 'entity_reference_autocomplete', 'weight' => 1],
]);
_ensure_view_display('paragraph', 'rooms_block', [
  'field_rooms_title' => ['type' => 'string',                   'weight' => 0, 'label' => 'hidden'],
  'field_rooms'       => ['type' => 'entity_reference_entity_view', 'weight' => 1, 'label' => 'hidden',
    'settings' => ['view_mode' => 'teaser']],
]);

// ── 6. Paragraphs field op content type 'page' ───────────────────────────

$allowed = ['text_block' => 'text_block', 'cta_block' => 'cta_block', 'rooms_block' => 'rooms_block'];

if (!FieldStorageConfig::loadByName('node', 'field_content_blocks')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_content_blocks',
    'entity_type' => 'node',
    'type'        => 'entity_reference_revisions',
    'cardinality' => -1,
    'settings'    => ['target_type' => 'paragraph'],
  ])->save();
}
if (!FieldConfig::loadByName('node', 'page', 'field_content_blocks')) {
  FieldConfig::create([
    'field_name'  => 'field_content_blocks',
    'entity_type' => 'node',
    'bundle'      => 'page',
    'label'       => 'Inhoudsblokken',
    'settings'    => [
      'handler'          => 'default:paragraph',
      'handler_settings' => [
        'target_bundles'           => $allowed,
        'target_bundles_drag_drop' => array_map(fn($i) => ['enabled' => TRUE, 'weight' => $i], array_flip(array_keys($allowed))),
        'negate'                   => FALSE,
      ],
    ],
  ])->save();
  echo "✓ Veld 'field_content_blocks' (paragraphs) toegevoegd aan node.page\n";
}

_ensure_form_display('node', 'page', [
  'field_content_blocks' => ['type' => 'paragraphs', 'weight' => 10,
    'settings' => ['title' => 'Blok', 'title_plural' => 'Blokken', 'edit_mode' => 'open', 'add_mode' => 'dropdown']],
]);
_ensure_view_display('node', 'page', [
  'field_content_blocks' => ['type' => 'entity_reference_revisions_entity_view', 'weight' => 10, 'label' => 'hidden'],
]);

echo "\n✅ Klaar!\n";

// ── Hulpfuncties ──────────────────────────────────────────────────────────

function _para_field(string $name, string $type, string $bundle, string $label, int $cardinality = 1): void {
  if (!FieldStorageConfig::loadByName('paragraph', $name)) {
    FieldStorageConfig::create([
      'field_name'  => $name,
      'entity_type' => 'paragraph',
      'type'        => $type,
      'cardinality' => $cardinality,
    ])->save();
  }
  if (!FieldConfig::loadByName('paragraph', $bundle, $name)) {
    FieldConfig::create([
      'field_name'  => $name,
      'entity_type' => 'paragraph',
      'bundle'      => $bundle,
      'label'       => $label,
    ])->save();
    echo "  ✓ $name op paragraph.$bundle\n";
  }
}

function _node_field(string $name, string $type, string $bundle, string $label, array $extra = []): void {
  if (!FieldStorageConfig::loadByName('node', $name)) {
    FieldStorageConfig::create(array_merge([
      'field_name'  => $name,
      'entity_type' => 'node',
      'type'        => $type,
    ], $extra))->save();
  }
  if (!FieldConfig::loadByName('node', $bundle, $name)) {
    FieldConfig::create([
      'field_name'  => $name,
      'entity_type' => 'node',
      'bundle'      => $bundle,
      'label'       => $label,
    ])->save();
    echo "  ✓ $name op node.$bundle\n";
  }
}

function _para_paragraph_ref(string $name, string $bundle, string $label, array $targets, int $cardinality = -1): void {
  if (!FieldStorageConfig::loadByName('paragraph', $name)) {
    FieldStorageConfig::create([
      'field_name'  => $name,
      'entity_type' => 'paragraph',
      'type'        => 'entity_reference_revisions',
      'cardinality' => $cardinality,
      'settings'    => ['target_type' => 'paragraph'],
    ])->save();
  }
  if (!FieldConfig::loadByName('paragraph', $bundle, $name)) {
    FieldConfig::create([
      'field_name'  => $name,
      'entity_type' => 'paragraph',
      'bundle'      => $bundle,
      'label'       => $label,
      'settings'    => [
        'handler'          => 'default:paragraph',
        'handler_settings' => ['target_bundles' => array_combine($targets, $targets)],
      ],
    ])->save();
    echo "  ✓ $name (ERR) op paragraph.$bundle\n";
  }
}

function _ensure_form_display(string $et, string $bundle, array $components): void {
  $d = EntityFormDisplay::load("$et.$bundle.default")
    ?? EntityFormDisplay::create(['targetEntityType' => $et, 'bundle' => $bundle, 'mode' => 'default', 'status' => TRUE]);
  foreach ($components as $field => $opts) { $d->setComponent($field, $opts); }
  $d->save();
}

function _ensure_view_display(string $et, string $bundle, array $components, string $mode = 'default'): void {
  $d = EntityViewDisplay::load("$et.$bundle.$mode")
    ?? EntityViewDisplay::create(['targetEntityType' => $et, 'bundle' => $bundle, 'mode' => $mode, 'status' => TRUE]);
  foreach ($components as $field => $opts) { $d->setComponent($field, $opts); }
  $d->save();
}

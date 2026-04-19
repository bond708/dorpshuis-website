<?php

/**
 * @file
 * Maakt block content type 'hero_blok' aan met velden voor de homepage-hero.
 *
 * Gebruik: ddev drush php:script scripts/setup-hero-block.php
 */

use Drupal\block_content\Entity\BlockContentType;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

// ── Block content type ────────────────────────────────────────────────────────

if (!BlockContentType::load('hero_blok')) {
  BlockContentType::create([
    'id'          => 'hero_blok',
    'label'       => 'Homepage Hero',
    'description' => 'Inhoud van de hero-sectie bovenaan de homepage: afbeelding, titel, ondertitel en knoppen.',
    'revision'    => TRUE,
  ])->save();
  echo "Block content type 'hero_blok' aangemaakt.\n";
}
else {
  echo "Block content type 'hero_blok' bestaat al.\n";
}

// ── Veld: Afbeelding ─────────────────────────────────────────────────────────

if (!FieldStorageConfig::loadByName('block_content', 'field_hero_afbeelding')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_hero_afbeelding',
    'entity_type' => 'block_content',
    'type'        => 'image',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'hero_blok', 'field_hero_afbeelding')) {
  FieldConfig::create([
    'field_name'   => 'field_hero_afbeelding',
    'entity_type'  => 'block_content',
    'bundle'       => 'hero_blok',
    'label'        => 'Hero-afbeelding',
    'description'  => 'Upload hier de foto die rechts in de hero-sectie verschijnt. Aanbevolen formaat: minimaal 1200 × 900 px, liggende oriëntatie (landscape). De afbeelding wordt bijgesneden om de sectie te vullen.',
    'required'     => FALSE,
    'settings'     => [
      'file_extensions' => 'jpg jpeg png webp',
      'max_filesize'    => '5 MB',
      'alt_field'       => TRUE,
      'alt_field_required' => TRUE,
      'title_field'     => FALSE,
    ],
  ])->save();
  echo "Veld 'field_hero_afbeelding' aangemaakt.\n";
}

// ── Veld: Titel ──────────────────────────────────────────────────────────────

if (!FieldStorageConfig::loadByName('block_content', 'field_hero_titel')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_hero_titel',
    'entity_type' => 'block_content',
    'type'        => 'string',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'hero_blok', 'field_hero_titel')) {
  FieldConfig::create([
    'field_name'  => 'field_hero_titel',
    'entity_type' => 'block_content',
    'bundle'      => 'hero_blok',
    'label'       => 'Hoofdtitel',
    'description' => 'De grote koptekst in de hero, bijvoorbeeld: "Dorpshuis De Eersteling". Wordt vetgedrukt en in hoofdletters weergegeven.',
    'required'    => TRUE,
    'settings'    => ['max_length' => 100],
  ])->save();
  echo "Veld 'field_hero_titel' aangemaakt.\n";
}

// ── Veld: Ondertitel ─────────────────────────────────────────────────────────

if (!FieldStorageConfig::loadByName('block_content', 'field_hero_ondertitel')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_hero_ondertitel',
    'entity_type' => 'block_content',
    'type'        => 'string_long',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'hero_blok', 'field_hero_ondertitel')) {
  FieldConfig::create([
    'field_name'  => 'field_hero_ondertitel',
    'entity_type' => 'block_content',
    'bundle'      => 'hero_blok',
    'label'       => 'Ondertitel / introductietekst',
    'description' => 'Korte omschrijving onder de hoofdtitel, maximaal 2 zinnen. Optioneel.',
    'required'    => FALSE,
  ])->save();
  echo "Veld 'field_hero_ondertitel' aangemaakt.\n";
}

// ── Veld: CTA primair ────────────────────────────────────────────────────────

if (!FieldStorageConfig::loadByName('block_content', 'field_hero_cta_primair')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_hero_cta_primair',
    'entity_type' => 'block_content',
    'type'        => 'link',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'hero_blok', 'field_hero_cta_primair')) {
  FieldConfig::create([
    'field_name'  => 'field_hero_cta_primair',
    'entity_type' => 'block_content',
    'bundle'      => 'hero_blok',
    'label'       => 'Knop 1 (goud / primair)',
    'description' => 'Goudkleurige actieknop, bijvoorbeeld: tekst "Bekijk onze zalen" → link "/zalen". Optioneel.',
    'required'    => FALSE,
    'settings'    => ['link_type' => 0x10, 'title' => 2],
  ])->save();
  echo "Veld 'field_hero_cta_primair' aangemaakt.\n";
}

// ── Veld: CTA secundair ──────────────────────────────────────────────────────

if (!FieldStorageConfig::loadByName('block_content', 'field_hero_cta_secundair')) {
  FieldStorageConfig::create([
    'field_name'  => 'field_hero_cta_secundair',
    'entity_type' => 'block_content',
    'type'        => 'link',
    'cardinality' => 1,
  ])->save();
}

if (!FieldConfig::loadByName('block_content', 'hero_blok', 'field_hero_cta_secundair')) {
  FieldConfig::create([
    'field_name'  => 'field_hero_cta_secundair',
    'entity_type' => 'block_content',
    'bundle'      => 'hero_blok',
    'label'       => 'Knop 2 (wit omlijnd / secundair)',
    'description' => 'Witte omlijningknop, bijvoorbeeld: tekst "Direct reserveren" → link "/contact". Optioneel.',
    'required'    => FALSE,
    'settings'    => ['link_type' => 0x10, 'title' => 2],
  ])->save();
  echo "Veld 'field_hero_cta_secundair' aangemaakt.\n";
}

// ── Formulier-display ────────────────────────────────────────────────────────

/** @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repo */
$display_repo = \Drupal::service('entity_display.repository');

$form_display = $display_repo->getFormDisplay('block_content', 'hero_blok', 'default');
$form_display
  ->setComponent('field_hero_afbeelding', [
    'type'     => 'image_image',
    'weight'   => 0,
    'settings' => ['preview_image_style' => 'thumbnail', 'progress_indicator' => 'throbber'],
  ])
  ->setComponent('field_hero_titel', [
    'type'   => 'string_textfield',
    'weight' => 1,
    'settings' => ['size' => 60],
  ])
  ->setComponent('field_hero_ondertitel', [
    'type'   => 'string_textarea',
    'weight' => 2,
    'settings' => ['rows' => 3],
  ])
  ->setComponent('field_hero_cta_primair', [
    'type'   => 'link_default',
    'weight' => 3,
  ])
  ->setComponent('field_hero_cta_secundair', [
    'type'   => 'link_default',
    'weight' => 4,
  ])
  ->save();

echo "Formulierdisplay opgeslagen.\n";
echo "\nKlaar! Ga nu naar Admin → Structuur → Blokinhoud → Toevoegen → Homepage Hero\n";
echo "en plaats het blok in regio 'Homepage: Hero' via Admin → Structuur → Blokopmaak.\n";

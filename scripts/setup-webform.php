<?php

/**
 * Maakt het contactformulier aan via de Webform API.
 * ddev drush php:script scripts/setup-webform.php
 */

use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformOptions;

$webform_id = 'contact';

if (Webform::load($webform_id)) {
  echo "Webform '$webform_id' bestaat al — overgeslagen.\n";
  return;
}

$webform = Webform::create([
  'id'     => $webform_id,
  'title'  => 'Contactformulier',
  'status' => 'open',
  'langcode' => 'nl',
]);

$webform->setElements([
  'naam' => [
    '#type'     => 'textfield',
    '#title'    => 'Naam',
    '#required' => TRUE,
  ],
  'email' => [
    '#type'     => 'email',
    '#title'    => 'E-mailadres',
    '#required' => TRUE,
  ],
  'datum' => [
    '#type'  => 'date',
    '#title' => 'Gewenste datum',
  ],
  'soort_evenement' => [
    '#type'    => 'select',
    '#title'   => 'Soort evenement',
    '#options' => [
      'vergadering'  => 'Vergadering / bijeenkomst',
      'feest'        => 'Feest / receptie',
      'bruiloft'     => 'Bruiloft',
      'training'     => 'Training / cursus',
      'tentoonstelling' => 'Tentoonstelling',
      'anders'       => 'Anders',
    ],
    '#empty_option' => '— Kies een type —',
  ],
  'bericht' => [
    '#type'  => 'textarea',
    '#title' => 'Bericht',
    '#rows'  => 5,
  ],
  'submit' => [
    '#type'  => 'webform_actions',
    '#title' => 'Versturen',
    '#submit__label' => 'Verstuur aanvraag',
  ],
]);

// Bevestigingsmail instellen
$webform->setSetting('confirmation_type', 'message');
$webform->setSetting('confirmation_message', '<p>Bedankt voor uw bericht! We nemen zo snel mogelijk contact met u op.</p>');

// Notificatiemail naar beheerder
$handlers = $webform->getHandlers();

$webform->save();
echo "✓ Webform 'contact' aangemaakt\n";
echo "  → Ga naar /admin/structure/webform/manage/contact/handlers om e-mailnotificaties in te stellen.\n";
echo "  → Blok: /admin/structure/webform/manage/contact/settings/access\n";

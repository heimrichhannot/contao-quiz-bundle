<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_quiz'];


/**
 * Fields
 */
$lang['tstamp'][0] = 'Änderungsdatum';

// general
$lang['title'][0]                       = 'Titel';
$lang['title'][1]                       = 'Geben Sie hier bitte den Titel ein.';
$lang['published']                      = ['Veröffentlichen', 'Wählen Sie diese Option zum Veröffentlichen.'];
$lang['start']                          = ['Anzeigen ab', 'Quiz erst ab diesem Tag auf der Webseite anzeigen.'];
$lang['stop']                           = ['Anzeigen bis', 'Quiz nur bis zu diesem Tag auf der Webseite anzeigen.'];
$lang['addSubmission']                  = ['Einsendung hinzufügen', ''];
$lang['submissionArchive']              = ['Archiv auswählen', 'Wählen Sie ein Submission-Archiv aus.'];
$lang['formHybridResetAfterSubmission'] = ['Formular nach dem Abschicken zurücksetzen', 'Deaktivieren um nach Absenden, das Formular mit den Daten erneut zu laden. (Achtung: Nur einmaliges Absenden möglich!)'];
$lang['formHybridSingleSubmission']     = ['Formular nur einmal erzeugen', 'Nachdem das Formular erfolgreich abgeschickt wurde, wird keine neue Entität erzeugt und nur Meldungen werden ausgegeben.'];

/**
 * Legends
 */
$lang['general_legend']    = 'Allgemeine Einstellungen';
$lang['publish_legend']    = 'Veröffentlichung';
$lang['submission_legend'] = 'Einsendung Einstellungen';

/**
 * Buttons
 */
$lang['new']        = ['Neues Quiz', 'Quiz erstellen'];
$lang['edit']       = ['Fragen hinzufügen', 'Fragen zum Quiz ID %s hinzufügen'];
$lang['editheader'] = ['Quiz-Einstellungen bearbeiten', 'Quiz-Einstellungen ID %s bearbeiten'];
$lang['quizScore']  = ['Auswertung hinzufügen', 'Auswertung zum Quiz ID %s hinzufügen'];
$lang['copy']       = ['Quiz duplizieren', 'Quiz ID %s duplizieren'];
$lang['delete']     = ['Quiz löschen', 'Quiz ID %s löschen'];
$lang['toggle']     = ['Quiz veröffentlichen', 'Quiz ID %s veröffentlichen/verstecken'];
$lang['show']       = ['Quiz Details', 'Quiz-Details ID %s anzeigen'];
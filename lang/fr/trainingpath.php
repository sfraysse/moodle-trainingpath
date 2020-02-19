<?php

/* * *************************************************************
 *  This script has been developed for Moodle - http://moodle.org/
 *
 *  You can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
  *
 * ************************************************************* */


// ------------------------- Standard plugin terms ---------------------------

// Plugin
$string['pluginadministration'] = 'Administration du module Parcours Formation';
$string['pluginname'] = 'Parcours Formation';

// Module
$string['modulename'] = 'Parcours Formation';
$string['modulename_help'] = "Parcours Formation permet de définir, de gérer, et de contrôler des parcours de formation linéaires prédéfinis.";
$string['modulename_link'] = 'mod/trainingpath/view';
$string['modulenameplural'] = 'Parcours Formation';

// Permissions
$string['trainingpath:addinstance'] = 'Définir un parcours de formation';
$string['trainingpath:editschedule'] = 'Editer un planning de formation';


// ------------------------- Settings ---------------------------

$string['prefered_activity_access_eval'] = 'Accès aux activités (évaluations)';
$string['prefered_activity_access_eval_desc'] = "Valeur par défaut de l'accès aux évaluations dans les réglages du planning.";
$string['prefered_activity_access'] = 'Accès aux activités (autres)';
$string['prefered_activity_access_desc'] = "Valeur par défaut de l'accès aux activités autres que test dans les réglages du planning.";


// ------------------------- Common terms ---------------------------

$string['home'] = 'Accueil';
$string['gradebook'] = 'Carnet de notes';
$string['path'] = 'Parcours de formation';
$string['certificate'] = 'Thème';
$string['batch'] = 'Phase';
$string['sequence'] = 'Séquence';
$string['activity'] = 'Activité';
$string['eval'] = 'Test';
$string['eval_comp'] = 'Test complémentaire';
$string['virtual'] = 'Classe virtuelle';
$string['classroom'] = 'Classe en présentiel';
$string['files'] = 'Fichiers';
$string['richtext'] = 'Texte riche';
$string['schedule'] = 'Planning';
$string['certificates'] = 'Thèmes';
$string['batches'] = 'Phases';
$string['sequences'] = 'Séquences';
$string['activities'] = 'Activités';
$string['schedules'] = 'Plannings';
$string['settings'] = 'Paramètres';
$string['general'] = 'Paramètres généraux';
$string['other_settings'] = 'Autres paramètres';
$string['title'] = 'Titre';
$string['code'] = 'Code';
$string['description'] = 'Description';
$string['context'] = 'Contexte';
$string['content'] = 'Contenu';

// ------------------------- Editing ---------------------------

$string['add'] = 'Ajouter';
$string['add_schedule'] = 'Ajouter un planning';
$string['add_certificate'] = 'Ajouter un thème';
$string['add_batch'] = 'Ajouter une phase';
$string['add_sequence'] = 'Ajouter une séquence';
$string['add_formal_activity'] = 'Ajouter une activité obligatoire';
$string['add_complementary_activity'] = 'Ajouter une activité complémentaire';

$string['delete'] = 'Supprimer';
$string['delete_schedule'] = 'Supprimer le planning';
$string['delete_schedule_confirm'] = 'Voulez-vous vraiment supprimer ce planning ?';
$string['delete_certificate'] = 'Supprimer le thème';
$string['delete_certificate_confirm'] = 'Voulez-vous vraiment supprimer ce thème ?';
$string['delete_batch'] = 'Supprimer la phase';
$string['delete_batch_confirm'] = 'Voulez-vous vraiment supprimer cette phase ? Toutes les séquences et activités associées seront aussi supprimées !';
$string['delete_sequence'] = 'Supprimer la séquence';
$string['delete_sequence_confirm'] = 'Voulez-vous vraiment supprimer cette séquence ? Toutes les activités associées seront aussi supprimées !';
$string['delete_activity'] = "Supprimer l'activité'";
$string['delete_activity_confirm'] = 'Voulez-vous vraiment supprimer cette activité ?';

$string['edit'] = 'Editer';
$string['edit_certificates'] = 'Editer les thèmes';
$string['edit_sequences'] = 'Editer les séquences';
$string['edit_batches'] = 'Editer les phases';
$string['edit_activities'] = 'Editer les activités';
$string['editing_schedule'] = 'Edition du planning';
$string['editing_certificate'] = 'Edition du thème';
$string['editing_certificates'] = 'Edition des thèmes';
$string['editing_batch'] = 'Edition de la phase';
$string['editing_batches'] = 'Edition des phases';
$string['editing_sequence'] = 'Edition de la séquence';
$string['editing_sequences'] = 'Edition des séquences';
$string['editing_activity'] = "Edition de l'activité";
$string['editing_activities'] = 'Edition des activités';
$string['editing_content'] = 'Edition du contenu';
$string['editing_eval'] = 'Edition du test';
$string['editing_virtual'] = 'Edition de la classe virtuelle';
$string['editing_classroom'] = 'Edition de la classe présentielle';
$string['editing_files'] = 'Edition des fichiers';
$string['editing_richtext'] = 'Edition du texte riche';

$string['new_schedule'] = 'Nouveau planning';
$string['new_certificate'] = 'Nouveau thème';
$string['new_batch'] = 'Nouvelle phase';
$string['new_sequence'] = 'Nouvelle séquence';
$string['new_content'] = 'Nouveau contenu';
$string['new_eval'] = 'Nouveau test';
$string['new_virtual'] = 'Nouvelle classe virtuelle';
$string['new_classroom'] = 'Nouvelle classe présentielle';
$string['new_files'] = 'Nouveaux fichiers';
$string['new_richtext'] = 'Nouveau texte riche';

$string['no_certificate'] = "Il n'y a actuellement aucun thème.";
$string['no_schedule'] = "Il n'y a actuellement aucun planning.";
$string['no_batch'] = "Il n'y a actuellement aucune phase.";
$string['no_sequence'] = "Il n'y a actuellement aucune séquence.";
$string['no_activity'] = "Il n'y a actuellement aucune activité.";
$string['no_matching_group'] = 'Aucun groupe correspondant';
$string['no_description'] = 'Aucune description';
$string['no_data'] = "Il n'y a actuellement aucune donnée.";
$string['none'] = 'Aucun';

$string['preview'] = 'Prévisualiser';
$string['preview_certificates'] = 'Prévisualiser les thèmes';
$string['preview_batches'] = 'Prévisualiser les phases';
$string['preview_sequences'] = 'Prévisualiser les séquences';
$string['preview_activities'] = 'Prévisualiser les activités';

$string['tracks_recalculate'] = 'Recalculer les données de suivi';
$string['tracks_recalculate_confirm'] = 'Les données de suivi ont été recalculées.';
$string['tracks_recalculate_desc'] = '
    En cliquant sur le bouton ci-dessous, la progression de tous les apprenants, à tous les niveaux du parcours,
    va être recalculée. Ceci peut être utile après avoir modifié le parcours.
';
$string['tracks_recalculate_back'] = 'Retour au parcours';

$string['save'] = 'Enregistrer';
$string['cancel'] = 'Annuler';
$string['confirm'] = 'Confirmer';

// Regulatory
$string['regulatory_rules'] = 'Contraintes de temps';
$string['certificate_duration'] = "Temps d'étude minimum (en heures)";
$string['certificate_duration_help'] = "Temps minimum qu'un apprenant doit passer sur le thème. Le thème ne pourra être validé tant que l'apprenant n'aura pas atteint ce quota.";
$string['certificate_duration_error'] = 'Ce champ doit être une valeur numérique positive. Le caractère "." est utilisé pour les décimales.';
$string['sequence_duration'] = 'Durée de la séquence (en jours)';
$string['sequence_duration_help'] = 'Durée à prendre en compte pour la génération du planning. Ce doit être un multiple de 0.5 jours.';
$string['sequence_duration_error'] = 'Ce champ doit être une valeur numérique positive. Le caractère "." est utilisé pour les décimales. Jours entiers ou demi-journées uniquement.';
$string['content_duration'] = "Temps d'étude minimum (en minutes)";
$string['content_duration_help'] = "Temps minimum qu'un apprenant doit passer sur l'activité. L'activité ne peut être validée en dessous de ce temps.";
$string['content_duration_error'] = 'Ce champ doit être une valeur numérique entière, positive et non nulle.';
$string['eval_duration'] = 'Temps alloué (en minutes)';
$string['eval_duration_help'] = "Temps alloué pour passer le test. Le temps doit être exprimé en minutes (ex. 60 pour un temps maximum d'une heure). 0 signifie qu'il n'y a pas de limite de temps.";
$string['eval_duration_error'] = 'Ce champ doit être une valeur numérique entière positive.';
$string['virtual_duration'] = 'Durée (en minutes)';
$string['virtual_duration_help'] = "Durée programmée de la session de classe virtuelle.";
$string['virtual_duration_error'] = 'Ce champ doit être une valeur numérique entière, positive et non nulle.';
$string['classroom_duration'] = 'Durée (en minutes)';
$string['classroom_duration_help'] = "Durée programmée de la session de classe présentielle.";
$string['classroom_duration_error'] = 'Ce champ doit être une valeur numérique entière, positive et non nulle.';
$string['session_duration_'] = 'Durée effectivement réalisée (defaut: {$a} minutes)';
$string['session_duration'] = 'Durée effectivement réalisée (minutes)';
$string['session_duration_error'] = 'Ce champ doit être une valeur numérique entière, positive et non nulle.';

// Colors
$string['colors'] = 'Couleurs de reporting';
$string['score_colors'] = 'Couleurs de reporting pour les scores';
$string['score_colors_desc'] = 'Couleurs à appliquer pour l\'affichage des scores dans les rapports. Chaque valeur indique le score au dessous duquel la couleur s\'applique.';
$string['score_colors_help'] = $string['score_colors_desc'];
$string['score_lessthan'] = 'Score <';
$string['score_upto'] = 'Score <=';

// Time
$string['time_colors'] = "Couleurs de reporting pour les temps d'étude";
$string['time_colors_desc'] = "Couleurs à appliquer pour l'affichage dans les rapports du temps effectivement passé en apprentissage.";
$string['time_colors_help'] = $string['time_colors_desc'];
$string['time_settings'] = "Paramètres de suivi du temps d'étude";
$string['time_optimum_threshold'] = 'Seuil de tolérance au-delà du temps nominal calculé (%)';
$string['time_optimum_threshold_help'] = "Ce paramètre (Seuil %) définit un seuil pour le code couleur d'affichage en fonction du temps d'étude T effectivement passé sur la séquence :
    <br>- Orange si T < [temps nominal]
    <br>- Jaune si [temps nominal] < T < [temps nominal] x Seuil %
    <br>- Vert si T > [temps nominal] x Seuil %";
$string['time_max_factor'] = 'Facteur de temps maximum';
$string['time_max_factor_help'] = "Définit un coefficient pour calculer la durée maximale comptabilisable pour une activité donnée (facteur commun à toutes les activités du parcours) :
    <br>[Temps max] = [Temps d'étude mini] x [Facteur de temps max]";

// Other settings
$string['file_extensions'] = 'Extensions de fichiers';
$string['file_extensions_desc'] = 'Extensions de fichiers autorisées dans les activités Fichiers';
$string['file_extensions_help'] = $string['file_extensions_desc'];
$string['passing_score'] = 'Seuil de réussite';
$string['passing_score_desc'] = 'Score minimum à obtenir pour réussir le test.';
$string['passing_score_help'] = $string['passing_score_desc'];
$string['remedial'] = 'Test de remédiation';
$string['complementary_l'] = 'complémentaire';
$string['formal_l'] = 'obligatoire';
$string['practical_information'] = 'Informations pratiques';
$string['launch_file'] = 'Fichier de lancement';
$string['group'] = 'Groupe';
$string['of_group'] = 'du groupe';
$string['morning'] = 'Matin';
$string['afternoon'] = 'Après-midi';
$string['morning_l'] = 'matin';
$string['afternoon_l'] = 'après-midi';
$string['locked'] = 'Modification verrouillé';

// Form errors
$string['score_color_notvalid'] = 'Vous devez entrer une valeur entre 0 et 100.';
$string['time_optimum_threshold_notvalid'] = 'Vous devez entrer une valeur entre 0 et 100.';
$string['time_max_factor_notvalid'] = 'Vous devez entrer une valeur entre 1 et 10.';
$string['error_files_missing_launch'] = 'Vous devez préciser un fichier de lancement.';
$string['error_files_invalid_package'] = "Le package n'est pas valide.";
$string['error_files_invalid_launch'] = "Le fichier de lancement que vous avez précisé n'existe pas dans le package fourni.";
$string['error_files_invalid_extension'] = "Cette extension de fichier n'est pas autorisée.";

// Permission errors
$string['permission_denied_edit_schedule'] = "Vous n'êtes pas autorisé à éditer les plannings.";
$string['permission_denied_edit_schedule_no_group'] = "Vous ne gérez aucun groupe d'apprenants. Vous devez être inscrit à un groupe pour pouvoir gérer son planning.";
$string['permission_denied_view_no_group'] = "Vous n'êtes actuellement inscrit à aucun groupe d'apprenants. Veuillez réessayer plus tard ou contacter votre superviseur.";
$string['permission_denied_view_no_schedule'] = "Il n'y a actuellement aucun planning défini pour votre groupe. Veuillez réessayer plus tard ou contacter votre superviseur.";
$string['permission_denied_view_hidden'] = "Vous n'êtes actuellement pas autorisé à voir ceci. Veuillez réessayer plus tard ou contacter votre superviseur.";
$string['permission_denied_tutor_group_not_allowed'] = "Vous n'êtes pas autorisé à superviser ce groupe.";
$string['permission_denied_calendar_not_defined'] = "Aucun calendrier n'a été défini.";


// ------------------------- Scheduling ---------------------------

$string['scheduling'] = 'Planification';
$string['scheduling_'] = 'Planification: {$a}';
$string['schedule_already_assigned'] = 'Un planning a déjà été assigné à ce groupe.';
$string['schedule_certificates'] = 'Planifier les thèmes';
$string['schedule_batches'] = 'Planifier les phases';
$string['schedule_sequences'] = 'Planifier les sequences';
$string['schedule_activities'] = 'Planifier les activités';
$string['scheduling_certificates'] = 'Planification des thèmes';
$string['scheduling_batches'] = 'Planification des phases';
$string['scheduling_sequences'] = 'Planification des sequences';
$string['scheduling_activities'] = 'Planification des activités';

// Access
$string['access'] = 'Accès';
$string['access_from_date'] = 'Du';
$string['access_to_date'] = 'Au';
$string['access_closed'] = 'Fermé';
$string['access_open'] = 'Ouvert';
$string['access_on_dates'] = 'Selon dates';
$string['access_between_dates'] = 'Du ... au ...';
$string['access_from_date'] = 'A partir du...';
$string['access_to_date'] = "Jusqu'au...";
$string['access_on_completion'] = 'Selon complétion';
$string['access_as_remedial'] = 'Auto-remédiation';
$string['access_hidden'] = 'Caché';
$string['access_currently_closed'] = 'Accès actuellement fermé';
$string['access_currently_open'] = 'Accès actuellement ouvert';
$string['access_currently_hidden'] = 'Elément actuellement caché';
$string['access_from_to'] = 'Ouvert du {$a->from} au {$a->to}';
$string['access_from'] = 'Ouvert à partir du {$a}';
$string['access_to'] = 'Ouvert jusqu\'au {$a}';
$string['access_open_completion'] = 'Ouvert';
$string['access_closed_completion'] = 'Vous devez terminer l\'activité précédente.';
$string['access_open_remedial'] = 'Ouvert';
$string['access_closed_remedial'] = 'Fermé';


// ------------------------- Viewing ---------------------------

$string['show_hide_acheived_sequences'] = 'Séquences achevées';
$string['viewing_trainingpath'] = 'Consultation du parcours de formation';
$string['open'] = 'Ouvrir';
$string['back_to_activity'] = "Retour à l'activité";
$string['back_to_sequence'] = 'Retour à la séquence';
$string['back_to_batch'] = 'Retour à la phase';
$string['back_to_batches'] = 'Retour aux phases';
$string['back_to_certificate'] = 'Retour au thème';
$string['back_to_certificates'] = 'Retour aux thèmes';

// Status
$string['status_currently_no'] = 'Actuellement aucun statut';
$string['status_completion'] = 'Complétion';
$string['status_completion_notattempted'] = 'Non commencé';
$string['status_completion_incomplete'] = 'En cours';
$string['status_completion_completed'] = 'Terminé';
$string['status_success'] = 'Résultat';
$string['status_success_unknown'] = 'Inconnu';
$string['status_success_passed'] = 'Réussite';
$string['status_success_failed'] = 'Echec';
$string['status_score'] = 'Score';
$string['status_score_remedial'] = 'Score (remédiation)';
$string['status_progress'] = 'Avancement';
$string['status_time_spent'] = 'Temps passé';
$string['status_time_passing'] = 'Temps minimum';
$string['status_time_status'] = 'Statut lié au temps';
$string['status_time_status_critical'] = 'Critique';
$string['status_time_status_minimal'] = 'Minimum';
$string['status_time_status_nominal'] = 'Nominal';
$string['status_time_status_optimal'] = 'Optimum';
$string['next_step'] = 'Prochaine étape';


// ------------------------- Tutoring ---------------------------

$string['gradebook'] = 'Carnet de notes';
$string['tutoring'] = 'Tutorat: {$a}';
$string['manage_session'] = 'Gérer la session';
$string['managing_session'] = 'Gestion de la session';
$string['global_followup'] = 'Suivi général';
$string['participation'] = 'Présence';
$string['manage_tracking'] = 'Gérer le suivi';
$string['managing_tracking'] = 'Gestion du suivi';
$string['user'] = 'Utilisateur';
$string['status'] = 'Statut';
$string['force_completion'] = 'Forcer la complétion';
$string['force_scores'] = 'Forcer les scores';
$string['reporting'] = 'Suivi';
$string['learners'] = 'Apprenants';
$string['global'] = 'Global';
$string['average'] = 'Moyenne';
$string['average_remedial'] = 'Moyenne (apprenants en remédiation)';
$string['group_progress'] = 'Progression du groupe';
$string['learner_progress'] = "Progression de l'apprenant";
$string['learners_progress'] = 'Progression des apprenants';
$string['eval_only_certificate_switch_0'] = 'Ne montrer que les séquences évaluées';
$string['eval_only_certificate_switch_1'] = 'Montrer toutes les séquences';
$string['eval_only_sequence_switch_0'] = 'Ne montrer que les tests';
$string['eval_only_sequence_switch_1'] = 'Montrer toutes les activités';
$string['review'] = 'Corrigé';
$string['comments'] = 'Commentaires';
$string['group_results_'] = 'Résultats du groupe : {$a}';
$string['xls_progress'] = 'Progression';
$string['xls_time'] = 'Temps';
$string['xls_success'] = 'Score';
$string['xls_remedial'] = 'Remédiation';
$string['xls_export'] = 'Exporter (Excel)';
$string['xls_export_global'] = 'Export global (Excel)';
$string['xls_export_certificates'] = 'Exporter les thèmes (Excel)';
$string['xls_export_users'] = 'Exporter les utilisateurs (Excel)';
$string['xls_export_sequences'] = 'Exporter les séquences (Excel)';


// ------------------------- Calendars ---------------------------

$string['calendar'] = 'Calendrier';
$string['manage_calendars'] = 'Gérer les calendriers';
$string['add_calendar'] = 'Ajouter un calendrier';
$string['delete_calendar'] = 'Supprimer le calendrier';
$string['delete_calendar_confirm'] = 'Voulez-vous vraiment supprimer ce calendrier ?';
$string['no_calendar'] = "Il n'y a actuellement aucun calendrier.";
$string['new_calendar'] = 'Nouveau calendrier';
$string['year'] = 'Année';
$string['weekly_closed'] = 'Congés hebdomadaires';
$string['yearly_closed'] = 'Congés annuels';
$string['monday'] = 'Lundi';
$string['tuesday'] = 'Mardi';
$string['wednesday'] = 'Mercredi';
$string['thursday'] = 'Jeudi';
$string['friday'] = 'Vendredi';
$string['saturday'] = 'Samedi';
$string['sunday'] = 'Dimanche';
$string['yearly_closed_help'] = "Veuillez entrer les dates de congés (DD/MM/YYY) séparés par ';' ou un retour à la ligne.
                                    Vous pouvez aussi entrer des périodes (DD/MM/YYY-DD/MM/YYY).
                                    Exemple:<br>
                                        <br>10/01/2017
                                        <br>11/01/2017
                                        <br>14/01/2017;15/01/2017
                                        <br>20/01/2017-25/01/2017";
$string['yearly_closed_error'] = "Le format fourni est invalide. Veuillez vérifier le format requis en cliquant sur le bouton d'aide ci-dessus.";
$string['generate_schedule'] = 'Générer le planning';
$string['auto_scheduling'] = 'Planification automatique';
$string['generate_schedules'] = 'Générer le planning';
$string['manual_scheduling'] = 'Planification manuelle';
$string['generate_schedule_from'] = 'Générer à partir de';


// ------------------------- Reset ---------------------------

$string['reset_tracks'] = 'Supprimer les données de suivi';
$string['reset_comments'] = 'Supprimer les commentaires';
$string['reset_schedules'] = 'Supprimer les plannings';


// ------------------------- Statistics ---------------------------

$string['statistics'] = 'Statistiques';


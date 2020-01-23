<?php
return [
    'section_title'          => 'Titre',
    'section_text'           => 'Texte',
    'section_html'           => 'Texte (amélioré)',
    'section_image'          => 'Image',
    'section_tabular'        => 'données tabulaires',
    'section_pdf_attachment' => 'Fichier PDF',

    'policy_deadline_over'    => 'La date-limite est passée',
    'policy_ww_title'         => 'Wurzelwerk-Users',
    'policy_ww_desc'          => 'Wurzelwerk-Users',
    'policy_ww_motion_denied' => 'Seuls les utilisateurs de Wurzelwerk peuvent créer des motions.',
    'policy_ww_amend_denied'  => 'Seuls les utilisateurs de Wurzelwerk peuvent créer des amendements.',
    'policy_ww_comm_denied'   => 'Seuls les utilisateurs de Wurzelwerk peuvent commenter',
    'policy_ww_supp_denied'   => 'Seuls les utilisateurs de Wurzelwerk peuvent soutenir',

    'policy_admin_title'         => 'Admins',
    'policy_admin_desc'          => 'Admins',
    'policy_admin_motion_denied' => 'Seuls les admins peuvent créer des motions.',
    'policy_admin_amend_denied'  => 'Seuls les admins peuvent créer des amendements.',
    'policy_admin_comm_denied'   => 'Seuls les admins peuvent commenter',
    'policy_admin_supp_denied'   => 'Seuls les admins peuvent soutenir',

    'policy_all_title'            => 'Tout le monde',
    'policy_all_desc'             => 'Tout le monde',
    'policy_nobody_title'         => 'Personne',
    'policy_nobody_motion_denied' => 'Actuellement, personne ne peut créer de motions.',
    'policy_nobody_amend_denied'  => 'Actuellement, personne ne peut créer d\'amendements.',
    'policy_nobody_comm_denied'   => 'Actuellement, personne ne peut commenter.',
    'policy_nobody_supp_denied'   => 'Actuellement, personne ne peut soutenir.',

    'policy_logged_title'           => 'Utilisateurs enregistrés',
    'policy_logged_desc'            => 'Utilisateurs enregistrés',
    'policy_specuser_motion_denied' => 'Seuls les utilisateurs explicitement autorisés peuvent créer des motions.',
    'policy_specuser_amend_denied'  => 'Seuls les utilisateurs explicitement autorisés peuvent create amendements.',
    'policy_specuser_supp_denied'   => 'Seuls les utilisateurs explicitement autorisés peuvent soutenir.',
    'policy_specuser_comm_denied'   => 'Seuls les utilisateurs explicitement autorisés peuvent commenter.',
    'policy_logged_motion_denied'   => 'Tu dois te connecter pour créer des motions.',
    'policy_logged_amend_denied'    => 'Tu dois te connecter pour créer des amendements.',
    'policy_logged_supp_denied'     => 'Tu dois te connecter pour soutenir.',
    'policy_logged_comm_denied'     => 'Tu dois te connecter pour commenter.',

    'preset_bdk_name'           => 'Convention fédérale allemande',
    'preset_bdk_desc'           => 'Réglages similaires aux conventions fédérales du parti vert allemand. Ce règlage n\'a pas de sens en dehors de l\'Allemagne.',
    'preset_election_name'      => 'Candidature / élections',
    'preset_election_desc'      => '',
    'preset_motions_name'       => 'Motions',
    'preset_motions_desc'       => 'Tout le monde peut créer des motions et des amendements. Ils doivent toutefois être examinés par les administeurs de la conférence.',
    'preset_party_name'         => 'Convention politique',
    'preset_party_desc'         => 'Une convention avec un ordre du jour de base. Les utilisateurs peuvent candidater pour des mandats, créer des motions et des amendements. L\'ordre du jour et les mandats peuvent être ajustés manuellement.',
    'preset_party_top'          => 'Ordre du jour',
    'preset_party_elections'    => 'Élections',
    'preset_party_1leader'      => 'Élection: 1er président',
    'preset_party_2leader'      => 'Élection: 2nd président',
    'preset_party_treasure'     => 'Élection: Trésorier',
    'preset_party_motions'      => 'Motions',
    'preset_party_misc'         => 'Miscellaneous',
    'preset_manifesto_name'     => 'Programme électoral', // @TODO
    'preset_manifesto_desc'     => 'Une ébauche du programe est publié par les administrateurs. Les utilisateurs peuent commenter l\'ébauche et créer des amendements. Ces derniers doivent être examinés avant d\'être publiés. Tout le monde peut commenter.',
    'preset_manifesto_title'    => 'Titre du chapitre',
    'preset_manifesto_text'     => 'Text',
    'preset_manifesto_singular' => 'Chapitre',
    'preset_manifesto_plural'   => 'Chapitres',
    'preset_manifesto_call'     => 'Créer un chapitre',

    'preset_app_singular'  => 'Candidature',
    'preset_app_plural'    => 'Canidatures',
    'preset_app_call'      => 'Candidater',
    'preset_app_name'      => 'Nom',
    'preset_app_photo'     => 'Photo',
    'preset_app_data'      => 'Données',
    'preset_app_signature' => 'Signatures (scannée)',
    'preset_app_age'       => 'Âge',
    'preset_app_gender'    => 'Sexe/Genre',
    'preset_app_birthcity' => 'Lieu de naissance',
    'preset_app_intro'     => 'Introduction',

    'preset_motion_singular' => 'Motion',
    'preset_motion_plural'   => 'Motions',
    'preset_motion_call'     => 'Commencer une motion',
    'preset_motion_title'    => 'Titre',
    'preset_motion_text'     => 'Texte de la motion',
    'preset_motion_reason'   => 'Justification',

    'role_initiator' => 'Auteur',
    'role_supporter' => 'Soutien',
    'role_likes'     => 'Likes',
    'role_dislikes'  => 'Dislikes',

    'person_type_natural' => 'Personne physique',
    'person_type_orga'    => 'Organisationation',

    'user_status_1'  => 'Non-confirmé',
    'user_status_0'  => 'Confirmé',
    'user_status_-1' => 'Supprimé',

    'STATUS_DELETED'                      => 'Supprimé',
    'STATUS_WITHDRAWN'                    => 'Retiré',
    'STATUS_WITHDRAWN_INVISIBLE'          => 'Rétité (invisible)',
    'STATUS_UNCONFIRMED'                  => 'Non-confirmé',
    'STATUS_DRAFT'                        => 'Brouillon',
    'STATUS_SUBMITTED_UNSCREENED'         => 'Déposé (non-examiné)',
    'STATUS_SUBMITTED_SCREENED'           => 'Déposé',
    'STATUS_ACCEPTED'                     => 'Accepté',
    'STATUS_REJECTED'                     => 'Rejeté',
    'STATUS_MODIFIED_ACCEPTED'            => 'Accepté avec modification',
    'STATUS_MODIFIED'                     => 'Modifié',
    'STATUS_ADOPTED'                      => 'Adopté',
    'STATUS_COMPLETED'                    => 'Complet',
    'STATUS_REFERRED'                     => 'Reféré',
    'STATUS_VOTE'                         => 'Vote',
    'STATUS_PAUSED'                       => 'En pause',
    'STATUS_MISSING_INFORMATION'          => 'Information manquante',
    'STATUS_DISMISSED'                    => 'Dismissed',
    'STATUS_COLLECTING_SUPPORTERS'        => 'Appel à soutiens',
    'STATUS_DRAFT_ADMIN'                  => 'Brouillon (Admin)',
    'STATUS_SUBMITTED_UNSCREENED_CHECKED' => 'Déposé (examiné, pas encore publié)',

    'PROPOSED_ACCEPTED' => 'Accepté',
    'PROPOSED_REJECTED' => 'Rejeté',
    'PROPOSED_REFERRED' => 'Reféré',
    'PROPOSED_VOTE'     => 'Vote',

    'section_comment_none'      => 'Pas de commentaires',
    'section_comment_motion'    => 'Commenter l\'ensemble de la motion',
    'section_comment_paragraph' => 'Commenter un seul paragraphe',

    'home_layout_std'         => 'Standard',
    'home_layout_tags'        => 'Tabulaire, regroupés par tags',
    'home_layout_agenda'      => 'Ordre du jour',
    'home_layout_agenda_long' => 'Ordre du jour (plusieurs motions)',

    'supp_only_initiators'    => 'Seulement l\'auteur',
    'supp_given_by_initiator' => 'Soutiens donnés par l\'auteur',
    'supp_collect_before'     => 'Phase de collecte avant la publication (pas pour les organisations)',

    'activity_none'                       => 'Pas encore d\'activité',
    'activity_someone'                    => 'Quelqu\'un',
    'activity_deleted'                    => 'supprimé',
    'activity_MOTION_DELETE'              => '###USER### <strong>a supprimé la motion ###MOTION###</strong>',
    'activity_MOTION_DELETE_PUBLISHED'    => '###USER### <strong>a supprimé la motion ###MOTION###</strong>',
    'activity_MOTION_PUBLISH'             => '###USER### <strong>a déposé la motion</strong>',
    'activity_MOTION_WITHDRAW'            => 'La motion a été <strong>retirée</strong>.',
    'activity_MOTION_COMMENT'             => '###USER### <strong>a commenté la  motion</strong>',
    'activity_MOTION_SCREEN'              => 'La <strong>motion a été publiée</strong>',
    'activity_AMENDMENT_PUBLISH'          => '###USER### <strong>a déposé l\'amendement ###AMENDMENT###</strong>',
    'activity_AMENDMENT_CHANGE'           => '###USER### <strong>a modifié l\'amendement ###AMENDMENT###</strong>',
    'activity_AMENDMENT_DELETE'           => '###USER### <strong>a supprimé l\'amendement ###AMENDMENT###</strong>',
    'activity_AMENDMENT_DELETE_PUBLISHED' => '###USER### <strong>a supprimé l\'amendement ###AMENDMENT###</strong>',
    'activity_AMENDMENT_WITHDRAW'         => 'L\'<strong>amendement ###AMENDMENT###</strong> a été <strong>retiré</strong>.',
    'activity_AMENDMENT_COMMENT'          => '###USER### <strong>a commenté l\'amendement ###AMENDMENT###</strong>',
    'activity_AMENDMENT_SCREEN'           => 'L\'<strong>amendement ###AMENDMENT###</strong> a été <strong>publié</strong>',
];

-- ============================================================================
-- Script de création et d'optimisation des Vues SQL Métier - Base db_eicg
-- ============================================================================

-- 1. Vue des affectations pédagogiques (Enseignant - Matière - Classe)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_dash_pedagogie_affectations` AS
SELECT 
    em.id_enseignant_matiere,
    em.enseignant_code,
    em.matiere_code,
    em.classe_code,
    em.coefficient,
    em.etablissement_code,
    em.annee_code,
    em.user_code,
    em.created_at_enseignant_matiere,
    em.updated_at_enseignant_matiere,
    em.statut_enseignant_matiere,
    m.libelle_matiere,
    cl.libelle_classe,
    cl.niveau_code,
    cl.filiere_code,
    f.libelle_filiere,
    n.libelle_niveau,
    e.code_enseignant,
    e.grade_enseignant,
    u.nom_user AS nom_prof,
    u.prenom_user AS prenom_prof,
    u.nom_user AS nom_enseignant,
    u.prenom_user AS prenom_enseignant,
    u.email_user AS email_enseignant,
    u.telephone_user AS telephone_enseignant,
    CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS enseignant_nom
FROM enseignant_matiere em
LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
LEFT JOIN classes cl ON cl.code_classe = em.classe_code
LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
LEFT JOIN users u ON u.code_user = em.enseignant_code;

-- 2. Vue complète des inscriptions (Inscriptions + Étudiants + Classes + Filières + Niveaux + Années + Parents)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_inscriptions_completes` AS
SELECT 
    i.id_inscription,
    i.code_inscription,
    i.etudiant_code,
    i.classe_code,
    i.etablissement_code,
    i.annee_code,
    i.user_code,
    i.created_at_inscription,
    i.updated_at_inscription,
    i.montant_scolarite_inscription,
    i.canal_inscription,
    i.affectation_etat,
    i.statut_inscription,
    i.photo_inscription,
    e.id_etudiant,
    e.code_etudiant,
    e.matricule_etudiant,
    e.matricule_menet,
    e.matricule_mesrs,
    e.nom_etudiant,
    e.prenom_etudiant,
    CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS etudiant_nom,
    CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS nom_complet_etudiant,
    e.date_naissance_etudiant,
    e.lieu_naissance_etudiant,
    e.sexe_etudiant,
    e.nationalite_etudiant,
    e.lieu_residence_etudiant,
    e.telephone_etudiant,
    e.email_etudiant,
    e.photo_etudiant,
    e.statut_etudiant,
    e.etablissement_origine,
    cl.code_classe,
    cl.libelle_classe,
    cl.libelle_classe AS classe_nom,
    cl.filiere_code,
    cl.niveau_code,
    cl.cycle_code,
    f.code_filiere,
    f.libelle_filiere,
    f.slug_filiere,
    f.type_filiere,
    n.code_niveau,
    n.libelle_niveau,
    n.slug_niveau,
    a.code_annee,
    a.libelle_annee,
    a.libelle_annee AS annee_nom,
    p.nom_pere,
    p.telephone_pere,
    p.profession_pere,
    p.nom_mere,
    p.telephone_mere,
    p.profession_mere,
    p.nom_tuteur,
    p.telephone_tuteur
FROM inscriptions i
LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
LEFT JOIN classes cl ON cl.code_classe = i.classe_code
LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
LEFT JOIN annees a ON a.code_annee = i.annee_code
LEFT JOIN parents p ON p.etudiant_code = e.code_etudiant;

-- 3. Vue complète des paiements (Paiements + Inscriptions + Étudiants + Classes + Filières + Niveaux + Années + Caissier + Tranches)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_paiements_complets` AS
SELECT 
    p.id_paiement,
    p.code_paiement,
    p.montant_paiement,
    p.date_paiement,
    p.statut_paiement,
    p.canal_paiement,
    p.reference_paiement,
    p.observations,
    p.type_paiement,
    p.categorie_paiement,
    p.mode_paiement,
    p.user_code,
    p.annee_code,
    p.etablissement_code,
    p.inscription_code,
    p.tranche_code,
    p.session_caisse_code,
    ins.code_inscription,
    ins.etudiant_code,
    ins.classe_code,
    ins.annee_code AS inscription_annee_code,
    ins.statut_inscription,
    ins.montant_scolarite_inscription,
    ins.affectation_etat,
    ins.photo_inscription,
    e.code_etudiant,
    e.matricule_etudiant,
    e.nom_etudiant,
    e.prenom_etudiant,
    TRIM(CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, ''))) AS etudiant_nom,
    e.telephone_etudiant,
    e.email_etudiant,
    e.photo_etudiant,
    cl.code_classe,
    cl.libelle_classe,
    cl.niveau_code,
    cl.filiere_code,
    f.code_filiere,
    f.libelle_filiere,
    f.type_filiere,
    n.code_niveau,
    n.libelle_niveau,
    a.libelle_annee,
    u.nom_user,
    u.prenom_user,
    u.nom_user AS nom_caissier,
    u.prenom_user AS prenom_caissier,
    u.code_user AS caissier_code,
    t.libelle_tranche,
    t.montant_tranche,
    t.date_limite AS date_limite_tranche
FROM paiements p
LEFT JOIN inscriptions ins ON ins.code_inscription = p.inscription_code
LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
LEFT JOIN annees a ON (a.code_annee = p.annee_code OR a.code_annee = ins.annee_code)
LEFT JOIN users u ON u.code_user = p.user_code
LEFT JOIN tranches_scolarite t ON t.code_tranche = p.tranche_code;

-- 4. Vue d'agrégation financière des paiements par inscription (Scolarité vs Frais Annexes)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_bilan_paiements_inscriptions` AS
SELECT 
    p.inscription_code,
    COALESCE(SUM(CASE 
        WHEN p.statut_paiement != 'annule'
         AND (p.categorie_paiement IS NULL OR p.categorie_paiement != 'FRAIS_ANNEXES')
         AND (p.tranche_code IS NULL OR p.tranche_code != 'FRAIS_ANNEXES')
         AND (p.type_paiement IS NULL OR p.type_paiement NOT LIKE '%Frais Annexe%')
        THEN p.montant_paiement ELSE 0 END), 0) AS total_paye_scolarite,
    COALESCE(SUM(CASE 
        WHEN p.statut_paiement = 'confirme'
         AND (p.categorie_paiement IS NULL OR p.categorie_paiement != 'FRAIS_ANNEXES')
         AND (p.tranche_code IS NULL OR p.tranche_code != 'FRAIS_ANNEXES')
         AND (p.type_paiement IS NULL OR p.type_paiement NOT LIKE '%Frais Annexe%')
        THEN p.montant_paiement ELSE 0 END), 0) AS total_paye_confirme_scolarite,
    COALESCE(SUM(CASE 
        WHEN p.statut_paiement != 'annule'
         AND (p.categorie_paiement = 'FRAIS_ANNEXES' OR p.tranche_code = 'FRAIS_ANNEXES' OR p.type_paiement LIKE '%Frais Annexe%')
        THEN p.montant_paiement ELSE 0 END), 0) AS total_paye_annexes,
    COUNT(CASE WHEN p.statut_paiement != 'annule' THEN 1 END) AS nb_versements
FROM paiements p
GROUP BY p.inscription_code;

-- 5. Vue complète des notes (Notes + Compositions + Matières de composition + Étudiants + Inscriptions + Classes + Filières + Niveaux + Matières + Semestres + Années)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_notes_completes` AS
SELECT 
    n.id_note,
    n.code_note,
    n.valeur_note,
    n.composition_code,
    n.composition_matiere_id,
    n.etudiant_code,
    n.observations,
    n.inscription_code,
    n.user_code,
    n.etablissement_code,
    n.created_at_note,
    n.updated_at_note,
    n.statut_note,
    e.nom_etudiant AS etu_nom,
    e.prenom_etudiant AS etu_prenom,
    CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS nom_etudiant,
    e.matricule_etudiant,
    e.telephone_etudiant,
    e.email_etudiant,
    m.libelle_matiere,
    cm.matiere_code,
    cl.code_classe,
    cl.libelle_classe,
    cl.niveau_code,
    cl.filiere_code,
    f.libelle_filiere,
    niv.libelle_niveau,
    s.libelle_semestre,
    comp.semestre_code,
    a.libelle_annee,
    ins.annee_code,
    ins.classe_code AS inscription_classe_code,
    comp.libelle_composition,
    comp.type_composition,
    comp.type_composition AS type_evaluation_code,
    comp.coefficient
FROM notes n
INNER JOIN compositions comp ON comp.code_composition = n.composition_code
LEFT JOIN composition_matieres cm ON cm.id_composition_matiere = n.composition_matiere_id
LEFT JOIN etudiants e ON e.code_etudiant = n.etudiant_code
LEFT JOIN inscriptions ins ON ins.code_inscription = n.inscription_code
LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
LEFT JOIN niveaux niv ON niv.code_niveau = cl.niveau_code
LEFT JOIN matieres m ON m.code_matiere = cm.matiere_code
LEFT JOIN semestres s ON s.code_semestre = comp.semestre_code
LEFT JOIN annees a ON a.code_annee = COALESCE(comp.annee_code, ins.annee_code);

-- 6. Vue complète des absences (Absences + Inscriptions + Étudiants + Classes + Filières + Niveaux + Matières)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_absences_completes` AS
SELECT 
    abs.id_absence,
    abs.code_absence,
    abs.inscription_code,
    abs.emploi_code,
    abs.matiere_code,
    abs.date_absence,
    abs.duree_heures,
    abs.justifiee,
    abs.motif,
    abs.motif AS motif_absence,
    abs.annee_code,
    abs.etablissement_code,
    abs.created_at_absence,
    ins.annee_code AS inscription_annee_code,
    ins.classe_code,
    e.nom_etudiant AS etu_nom,
    e.prenom_etudiant AS etu_prenom,
    CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) AS nom_etudiant,
    e.matricule_etudiant,
    e.telephone_etudiant,
    e.email_etudiant,
    e.code_etudiant,
    cl.code_classe,
    cl.libelle_classe,
    cl.niveau_code,
    f.libelle_filiere,
    n.libelle_niveau,
    m.libelle_matiere
FROM absences abs
LEFT JOIN inscriptions ins ON ins.code_inscription = abs.inscription_code
LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
LEFT JOIN matieres m ON m.code_matiere = abs.matiere_code;

-- 7. Vue complète des emplois du temps (Emplois + Classes + Niveaux + Matières + Salles + Enseignants + Users)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_emplois_temps_complets` AS
SELECT 
    edt.id_emploi,
    edt.code_emploi,
    edt.classe_code,
    edt.matiere_code,
    edt.enseignant_code,
    edt.salle_code,
    edt.etablissement_code,
    edt.annee_code,
    edt.user_code,
    edt.jour,
    edt.heure_debut,
    edt.heure_fin,
    edt.created_at_emploi,
    edt.updated_at_emploi,
    edt.statut_emploi,
    cl.libelle_classe,
    cl.niveau_code,
    n.libelle_niveau,
    m.libelle_matiere,
    s.libelle_salle,
    CONCAT(COALESCE(u.nom_user, ''), ' ', COALESCE(u.prenom_user, '')) AS nom_prof
FROM emplois_temps edt
LEFT JOIN classes cl ON cl.code_classe = edt.classe_code
LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
LEFT JOIN matieres m ON m.code_matiere = edt.matiere_code
LEFT JOIN salles s ON s.code_salle = edt.salle_code
LEFT JOIN enseignants e ON e.code_enseignant = edt.enseignant_code
LEFT JOIN users u ON u.code_user = edt.enseignant_code;

-- 8. Vue optimisée des détails de notes pour les bulletins
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_bulletin_notes_details` AS
SELECT 
    n.id_note AS id_note,
    n.code_note AS code_note,
    n.valeur_note AS valeur_note,
    n.statut_note AS statut_note,
    n.observations AS observations,
    n.created_at_note AS created_at_note,
    n.inscription_code AS inscription_code,
    i.etudiant_code AS etudiant_code,
    i.classe_code AS classe_code,
    i.annee_code AS annee_code,
    cl.filiere_code AS filiere_code,
    cl.niveau_code AS niveau_code,
    cl.libelle_classe AS libelle_classe,
    f.libelle_filiere AS libelle_filiere,
    niv.libelle_niveau AS libelle_niveau,
    comp.id_composition AS id_composition,
    comp.code_composition AS code_composition,
    comp.libelle_composition AS libelle_composition,
    comp.type_composition AS type_evaluation_code,
    comp.semestre_code AS semestre_code,
    COALESCE(comp.coefficient, 1.00) AS coefficient_composition,
    comp.statut_composition AS statut_composition,
    comp.user_code AS enseignant_user_code,
    COALESCE(NULLIF(TRIM(edt_ens.enseignant), ''), CONCAT(COALESCE(u.nom_user,''),' ',COALESCE(u.prenom_user,''))) AS nom_enseignant,
    edt_ens.enseignant AS enseignant,
    cm.matiere_code AS matiere_code,
    m.libelle_matiere AS libelle_matiere,
    COALESCE(mq.coefficient, 1.00) AS coefficient_maquette,
    COALESCE(mq.categorie_matiere, 'GENERALE') AS categorie_matiere
FROM notes n
JOIN composition_matieres cm ON cm.id_composition_matiere = n.composition_matiere_id
JOIN composition_niveau_filiere cnf ON cnf.code_composition_niveau_filiere = cm.composition_niveau_filiere_code
JOIN compositions comp ON comp.code_composition = cm.composition_code
JOIN inscriptions i ON i.code_inscription = n.inscription_code
JOIN classes cl ON cl.code_classe = i.classe_code
JOIN filieres f ON f.code_filiere = cl.filiere_code
JOIN niveaux niv ON niv.code_niveau = cl.niveau_code
JOIN matieres m ON m.code_matiere = cm.matiere_code
LEFT JOIN users u ON u.code_user = comp.user_code
LEFT JOIN (
    SELECT 
        edt.classe_code,
        edt.matiere_code,
        COALESCE(edt.annee_code, '') AS annee_code,
        GROUP_CONCAT(DISTINCT TRIM(CONCAT(COALESCE(u_edt.nom_user, ''), ' ', COALESCE(u_edt.prenom_user, ''))) SEPARATOR ', ') AS enseignant
    FROM emplois_temps edt
    LEFT JOIN users u_edt ON u_edt.code_user = edt.enseignant_code
    WHERE edt.statut_emploi = 'actif' OR edt.statut_emploi IS NULL
    GROUP BY edt.classe_code, edt.matiere_code, COALESCE(edt.annee_code, '')
) edt_ens ON edt_ens.classe_code = i.classe_code
         AND edt_ens.matiere_code = cm.matiere_code
         AND (edt_ens.annee_code = i.annee_code OR edt_ens.annee_code = '' OR edt_ens.annee_code IS NULL)
LEFT JOIN maquettes_pedagogiques mq ON mq.filiere_code = cl.filiere_code 
                                    AND mq.niveau_code = cl.niveau_code 
                                    AND mq.matiere_code = cm.matiere_code
                                    AND (mq.annee_code = i.annee_code OR mq.annee_code = '' OR mq.annee_code IS NULL)
                                    AND (mq.statut_maquette = 'actif' OR mq.statut_maquette IS NULL);

-- 9. Vue complète des maquettes pédagogiques (Maquettes + Matières + Filières + Niveaux + Années)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_maquettes_completes` AS
SELECT
    mq.id_maquette,
    mq.code_maquette,
    mq.annee_code,
    mq.filiere_code,
    mq.niveau_code,
    mq.matiere_code,
    mq.categorie_matiere,
    mq.coefficient,
    mq.etablissement_code,
    mq.statut_maquette,
    mq.user_code,
    mq.created_at_maquette,
    mq.updated_at_maquette,
    m.libelle_matiere,
    f.libelle_filiere,
    f.type_filiere,
    n.libelle_niveau,
    a.libelle_annee
FROM maquettes_pedagogiques mq
LEFT JOIN matieres m ON m.code_matiere = mq.matiere_code
LEFT JOIN filieres f ON f.code_filiere = mq.filiere_code
LEFT JOIN niveaux n ON n.code_niveau = mq.niveau_code
LEFT JOIN annees a ON a.code_annee = mq.annee_code;

-- 10. Vue de synthèse des inscriptions pour les bulletins & PV de délibération
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_bulletin_inscriptions_synthese` AS
SELECT
    i.id_inscription,
    i.code_inscription,
    i.etudiant_code,
    i.classe_code,
    i.annee_code,
    i.etablissement_code,
    i.statut_inscription,
    i.affectation_etat,
    i.photo_inscription,
    e.id_etudiant,
    e.code_etudiant,
    e.matricule_etudiant,
    e.matricule_menet,
    e.matricule_mesrs,
    e.nom_etudiant,
    e.prenom_etudiant,
    TRIM(CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, ''))) AS etudiant_nom,
    e.sexe_etudiant,
    e.photo_etudiant,
    e.telephone_etudiant,
    e.email_etudiant,
    cl.code_classe,
    cl.libelle_classe,
    cl.libelle_classe AS classe_nom,
    cl.filiere_code,
    cl.niveau_code,
    cl.cycle_code,
    f.code_filiere,
    f.libelle_filiere,
    f.libelle_filiere AS filiere_nom,
    f.slug_filiere,
    f.type_filiere,
    n.code_niveau,
    n.libelle_niveau,
    n.libelle_niveau AS niveau_nom,
    n.slug_niveau,
    a.code_annee,
    a.libelle_annee,
    a.libelle_annee AS annee_nom
FROM inscriptions i
INNER JOIN etudiants e ON e.code_etudiant = i.etudiant_code
LEFT JOIN classes cl ON cl.code_classe = i.classe_code
LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
LEFT JOIN annees a ON a.code_annee = i.annee_code;

-- 11. Vue des sessions d'évaluations par classe, composition et matière (Registre des Notes)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_sessions_evaluations` AS
SELECT
    comp.id_composition,
    comp.code_composition,
    comp.libelle_composition,
    comp.type_composition,
    comp.semestre_code,
    COALESCE(comp.coefficient, 1.00) AS coefficient,
    comp.date_composition,
    comp.date_fin_composition,
    comp.statut_composition,
    comp.created_at_composition,
    comp.user_code AS composition_user_code,
    COALESCE(NULLIF(comp.annee_code, ''), cl.annee_code) AS annee_code,
    comp.etablissement_code,
    cm.id_composition_matiere,
    cm.matiere_code,
    m.libelle_matiere,
    cl.code_classe,
    cl.libelle_classe,
    cl.niveau_code,
    cl.filiere_code,
    niv.libelle_niveau,
    f.libelle_filiere,
    s.libelle_semestre,
    s.statut_semestre,
    a.libelle_annee,
    COALESCE(ns.date_enregistrement_note, comp.date_composition, comp.created_at_composition) AS date_enregistrement,
    ns.date_enregistrement_note,
    COALESCE(eff.total_etudiants, 0) AS total_etudiants,
    COALESCE(ns.nb_notes, 0) AS nb_notes,
    ns.moyenne_classe
FROM composition_matieres cm
INNER JOIN composition_niveau_filiere cnf ON cnf.code_composition_niveau_filiere = cm.composition_niveau_filiere_code
INNER JOIN compositions comp ON comp.code_composition = cm.composition_code
INNER JOIN classes cl ON cl.niveau_code = cnf.niveau_code
                     AND cl.filiere_code = cnf.filiere_code
                     AND (comp.annee_code IS NULL OR comp.annee_code = '' OR cl.annee_code = comp.annee_code)
LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
LEFT JOIN niveaux niv ON niv.code_niveau = cl.niveau_code
LEFT JOIN matieres m ON m.code_matiere = cm.matiere_code
LEFT JOIN semestres s ON s.code_semestre = comp.semestre_code
LEFT JOIN annees a ON a.code_annee = COALESCE(NULLIF(comp.annee_code, ''), cl.annee_code)
LEFT JOIN (
    SELECT
        i.classe_code,
        i.annee_code,
        COUNT(DISTINCT i.code_inscription) AS total_etudiants
    FROM inscriptions i
    WHERE i.statut_inscription != 'annule' OR i.statut_inscription IS NULL
    GROUP BY i.classe_code, i.annee_code
) eff ON eff.classe_code = cl.code_classe
     AND eff.annee_code = COALESCE(NULLIF(comp.annee_code, ''), cl.annee_code)
LEFT JOIN (
    SELECT
        n.composition_code,
        n.composition_matiere_id,
        i.classe_code,
        COUNT(DISTINCT n.id_note) AS nb_notes,
        ROUND(AVG(n.valeur_note), 2) AS moyenne_classe,
        MAX(n.created_at_note) AS date_enregistrement_note
    FROM notes n
    INNER JOIN inscriptions i ON i.code_inscription = n.inscription_code
    WHERE n.statut_note = 'actif'
      AND (i.statut_inscription != 'annule' OR i.statut_inscription IS NULL)
    GROUP BY n.composition_code, n.composition_matiere_id, i.classe_code
) ns ON ns.composition_code = comp.code_composition
    AND ns.composition_matiere_id = cm.id_composition_matiere
    AND ns.classe_code = cl.code_classe;

-- 12. Vue de synthèse annuelle dashboard (Dissociation stricte Scolarité / Frais Annexes)
CREATE OR REPLACE SQL SECURITY INVOKER VIEW `v_dash_synthese_annuelle` AS
SELECT
    a.code_annee AS code_annee,
    a.libelle_annee AS libelle_annee,
    a.statut_annee AS statut_annee,
    (SELECT COUNT(0) FROM inscriptions ins WHERE ins.annee_code = a.code_annee AND (ins.statut_inscription != 'annule' OR ins.statut_inscription IS NULL)) AS total_inscrits,
    (SELECT COUNT(0) FROM classes cl WHERE cl.annee_code = a.code_annee AND (cl.statut_classe = 'actif' OR cl.statut_classe IS NULL)) AS total_classes,
    (SELECT COALESCE(SUM(ins2.montant_scolarite_inscription), 0) FROM inscriptions ins2 WHERE ins2.annee_code = a.code_annee AND (ins2.statut_inscription != 'annule' OR ins2.statut_inscription IS NULL)) AS total_scolarite_attendue,
    (SELECT COALESCE(SUM(p.montant_paiement), 0)
     FROM paiements p
     WHERE p.annee_code = a.code_annee
       AND p.statut_paiement = 'confirme'
       AND (p.categorie_paiement IS NULL OR p.categorie_paiement != 'FRAIS_ANNEXES')
       AND (p.tranche_code IS NULL OR p.tranche_code != 'FRAIS_ANNEXES')
       AND (p.type_paiement IS NULL OR p.type_paiement NOT LIKE '%Frais Annexe%')) AS total_encaisse,
    (SELECT COALESCE(SUM(d.montant_depense), 0) FROM depenses d WHERE d.annee_code = a.code_annee AND (d.statut_depense != 'annule' OR d.statut_depense IS NULL)) AS total_depenses
FROM annees a;




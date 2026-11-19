SELECT e.*,
       u.nom_user,
       u.prenom_user,
       -- MySQL regroupe lui-même toutes les matières du professeur en une seule ligne :
       GROUP_CONCAT(DISTINCT edt.matiere_code SEPARATOR ',') AS matieres_codes,
       GROUP_CONCAT(DISTINCT m.libelle_matiere SEPARATOR ', ') AS matieres_libelles
FROM enseignants e
JOIN users u ON u.code_user = e.code_enseignant
LEFT JOIN emplois_temps edt ON (edt.enseignant_code = e.code_enseignant OR edt.enseignant_code = e.user_code)
LEFT JOIN matieres m ON m.code_matiere = edt.matiere_code
GROUP BY e.id_enseignant
ORDER BY e.id_enseignant DESC

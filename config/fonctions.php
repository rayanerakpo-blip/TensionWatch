<?php
// Fichier de fonctions réutilisables dans plusieurs pages du site

/**
 * Évalue le niveau de gravité d'une mesure de tension artérielle
 * Seuils basés sur les recommandations médicales usuelles (OMS / HAS)
 *
 * @param int $systolique  Valeur systolique (le chiffre du haut)
 * @param int $diastolique Valeur diastolique (le chiffre du bas)
 * @return array ['niveau' => code CSS, 'label' => texte à afficher]
 */
function evaluerTension($systolique, $diastolique) {
    // Crise hypertensive : nécessite une consultation en urgence
    if ($systolique >= 180 || $diastolique >= 120) {
        return ['niveau' => 'urgence', 'label' => 'Crise hypertensive - Consultez en urgence'];
    }
    // Hypertension avérée
    elseif ($systolique >= 140 || $diastolique >= 90) {
        return ['niveau' => 'haute', 'label' => 'Hypertension'];
    }
    // Tension élevée (pré-hypertension)
    elseif ($systolique >= 120 || $diastolique >= 80) {
        return ['niveau' => 'elevee', 'label' => 'Tension élevée'];
    }
    // Tension normale
    else {
        return ['niveau' => 'normale', 'label' => 'Tension normale'];
    }
}
?>
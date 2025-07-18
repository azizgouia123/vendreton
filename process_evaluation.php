<?php

// --- CONFIGURATION ---
// Nom du fichier où les évaluations seront stockées (doit être accessible en écriture par le serveur web)
$dataFile = 'evaluations.json'; 

// URL de la page de remerciement après l'envoi réussi
$thankYouPage = "thank_you.html"; 

// URL de la page d'erreur en cas de problème d'envoi
$errorPage = "error.html"; 
// --- FIN CONFIGURATION ---


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $vin = isset($_POST['vin']) ? htmlspecialchars(trim($_POST['vin'])) : 'N/A';
    $kilometrage = isset($_POST['kilometrage']) ? htmlspecialchars(trim($_POST['kilometrage'])) : 'N/A';
    $etat = isset($_POST['etat']) ? htmlspecialchars(trim($_POST['etat'])) : 'N/A';
    $commentaires = isset($_POST['commentaires']) ? htmlspecialchars(trim($_POST['commentaires'])) : 'Aucun';

    // Validation basique (optionnel mais recommandé)
    if (empty($vin) || strlen($vin) !== 17) {
        header("Location: " . $errorPage . "?message=VIN invalide ou manquant");
        exit();
    }
    // Ajoutez d'autres validations si nécessaire

    // Préparer les données pour le fichier JSON
    $newEvaluation = [
        'timestamp' => date("Y-m-d H:i:s"), // Ajoute un horodatage
        'vin' => $vin,
        'kilometrage' => $kilometrage,
        'etat' => $etat,
        'commentaires' => $commentaires
    ];

    // Lire les évaluations existantes du fichier
    $currentEvaluations = [];
    if (file_exists($dataFile) && filesize($dataFile) > 0) {
        $fileContent = file_get_contents($dataFile);
        $currentEvaluations = json_decode($fileContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Gérer une erreur de décodage JSON (fichier corrompu)
            $currentEvaluations = [];
        }
    }

    // Ajouter la nouvelle évaluation
    $currentEvaluations[] = $newEvaluation;

    // Écrire toutes les évaluations (y compris la nouvelle) dans le fichier JSON
    // JSON_PRETTY_PRINT rend le fichier lisible, JSON_UNESCAPED_UNICODE pour les accents
    $jsonResult = json_encode($currentEvaluations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($jsonResult === false) {
        // Erreur d'encodage JSON
        error_log("Erreur d'encodage JSON: " . json_last_error_msg());
        header("Location: " . $errorPage . "?message=Erreur interne du serveur.");
        exit();
    }

    if (file_put_contents($dataFile, $jsonResult)) {
        // Rediriger vers la page de remerciement si l'enregistrement est réussi
        header("Location: " . $thankYouPage);
        exit();
    } else {
        // Rediriger vers la page d'erreur si l'enregistrement échoue
        error_log("Impossible d'écrire dans le fichier: " . $dataFile);
        header("Location: " . $errorPage . "?message=Erreur lors de l'enregistrement des données.");
        exit();
    }

} else {
    // Si le script est accédé directement (non via un POST de formulaire),
    // rediriger l'utilisateur vers la page d'évaluation ou d'accueil
    header("Location: eval.html");
    exit();
}

?>

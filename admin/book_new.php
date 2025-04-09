<?php
session_start();

include_once "../utils/regex.php";
include_once "./partials/top.php";

$errors = [];
$successes = [];

function logUnauthorizedAccess($reason) {
    $userIp = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';

    $message = sprintf(
        "[%s] [UNAUTHORIZED_ACCESS] IP: %s | Raison: %s\n",
        date('Y-m-d H:i:s'),
        $userIp,
        $reason
    );

    error_log($message, 3, '/var/log/access.log');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logUnauthorizedAccess("Méthode non autorisée: " . $_SERVER['REQUEST_METHOD']);

    header('Location: ../405.php');
    exit;
}

$csrfTokenPosted = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!isset($_SESSION['csrf_token']) || !$csrfTokenPosted ||
    !hash_equals($_SESSION['csrf_token'], $csrfTokenPosted)) {
    logUnauthorizedAccess("Token CSRF invalide ou manquant.");

    $errors[] = "Le token CSRF est invalide ou manquant. Veuillez réessayer.";
}

$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$title || trim($title) === '') {
    $errors[] = "Le champ 'Titre' est obligatoire. Merci de saisir une valeur.";
} else {
    $title = trim($title);
    $titleLen = mb_strlen($title);
    if ($titleLen < 2 || $titleLen > 150) {
        $errors[] = "Le champ 'Titre' doit contenir entre 2 et 150 caractères.";
    }
}

$isbn = filter_input(INPUT_POST, 'isbn', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$isbn || trim($isbn) === '') {
    $errors[] = "Le champ 'ISBN' est obligatoire. Merci de saisir une valeur.";
} else {
    $isbn = trim($isbn);
    if (!preg_match($validPatterns['isbn'], $isbn)) {
        $errors[] = "Le champ 'ISBN' doit contenir exactement 13 chiffres.";
    }
}

$summary = filter_input(INPUT_POST, 'summary', FILTER_SANITIZE_SPECIAL_CHARS);
if ($summary !== null && $summary !== false && trim($summary) !== '') {
    $summary = trim($summary);
    if (mb_strlen($summary) > 65535) {
        $errors[] = "Le champ 'Résumé' ne doit pas excéder 65535 caractères.";
    }
} else {
    $summary = '';
}

$publicationYear = filter_input(INPUT_POST, 'publication_year', FILTER_SANITIZE_SPECIAL_CHARS);
if (!$publicationYear || trim($publicationYear) === '') {
    $errors[] = "Le champ 'Année de publication' est obligatoire. Merci de saisir une valeur.";
} else {
    $publicationYear = trim($publicationYear);
    if (!preg_match($validPatterns['year'], $publicationYear)) {
        $errors[] = "Le champ 'Année de publication' doit être au format YYYY (ex. : 1997).";
    }
}

if (count($errors) === 0) {
    unset($_SESSION['csrf_token']);

    $currentDateTime = new DateTime();
    $createdAt = $updatedAt = $currentDateTime->format('Y-m-d H:i:s');

    $host = 'localhost';
    $dbName = 'mediatek';
    $user = 'mentor';
    $pass = 'superMentor';

    try {
        $connection = new PDO(
            "mysql:host=$host;dbname=$dbName;charset=utf8",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $query = "INSERT INTO `book`
                  (`isbn`, `title`, `summary`, `publication_year`, `created_at`, `updated_at`)
                  VALUES (:isbn, :title, :summary, :publication_year, :created_at, :updated_at)";
        $statement = $connection->prepare($query);

        $statement->execute([
            ':isbn'             => $isbn,
            ':title'            => $title,
            ':summary'          => $summary,
            ':publication_year' => $publicationYear,
            ':created_at'       => $createdAt,
            ':updated_at'       => $updatedAt
        ]);

        $successes[] = 'Le nouveau livre a bien été enregistré.';
        $connection = null;
    } catch (PDOException $e) {
        $technicalErrorMessage = "[CreationBookError] " . $e->getMessage();
        error_log(
            date('Y-m-d H:i:s') . " : " . $technicalErrorMessage . "\n",
            3,
            '/var/log/error.log'
        );
        $errors[] = "Une erreur technique s'est produite. Veuillez contacter l'administrateur.";
    }
}

if (count($errors) !== 0) {
    $errorMsg = "<ul>";
    foreach ($errors as $error) {
        $errorMsg .= "<li>$error</li>";
    }
    $errorMsg .= "</ul>";
    echo $errorMsg;
} else {
    $successMsg = "<ul>";
    foreach ($successes as $success) {
        $successMsg .= "<li>$success</li>";
    }
    $successMsg .= "</ul>";
    echo $successMsg;
}

include_once "./partials/bottom.php";

<?php
// Configuration de la base de données (identique à celle de l'API Python)
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'administration_courses'
];

// Fonction pour afficher les résultats
function print_result($title, $result) {
    echo "<h3>$title</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    echo "<hr>";
}

// Connexion à la base de données
try {
    $conn = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['database']}",
        $db_config['user'],
        $db_config['password']
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h2>✅ Connexion à la base de données réussie</h2>";
} catch (PDOException $e) {
    die("<h2>❌ Erreur de connexion: " . $e->getMessage() . "</h2>");
}

// Vérifier si la base de données existe
try {
    $stmt = $conn->query("SHOW DATABASES LIKE '{$db_config['database']}'");
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (count($result) > 0) {
        echo "<h3>✅ Base de données '{$db_config['database']}' existe</h3>";
    } else {
        echo "<h3>❌ Base de données '{$db_config['database']}' n'existe pas</h3>";
    }
} catch (PDOException $e) {
    echo "<h3>❌ Erreur lors de la vérification de la base de données: " . $e->getMessage() . "</h3>";
}

// Vérifier les tables
try {
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    print_result("Tables dans la base de données", $tables);
} catch (PDOException $e) {
    echo "<h3>❌ Erreur lors de la récupération des tables: " . $e->getMessage() . "</h3>";
}

// Vérifier les procédures stockées
try {
    $stmt = $conn->query("SHOW PROCEDURE STATUS WHERE Db = '{$db_config['database']}'");
    $procedures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_result("Procédures stockées", $procedures);
    
    // Vérifier spécifiquement la procédure AjouterCoureurCourse
    $ajouterCoureurExists = false;
    foreach ($procedures as $proc) {
        if ($proc['Name'] === 'AjouterCoureurCourse') {
            $ajouterCoureurExists = true;
            break;
        }
    }
    
    if ($ajouterCoureurExists) {
        echo "<h3>✅ La procédure 'AjouterCoureurCourse' existe</h3>";
        
        // Afficher les paramètres de la procédure
        $stmt = $conn->query("SHOW CREATE PROCEDURE AjouterCoureurCourse");
        $procDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<h4>Définition de la procédure:</h4>";
        echo "<pre>" . htmlspecialchars($procDetails['Create Procedure']) . "</pre>";
    } else {
        echo "<h3>❌ La procédure 'AjouterCoureurCourse' n'existe pas</h3>";
    }
} catch (PDOException $e) {
    echo "<h3>❌ Erreur lors de la récupération des procédures: " . $e->getMessage() . "</h3>";
}

// Tester l'ajout d'un coureur (si la procédure existe)
if ($ajouterCoureurExists) {
    try {
        // Générer un numéro RFID unique pour le test
        $rfid = mt_rand(1000000000, 9999999999);
        
        echo "<h3>Test d'ajout d'un coureur</h3>";
        echo "<p>Tentative d'ajout d'un coureur de test avec RFID: $rfid</p>";
        
        // Préparer l'appel à la procédure stockée
        $stmt = $conn->prepare("CALL AjouterCoureurCourse(?, ?, ?, ?, ?, ?)");
        $nom = "Test";
        $prenom = "PHP";
        $age = 30;
        $poids = 75;
        $courseId = 1; // Assurez-vous que cette course existe
        
        $stmt->bindParam(1, $nom, PDO::PARAM_STR);
        $stmt->bindParam(2, $prenom, PDO::PARAM_STR);
        $stmt->bindParam(3, $age, PDO::PARAM_INT);
        $stmt->bindParam(4, $poids, PDO::PARAM_INT);
        $stmt->bindParam(5, $rfid, PDO::PARAM_INT);
        $stmt->bindParam(6, $courseId, PDO::PARAM_INT);
        
        $stmt->execute();
        
        echo "<p>✅ Coureur ajouté avec succès!</p>";
    } catch (PDOException $e) {
        echo "<p>❌ Erreur lors de l'ajout du coureur: " . $e->getMessage() . "</p>";
    }
}
?>
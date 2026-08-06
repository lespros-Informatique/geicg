<?php

class Validator
{
    private $pdo; // private propriete de connexion a la database

    public function __construct() //ce contructer & la connexion a la database
    {
        $this->pdo = new Database();
    }

    public function safeRollBack()
    {
        if ($this->pdo->getCon()->inTransaction()) {
            $this->pdo->getCon()->rollBack();
        }
    }

    public function safeCommit()
    {
        if ($this->pdo->getCon()->inTransaction()) {
            $this->pdo->getCon()->commit();
        }
    }

    public function safeBeginTransaction()
    {
        if (!$this->pdo->getCon()->inTransaction()) {
            $this->pdo->getCon()->beginTransaction();
        }
    }

    public function hasRole($role)
    {
        return in_array($role, $_SESSION['roles'] ?? []);
    }

    // Exemple d'utilisation
    // if (hasRole('Admin')) {
    //     echo "Bienvenue Admin !";
    // }


    public function verif($table, $field, $value) // function veriviant l'existance d'une ligne par 1 element
    {
        $result = false;
        try {
            $sql = " SELECT * FROM $table WHERE $field=?";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$value]);
            if ($query->rowCount() > 0) {
                $result = true;
            }
        } catch (Exception $e) {
            error_log("Validator::verif error: " . $e->getMessage());
        }

        return $result;
    }

    public static function notifyNodeNewOrder($orderId)
    {
        $payload = json_encode(["order_id" => $orderId]);

        // DEBUG: Log the order ID being sent
        error_log("🔔 Trying to notify Node.js about order: " . $orderId);
        
        // Complete URL to Node.js server - FIXED: removed /resto prefix
        $nodeServerUrl = "http://localhost:8080/commandes/createFromCart";
        error_log("🔗 Sending request to: " . $nodeServerUrl);
        
        $ch = curl_init($nodeServerUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Content-Length: " . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 second timeout

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        error_log("📡 cURL Response Code: " . $httpCode);
        error_log("📡 cURL Error: " . ($curlError ?: "None"));
        error_log("📡 Response: " . $response);
        
        curl_close($ch);

        return $response;
    }

    public function _verif($table, $field, $value, $id, $id_val) // function veriviant l'existance d'une ligne par 1 element et id
    {
        $result = false;
        try {
            $sql = " SELECT * FROM $table WHERE $field=? AND $id != ?";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$value, $id_val]);
            if ($query->rowCount() > 0) {
                $result = true;
            }
        } catch (Exception $e) {
            error_log("Validator::_verif error: " . $e->getMessage());
        }

        return $result;
    }

    public function verifs($table, $field1, $field2, $val1, $val2) // function veriviant l'existance d'une ligne par 2 element
    {
        $data = false;
        try {
            $sql = " SELECT * FROM $table WHERE $field1=? AND $field2=?";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$val1, $val2]);
            if ($query->rowCount() > 0) {
                $data = true;
            }
        } catch (Exception $e) {
            error_log("Validator::verifs error: " . $e->getMessage());
        }

        return $data;
    }

    // function veriviant l'existance d'une ligne par 2 element
    public function _verifs($table, $field1, $field2, $val1, $val2, $session, $sess_val)
    {
        $data = false;
        try {
            $sql = " SELECT * FROM $table WHERE $field1=? AND $field2=? AND $session=?";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$val1, $val2, $sess_val]);
            if ($query->rowCount() > 0) {
                $data = true;
            }
        } catch (Exception $e) {
            error_log("Validator::_verifs error: " . $e->getMessage());
        }

        return $data;
    }

    public function updateByElement($table, $element, $element_val, $id, $id_val) // update by element
    {
        try {
            $sql = "UPDATE $table SET $element=? WHERE $id=?";
            $query = $this->pdo->getCon()->prepare($sql);
            if ($query->execute([$element_val, $id_val])) {
                return true;
            }
        } catch (Exception $e) {
            error_log("Validator::updateByElement error: " . $e->getMessage());
        }

        return false;
    }

    public function getByElement($table, $el, $val) // obtenir une ligne de données par un element
    {
        $data = '';
        try {
            $sql = " SELECT * FROM $table WHERE $el = ?
                ";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$val]);
            if ($query->rowCount() > 0) {
                $data = $query->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Validator::getByElement error: " . $e->getMessage());
        }

        return $data;
    }

    public function getByElements($table, $el1, $val1, $el2, $val2) // obtenir une ligne de données par un element
    {
        $data = '';
        try {
            $sql = " SELECT * FROM $table WHERE $el1 = ? AND $el2 = ?
                ";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$val1, $val2]);
            if ($query->rowCount() > 0) {
                $data = $query->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Validator::getByElements error: " . $e->getMessage());
        }

        return $data;
    }

    public function getAll($table) // obtenir toutes les données d'une table
    {
        $data = '';
        try {
            $sql = " SELECT * FROM $table 
                ";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute();
            if ($query->rowCount() > 0) {
                $data = $query->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Validator::getAll error: " . $e->getMessage());
        }

        return $data;
    }

    public function getAllOder($table, $el) // obtenir toutes les données d'une table par ordre croisant
    {
        $data = '';
        try {
            $sql = " SELECT * FROM $table  ORDER BY $el DESC";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute();
            if ($query->rowCount() > 0) {
                $data = $query->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Validator::getAllOder error: " . $e->getMessage());
        }

        return $data;
    }

    public function getTable($table) // obtenir toutes les données d'une table par ordre croisant
    {
        $data = '';
        try {
            $sql = " SELECT * FROM $table";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute();
            if ($query->rowCount() > 0) {
                $data = $query->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Validator::getTable error: " . $e->getMessage());
        }

        return $data;
    }

    public function getAllByElement($table, $el, $val) // obtenir toutes les données d'une table
    {
        $data = [];
        try {
            $sql = " SELECT * FROM $table WHERE $el = ?
                ";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$val]);
            if ($query->rowCount() > 0) {
                $data = $query->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Validator::getAllByElement error: " . $e->getMessage());
        }

        return $data;
    }
    public function getByMaxElement($table, $id) // obtenir toutes les données d'une table
    {
        $data = [];
        try {
            $sql = " SELECT MAX($id) AS $id FROM $table LIMIT 1
                ";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute();
            if ($query->rowCount() > 0) {
                $data = $query->fetch(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Validator::getByMaxElement error: " . $e->getMessage());
        }

        return $data;
    }


    public function getAllByElements($table, $el1, $el2, $val1, $val2) // obtenir toutes les données d'une table
    {
        $data = [];
        try {
            $sql = " SELECT * FROM $table WHERE $el1 = ? AND $el2 = ?
                ";
            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$val1, $val2]);
            if ($query->rowCount() > 0) {
                $data = $query->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $e) {
            error_log("Validator::getAllByElements error: " . $e->getMessage());
        }

        return $data;
    }
    public static function validNumber($number, $limit) // return la date d'aujourd'hui
    {
        if (ctype_digit($number) && strlen($number) === $limit) {
            return true;
        } else {
            return false;
        }
    }
    public static function afficherImageBLOB($blob, $defaultImg, $typeMime = 'image/png')
    {
        if (empty($blob)) {
            return RACINE . $defaultImg;
        }
        return 'data:' . $typeMime . ';base64,' . base64_encode($blob);
    }

    public static function traiterImageUpload(string $champ = 'photo', int $tailleMax = 2 * 1024 * 1024): ?string
    {
        if (!isset($_FILES[$champ]) || $_FILES[$champ]['error'] !== UPLOAD_ERR_OK) {
            return null; // Pas de fichier ou erreur
        }

        $fichier = $_FILES[$champ];
        $nomTmp = $fichier['tmp_name'];
        $taille = $fichier['size'];
        $typeMime = mime_content_type($nomTmp); // vérifie vraiment le contenu du fichier

        // Extensions autorisées
        $extensionsAutorisees = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        // Vérification de la taille
        if ($taille > $tailleMax) {
            throw new RuntimeException("L'image dépasse la taille maximale autorisée de 2 Mo.");
        }

        // Vérification de l’extension MIME
        if (!array_key_exists($typeMime, $extensionsAutorisees)) {
            throw new RuntimeException("Format d'image non autorisé. Formats acceptés : JPG, PNG, WEBP.");
        }

        // Lecture du contenu binaire
        $binaire = file_get_contents($nomTmp);
        if ($binaire === false) {
            throw new RuntimeException("Erreur lors de la lecture de l'image.");
        }

        return $binaire; // prêt pour insertion en BDD
    }

    public static function dateActuelle($waveDate = null)
    {
        if ($waveDate) {
            // Si une date est fournie, on la convertit
            try {
                $dateTimeWave = new \DateTime($waveDate, new \DateTimeZone('UTC')); // Convertir UTC en DateTime
                $dateTimeWave->setTimezone(new \DateTimeZone('Africa/Abidjan'));   // Convertir en heure de Côte d'Ivoire

                return $dateTimeWave->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // En cas d'erreur de format de date
                return 'Invalid date: ' . $waveDate;
            }
        } else {
            // Sinon, renvoyer la date actuelle en Côte d'Ivoire
            $date = new \DateTime('now', new \DateTimeZone('Africa/Abidjan'));

            return $date->format('Y-m-d H:i:s');
        }
    }

    public function generateCode($table, $field, $prefixe, $length) // $password = $this->validator->generateCode("proprietaire", "password_pro","@", 6 );
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charactersLength = strlen($characters);
        $randomCode = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomCode .= $characters[rand(0, $charactersLength - 1)];
        }
        if ($this->verif($table, $field, $randomCode)) {
            return $this->generateCode($table, $field, $prefixe, $length); // Si le code est déjà utilisé, on réessaye
        }

        return $prefixe . $randomCode;
    }

    private $secretKey = 'TaCleSecrete';

    public function decrypter($encryptedData)
    {
        $key = hash('sha256', $this->secretKey);
        $base64Data = urldecode($encryptedData);
        $base64Data .= str_repeat('=', strlen($base64Data) % 4);  // Ajouter les caractères de remplissage nécessaires
        list($encryptedData, $iv, $hmac) = explode('::', base64_decode($base64Data), 3);
        if (hash_hmac('sha256', $encryptedData, $key) !== $hmac) {
            throw new Exception('HMAC verification failed.');
        }

        return openssl_decrypt($encryptedData, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Fonction pour traiter l'upload des fichiers (photos).
     *
     * @param array  $file           Le tableau de fichier `$_FILES['file']`.
     * @param string $destinationDir Le répertoire où le fichier sera stocké.
     * @param array  $allowedTypes   Tableau des types de fichiers autorisés (extensions).
     * @param int    $maxSize        Taille maximale du fichier (en octets).
     *
     * @return mixed Retourne un tableau avec le nom du fichier et le chemin si succès, ou un message d'erreur si échoué.
     */
    public function processUploadedFile($file, $destinationDir, $allowedTypes = [], $maxSize = 5000000)
    {
        // Vérifier s'il y a des erreurs d'upload
        if ($file['error'] !== 0) {
            return "Erreur lors de l'upload du fichier. Code d'erreur : " . $file['error'];
        }

        // Vérifier la taille du fichier
        if ($file['size'] > $maxSize) {
            return 'Le fichier est trop volumineux. La taille maximale autorisée est de ' . $maxSize / 1000000 . ' MB.';
        }

        // Vérifier le type de fichier (MIME)
        if (!in_array($file['type'], $allowedTypes)) {
            return 'Type de fichier non autorisé. Les types acceptés sont : ' . implode(', ', $allowedTypes);
        }

        // Obtenir l'extension du fichier
        $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Générer un nom unique pour le fichier (sans l'ancien nom)
        $fileName = uniqid() . '.' . $fileExt;
        $filePath = $destinationDir . '/' . $fileName;

        // Vérifier si le répertoire existe, sinon le créer
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        // Déplacer le fichier vers le répertoire de destination
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $filePath; // Retourne uniquement le nouveau nom du fichier
        } else {
            return 'Erreur lors du déplacement du fichier.';
        }
    }

    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }

        $data = trim($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return $data;
    }



    public static function validateRequiredFields(array $data, array $optionalFields = [])
    {
        $errors = [];

        foreach ($data as $key => $value) {
            // Vérifier si le champ est obligatoire et vide
            if (!in_array($key, $optionalFields) && empty(trim($value))) {
                $errors[$key] = "Le champ '$key' est requis et ne peut pas être vide.";
            }
        }

        return empty($errors) ? true : $errors;
    }

    public static function trColor($i)
    {
        $color = ($i % 2 == 0) ? '#ffffff' : '#D8D2D2FF'; // Alternance blanc  gris

        return $color;
    }

public static function legendStatus($iconActive, $text1, $iconInactive, $text2)
     {
         return '
         <div style="margin-bottom:-90px">
             <strong>Légende :</strong>
             <span class="badge" style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 10px;">
                 <i data-lucide="' . $iconActive . '"></i> ' . $text1 . '
             </span>
             <span class="badge" style="background-color: #dc3545; color: white; padding: 5px 10px; border-radius: 10px;">
                 <i data-lucide="' . $iconInactive . '"></i> ' . $text2 . '
             </span>
         </div>';
     }

public static function viewStatus($icon1, $icon2, $status, $nb)
     {
         $isActive = $status == $nb;
         $statut = $isActive ? '<i data-lucide="' . $icon1 . '"></i>' : '<i data-lucide="' . $icon2 . '"></i>';

         // Choisir la couleur du badge (en fonction de l'état) et forcer la couleur avec CSS
         $bgColor = $isActive ? '#28a745' : '#dc3545'; // Vert pour Actif, Rouge pour Inactif

         // Retourner le badge avec le style intégré (couleur de fond forcée, texte en blanc, bords arrondis)
         return "<span class='badge' style='font-size:10px; background-color: $bgColor; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold; text-transform: uppercase;'>$statut</span>";
     }


public static function viewStatus2($icon1, $icon2, $icon3, $status, $ref)
     {

         $isActive = $status;
         if ($isActive < $ref) {
             $statut = ' <span class="badge badge-warning"> <i data-lucide="' . $icon1 . '"></i> En cours</span>';
         }
         if ($isActive > $ref) {
             $statut = ' <span class="badge badge-danger"><i data-lucide="' . $icon2 . '"> </i> Echec</span> ';
         }
         if ($isActive == $ref) {
             $statut = '  <span class="badge badge-success"><i data-lucide="' . $icon3 . '"> </i> Succès</span>';
         }

         // Retourner le badge avec le style intégré (couleur de fond forcée, texte en blanc, bords arrondis)
         return "$statut";
     }

    public static function viewMode($status, $val1, $val2)
    {
        // Si status1 = 1, afficher "Wave", sinon "Expresse"
        if ($status == $val1) {
            $statut = 'WAVE';
        } elseif ($status == $val2) {
            $statut = 'ESPECE ';
        } else {
            $statut = 'Aucun';
        }
        // Retourner le badge stylisé
        return $statut;
    }

    public static function icon($icon)
    {
        $result = '<i data-lucide="' . $icon . '"></i>';

        return $result;
    }

    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(?string $token): bool
    {
        if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        if (!isset($_SESSION['csrf_token_time']) || (time() - $_SESSION['csrf_token_time']) > 3600) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::generateCsrfToken()) . '">';
    }
    public function getCountByElements($table, $el1, $el2, $val1, $val2) // returns le nbr de client
    {
        $result = 0;
        try {
            $sql = "SELECT COUNT(*) total FROM $table ar WHERE ar.$el1 = ? AND ar.$el2 = ? ";

            $query = $this->pdo->getCon()->prepare($sql);

            $query->execute([$val1, $val2]);

            if ($query->rowcount() > 0) {
                $result = $query->fetch(PDO::FETCH_ASSOC);

                return $result['total'];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            error_log("Validator::getCountByElements error: " . $e->getMessage());
            return null;
        }
    }


    public function getCountByJoinElement($table1, $table2, $joinCondition, $element1, $element2, $value1, $value2)
    {
        try {
            $sql = "SELECT COUNT(*) AS total
                FROM $table1 t1
                INNER JOIN $table2 t2 ON $joinCondition
                WHERE $element1 = ? AND $element2 = ?";

            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$value1, $value2]);

            $result = $query->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['total'] : 0;
        } catch (\Exception $e) {
            error_log("Validator::getCountByJoinElement error: " . $e->getMessage());
            return 0;
        }
    }
    public function getByJoinElement($table1, $table2, $joinCondition, $element1, $element2, $value1, $value2)
    {
        try {
            $sql = "SELECT * 
                FROM $table1 t1
                INNER JOIN $table2 t2 ON $joinCondition
                WHERE $element1 = ? AND $element2 = ?";

            $query = $this->pdo->getCon()->prepare($sql);
            $query->execute([$value1, $value2]);

            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            return !empty($result) ? $result : null;
        } catch (\Exception $e) {
            error_log("Validator::getByJoinElement error: " . $e->getMessage());
            return null;
        }
    }

    public function getCountByElement($table, $el1, $el2, $val1, $val2) // returns le nbr de client
    {
        $result = 0;
        try {
            $sql = "SELECT COUNT(*) total FROM $table ar WHERE ar.$el1 = ? AND ar.$el2 = ? ";

            $query = $this->pdo->getCon()->prepare($sql);

            $query->execute([$val1, $val2]);

            if ($query->rowcount() > 0) {
                $result = $query->fetch(PDO::FETCH_ASSOC);

                return $result['total'];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            error_log("Validator::getCountByElement error: " . $e->getMessage());
            return null;
        }
    }
    public function getCountTable($table) // returns le nbr de client
    {
        $result = 0;
        try {
            $sql = "SELECT COUNT(*) total FROM $table ";

            $query = $this->pdo->getCon()->prepare($sql);

            $query->execute();

            if ($query->rowcount() > 0) {
                $result = $query->fetch(PDO::FETCH_ASSOC);

                return $result['total'];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            error_log("Validator::getCountTable error: " . $e->getMessage());
            return null;
        }
    }

    public static function ficon($icon)
    {
        $result = '<i class="feather icon-' . $icon . '"></i>';

        return $result;
    }

    public static function truncateText($text, $length = 10)
    {
        $safeText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        if (strlen($text) > $length) {
            return '<td class="truncate-text" title="' . $safeText . '" onclick="showFullText(this)">'
                . htmlspecialchars(substr($text, 0, $length)) . '...</td>';
        }

        return "<td>$safeText</td>";
    }

    public function updateByElements($table, $element1, $element_val1, $element2, $element_val2, $id, $id_val) // update by element1
    {
        try {
            $sql = "UPDATE $table SET $element1=? WHERE $element2=?  AND $id=?";
            $query = $this->pdo->getCon()->prepare($sql);
            if ($query->execute([$element_val1, $element_val2, $id_val])) {
                return true;
            }
        } catch (Exception $e) {
            error_log("Validator::updateByElements error: " . $e->getMessage());
        }

        return false;
    }

    // checkoutwave
    public function checkOutWave($montant)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.wave.com/v1/checkout/sessions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'amount' => $montant,
                'currency' => 'XOF',
                'error_url' => RACINE . 'church/home/error',
                'success_url' => RACINE . 'church/home/success',
            ]),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer wave_ci_prod_QPqtloWqepC-bbSz_GGTKlBm8vgopNcgBv7-r0hCW-9UlxYAHVECdTRU5TLweboyGdQ_ZS3G_0HYNiYEhIh9lRTs_I6JpZNXxg',
                'Content-Type: application/json; charset=UTF-8',
            ],
        ]);

        // Exécution de la requête cURL
        $response = curl_exec($curl);

        // Vérification des erreurs cURL
        if ($response === false) {
            // Affichage de l'erreur si cURL échoue
            // var_dump(curl_error($curl));
            curl_close($curl);

            return false;  // Retourner false en cas d'erreur cURL
        }

        // Fermeture de la session cURL
        curl_close($curl);

        // Décoder la réponse JSON de l'API
        $responseData = json_decode($response, true);

        // Vérifier si la réponse est valide
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Erreur lors du décodage JSON: ' . json_last_error_msg();

            return false;  // Retourner false si JSON est mal formé
        }

        // Retourner la réponse décodée (c'est ici que vous récupérez les données)
        return $responseData;
    }

    public function crypter($data)
    {
        $key = hash('sha256', $this->secretKey);
        $iv = substr(hash('sha256', openssl_random_pseudo_bytes(16)), 0, 16);
        $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        $hmac = hash_hmac('sha256', $encryptedData, $key);
        $base64Data = base64_encode($encryptedData . '::' . $iv . '::' . $hmac);

        return urlencode(rtrim($base64Data, '='));
    }

    public static function dateHier() // la date d'aujourd'hui
    {
        $date = date('Y-m-d');

        return date('Y-m-d', strtotime('-1 day', strtotime($date))); // Soustrait un jour
    }

    public static function dateActuelleCourt() // la date d'aujourd'hui
    {
        $date = new \DateTime();

        return $date->format('Y-m-d');
    }

    public static function dateToInteger($dateToconvert) // convert date to integer
    {
        $date = new DateTime($dateToconvert);

        return $date->getTimestamp();
    }

    public static function integerToDate($integerToconvert) // convert un entier en date
    {
        if ($integerToconvert) {
            // Convertir l'entier en objet DateTime
            $date = new DateTime();
            $date->setTimestamp((int) $integerToconvert);

            return $date->format('Y-m-d'); // returner la date sous le format souhaité
        }
    }

    public function isValidMoney($amount, $amountMax) // verifier amount
    {
        if (preg_match('/^[0-9]+$/', $amount) && intval($amount) >= $amountMax && intval($amount) % $amountMax === 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function calculerDateFin($dateDebut, $nombreMois)
    {
        // Vérifier si la date de début et le nombre de mois sont valides
        if (!empty($dateDebut) && is_numeric($nombreMois) && $nombreMois > 0) {
            // Créer un objet DateTime avec la date de début
            $date = new DateTime($dateDebut);

            // Ajouter le nombre de mois spécifié
            $date->modify("+{$nombreMois} months");

            // Retourner la date de fin au format YYYY-MM-DD
            return $date->format('Y-m-d');
        }

        return null; // Retourner null si les entrées ne sont pas valides
    }


    public static function formatDate($date)
    {
        // Vérifier si la date est valide
        if (!$date || $date == '0000-00-00') {
            return 'Date invalide';
        }

        // Convertir la date en objet DateTime
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);

        // Vérifier si la conversion a réussi
        if (!$dateObj) {
            return 'Format incorrect';
        }

        // Retourner la date formatée en "jj-mm-aaaa"
        return $dateObj->format('d-m-Y');
    }

    public static function  isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    public static function isConnected(): bool
    {
        return isset($_SESSION[USERS_AUTH]['code_user'])
            && !empty($_SESSION[USERS_AUTH]['code_user']);
    }
    // Méthode pour hasher un mot de passe
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Méthode pour vérifier un mot de passe avec son hash
    public static function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

    public function getSumAndCount2($table, $id, $id_val, $status, $status_val, $etat, $etat_val, $el) // returns des apparts d'un proprio
    {
        try {
            $sql = "SELECT SUM($el) somme, COUNT($el) count FROM $table v
            WHERE v.$id = ? AND v.$status = ? AND v.$etat = ?";

            $query = $this->pdo->getCon()->prepare($sql);

            $query->execute([$id_val, $status_val, $etat_val]);

            if ($query->rowcount() > 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            } else {
                return null;
            }
        } catch (\Exception $e) {
            error_log("Validator::getSumAndCount2 error: " . $e->getMessage());
            return null;
        }
    }
    public function getSumAndCount($table, $id, $id_val, $status, $status_val, $el) // returns des apparts d'un propio
    {
        try {
            $sql = "SELECT SUM($el) somme, COUNT($el) count FROM $table v
            WHERE v.$id = ? AND v.$status = ?";

            $query = $this->pdo->getCon()->prepare($sql);

            $query->execute([$id_val, $status_val]);

            if ($query->rowcount() > 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            } else {
                return null;
            }
        } catch (\Exception $e) {
            error_log("Validator::getSumAndCount error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valider les données d'une commande
     * @param array $data Données de la commande à valider
     * @return array Tableau des erreurs de validation (vide si aucune erreur)
     */
    public function validateCommandeData($data)
    {
        $errors = [];

        // Valider le code commande (obligatoire pour création)
        if (isset($data['code_commande'])) {
            if (empty(trim($data['code_commande']))) {
                $errors['code_commande'] = "Le code de commande est requis";
            } elseif (!preg_match('/^CMD\d{8}\d{4}$/', $data['code_commande'])) {
                $errors['code_commande'] = "Le format du code de commande est invalide";
            }
        }

        // Valider l'ID client (obligatoire pour création)
        if (isset($data['client_id'])) {
            if (empty(trim($data['client_id'])) || !is_numeric($data['client_id'])) {
                $errors['client_id'] = "Un client valide doit être sélectionné";
            }
        }

        // Valider le total (obligatoire)
        if (isset($data['total'])) {
            if (!is_numeric($data['total']) || floatval($data['total']) <= 0) {
                $errors['total'] = "Le total doit être un nombre positif";
            }
        }

        // Valider les frais de livraison
        if (isset($data['frais_livraison'])) {
            if (!is_numeric($data['frais_livraison']) || floatval($data['frais_livraison']) < 0) {
                $errors['frais_livraison'] = "Les frais de livraison doivent être un nombre positif ou zéro";
            }
        }

        // Valider le statut
        if (isset($data['statut'])) {
            $statuts_valides = ['reçue', 'en préparation', 'prête', 'en route', 'livrée', 'annulée'];
            if (!in_array($data['statut'], $statuts_valides)) {
                $errors['statut'] = "Le statut sélectionné n'est pas valide";
            }
        }

        // Valider la méthode de paiement
        if (isset($data['paiement'])) {
            $paiements_valides = ['à la livraison', 'wave', 'espèces', 'carte bancaire'];
            if (!in_array($data['paiement'], $paiements_valides)) {
                $errors['paiement'] = "La méthode de paiement sélectionnée n'est pas valide";
            }
        }

        // Valider l'adresse de livraison (obligatoire)
        if (isset($data['adresse_livraison'])) {
            if (empty(trim($data['adresse_livraison']))) {
                $errors['adresse_livraison'] = "L'adresse de livraison est requise";
            } elseif (strlen($data['adresse_livraison']) < 10) {
                $errors['adresse_livraison'] = "L'adresse de livraison doit contenir au moins 10 caractères";
            }
        }

        // Valider les instructions (optionnel mais limité en longueur)
        if (isset($data['instructions'])) {
            if (strlen($data['instructions']) > 500) {
                $errors['instructions'] = "Les instructions ne peuvent pas dépasser 500 caractères";
            }
        }

        return $errors;
    }

    /**
     * Valider les données d'un client
     * @param array $data Données du client à valider
     * @return array Tableau des erreurs de validation (vide si aucune erreur)
     */
    public function validateClientData($data)
    {
        $errors = [];

        // Valider le nom (obligatoire)
        if (isset($data['nom'])) {
            if (empty(trim($data['nom']))) {
                $errors['nom'] = "Le nom est requis";
            } elseif (strlen($data['nom']) < 2) {
                $errors['nom'] = "Le nom doit contenir au moins 2 caractères";
            }
        }

        // Valider le téléphone
        if (isset($data['telephone'])) {
            if (empty(trim($data['telephone']))) {
                $errors['telephone'] = "Le téléphone est requis";
            } elseif (!preg_match('/^(\+225|0)[0-9]{9}$/', str_replace(' ', '', $data['telephone']))) {
                $errors['telephone'] = "Le format du téléphone n'est pas valide (ex: 0123456789)";
            }
        }

        // Valider l'adresse
        if (isset($data['adresse'])) {
            if (empty(trim($data['adresse']))) {
                $errors['adresse'] = "L'adresse est requise";
            } elseif (strlen($data['adresse']) < 10) {
                $errors['adresse'] = "L'adresse doit contenir au moins 10 caractères";
            }
        }

        // Valider l'email
        if (isset($data['email'])) {
            if (empty(trim($data['email']))) {
                $errors['email'] = "L'email est requis";
            } elseif (!self::isValidEmail($data['email'])) {
                $errors['email'] = "Le format de l'email n'est pas valide";
            }
        }

        // Valider le mot de passe (pour l'inscription/modification)
        if (isset($data['password'])) {
            if (empty(trim($data['password']))) {
                $errors['password'] = "Le mot de passe est requis";
            } elseif (strlen($data['password']) < 6) {
                $errors['password'] = "Le mot de passe doit contenir au moins 6 caractères";
            }
        }

        return $errors;
    }

public static function saveSesion(string $entity, $data){
        if (is_array($data)) {
            if(sizeof($data) > 0 && !empty($entity)){
                foreach ($data as $key => $value) {
                    $_SESSION[$entity][$key]= $value;
                }
            }
        }else{
            $_SESSION[$entity]= $data;
        }
    }

    public function insert(string $table, array $data, array $keys): bool
    {
        try {
            $columns = implode(', ', $keys);
            $placeholders = implode(', ', array_map(fn($k) => ":$k", $keys));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->getCon()->prepare($sql);
            return $stmt->execute($data);
        } catch (Exception $e) {
            error_log("Insert error: " . $e->getMessage());
            return false;
        }
    }

    public function update(string $table, array $data, array $keys, string $idField, int $id): bool
    {
        try {
            $setClause = implode(', ', array_map(fn($k) => "$k = :$k", $keys));
            $sql = "UPDATE $table SET $setClause WHERE $idField = :id";
            $stmt = $this->pdo->getCon()->prepare($sql);
            $data['id'] = $id;
            return $stmt->execute($data);
        } catch (Exception $e) {
            error_log("Update error: " . $e->getMessage());
            return false;
        }
    }

    public static function getSesion(string $entity,string $field="")
    {
        if(!empty($field) && !empty($entity)){
            if (key_exists($field,$_SESSION[$entity])) {
                return $_SESSION[$entity][$field];
            }else{
                if (key_exists($entity,$_SESSION)) {
                    return $_SESSION[$entity];
                }
            }
        }
    }

}

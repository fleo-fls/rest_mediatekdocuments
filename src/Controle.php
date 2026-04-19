<?php
header('Content-Type: application/json; charset=utf-8');

include_once("MyAccessBDD.php");

/**
 * Contrôleur : reçoit et traite les demandes du point d'entrée
 */
class Controle {

    /**
     * @var MyAccessBDD
     */
    private $myAaccessBDD;

    /**
     * constructeur : récupère l'instance d'accès à la BDD
     */
    public function __construct() {
        try {
            $this->myAaccessBDD = new MyAccessBDD();
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $this->reponse(500, "erreur serveur");
            exit;
        }
    }

    /**
     * réception d'une demande de requête
     * @param string $methodeHTTP
     * @param string|null $table
     * @param string|null $id
     * @param array|null $champs
     */
    public function demande($methodeHTTP, $table, $id, $champs) {
        try {
            $result = $this->myAaccessBDD->demande($methodeHTTP, $table, $id, $champs);
            $this->controleResult($result);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            $this->reponse(500, "erreur serveur");
            exit;
        }
    }

    /**
     * réponse renvoyée au client au format json
     * @param int $code
     * @param string $message
     * @param array|int|string|null $result
     */
    private function reponse(int $code, string $message, array|int|string|null $result = "") {
        $retour = [
            'code' => $code,
            'message' => $message,
            'result' => $result
        ];
        echo json_encode($retour, JSON_UNESCAPED_UNICODE);
    }

    /**
     * contrôle si le résultat n'est pas null
     * @param array|int|null $result
     */
    private function controleResult(array|int|null $result) {
        if (!is_null($result)) {
            $this->reponse(200, "OK", $result);
        } else {
            $this->reponse(400, "requete invalide");
        }
    }

    /**
     * authentification incorrecte
     */
    public function unauthorized() {
        $this->reponse(401, "authentification incorrecte");
    }
}
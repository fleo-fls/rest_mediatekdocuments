<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "" :
                // return $this->uneFonction(parametres);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
    switch($table){
        case "livre":
            return $this->insertLivre($champs);
        case "dvd":
            return $this->insertDvd($champs);
        case "revue":
            return $this->insertRevue($champs);
        default:
            return $this->insertOneTupleOneTable($table, $champs);
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "livre":
                return $this->updateLivre($id, $champs);
            case "dvd":
                return $this->updateDvd($id, $champs);
            case "revue":
                return $this->updateRevue($id, $champs);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "livre" :
                return $this->deleteLivre($champs);
            case "dvd" :
                return $this->deleteDvd($champs);
            case "revue":
                return $this->deleteRevue($champs);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }	
    /**
     * insert un livre dans la BDD
     * @param array|null $champs
     * @return int|null
     */
    private function insertLivre(?array $champs) : ?int
    {
    if (empty($champs)) {
        return null;
    }

    $champsDocument = [
        "id" => $champs["id"],
        "titre" => $champs["titre"],
        "image" => $champs["image"],
        "idGenre" => $champs["idGenre"],
        "idPublic" => $champs["idPublic"],
        "idRayon" => $champs["idRayon"]
    ];

    $requeteDocument = "insert into document (id, titre, image, idGenre, idPublic, idRayon)
                        values (:id, :titre, :image, :idGenre, :idPublic, :idRayon);";

    $resultDocument = $this->conn->updateBDD($requeteDocument, $champsDocument);

    if ($resultDocument === null || $resultDocument == 0) {
        return null;
    }

    // insertion dans livres_dvd
    $champsLivresDvd = [
        "id" => $champs["id"]
    ];

    $requeteLivresDvd = "insert into livres_dvd (id)
                         values (:id);";

    $resultLivresDvd = $this->conn->updateBDD($requeteLivresDvd, $champsLivresDvd);

    if ($resultLivresDvd === null || $resultLivresDvd == 0) {
        return null;
    }
    $champsLivre = [
        "id" => $champs["id"],
        "ISBN" => $champs["ISBN"],
        "auteur" => $champs["auteur"],
        "collection" => $champs["collection"]
    ];

    $requeteLivre = "insert into livre (id, ISBN, auteur, collection)
                     values (:id, :ISBN, :auteur, :collection);";

    $resultLivre = $this->conn->updateBDD($requeteLivre, $champsLivre);

    if ($resultLivre === null || $resultLivre == 0) {
        return null;
    }

    return 1;
    }
    
    /**
     * Modification d'un Livre dans la BDD
     * @param string|null $id
     * @param array|null $champs
     * @return int|null
     */
    private function updateLivre(?string $id, ?array $champs) : ?int
    {
    if (is_null($id) || empty($champs)) {
        return null;
    }

    $champsDocument = [
        "titre" => $champs["titre"],
        "image" => $champs["image"],
        "idGenre" => $champs["idGenre"],
        "idPublic" => $champs["idPublic"],
        "idRayon" => $champs["idRayon"],
        "id" => $id
    ];

    $requeteDocument = "update document 
                        set titre = :titre,
                            image = :image,
                            idGenre = :idGenre,
                            idPublic = :idPublic,
                            idRayon = :idRayon
                        where id = :id;";

    $resultDocument = $this->conn->updateBDD($requeteDocument, $champsDocument);

    $champsLivre = [
        "ISBN" => $champs["ISBN"],
        "auteur" => $champs["auteur"],
        "collection" => $champs["collection"],
        "id" => $id
    ];

    $requeteLivre = "update livre
                     set ISBN = :ISBN,
                         auteur = :auteur,
                         collection = :collection
                     where id = :id;";

    $resultLivre = $this->conn->updateBDD($requeteLivre, $champsLivre);

    if ($resultDocument === null || $resultLivre === null) {
        return null;
    }

    return 1;
    }
    
    /**
     * Suppression d'un livre dans la BDD
     * @param array|null $champs
     * @return int|null
     */
    private function deleteLivre(?array $champs) : ?int
    {
    if (empty($champs)) {
        return null;
    }

    if (!array_key_exists("id", $champs) || empty($champs["id"])) {
        return null;
    }

    $params = ["id" => $champs["id"]];

    $requeteLivre = "delete from livre where id = :id;";
    $resultLivre = $this->conn->updateBDD($requeteLivre, $params);
    if ($resultLivre === null || $resultLivre == 0) {
        return null;
    }

    $requeteLivresDvd = "delete from livres_dvd where id = :id;";
    $resultLivresDvd = $this->conn->updateBDD($requeteLivresDvd, $params);
    if ($resultLivresDvd === null || $resultLivresDvd == 0) {
        return null;
    }

    $requeteDocument = "delete from document where id = :id;";
    $resultDocument = $this->conn->updateBDD($requeteDocument, $params);
    if ($resultDocument === null || $resultDocument == 0) {
        return null;
    }

    return 1;
    }
    
    /**
     * Ajout d'un DVD dans la BDD
     * @param array|null $champs
     * @return int|null
     */
    private function insertDvd(?array $champs) : ?int
    {
    if (empty($champs)) {
        return null;
    }

    $champsDocument = [
        "id" => $champs["id"],
        "titre" => $champs["titre"],
        "image" => $champs["image"],
        "idGenre" => $champs["idGenre"],
        "idPublic" => $champs["idPublic"],
        "idRayon" => $champs["idRayon"]
    ];

    $requeteDocument = "insert into document (id, titre, image, idGenre, idPublic, idRayon)
                        values (:id, :titre, :image, :idGenre, :idPublic, :idRayon);";

    $resultDocument = $this->conn->updateBDD($requeteDocument, $champsDocument);

    if ($resultDocument === null || $resultDocument == 0) {
        return null;
    }

    $champsLivresDvd = [
        "id" => $champs["id"]
    ];

    $requeteLivresDvd = "insert into livres_dvd (id)
                         values (:id);";

    $resultLivresDvd = $this->conn->updateBDD($requeteLivresDvd, $champsLivresDvd);

    if ($resultLivresDvd === null || $resultLivresDvd == 0) {
        return null;
    }

    $champsDvd = [
        "id" => $champs["id"],
        "duree" => $champs["duree"],
        "realisateur" => $champs["realisateur"],
        "synopsis" => $champs["synopsis"]
    ];

    $requeteDvd = "insert into dvd (id, duree, realisateur, synopsis)
                   values (:id, :duree, :realisateur, :synopsis);";

    $resultDvd = $this->conn->updateBDD($requeteDvd, $champsDvd);

    if ($resultDvd === null || $resultDvd == 0) {
        return null;
    }

    return 1;
    }
    
    /**
     * Modification d'un DVD dans la BDD
     * @param string|null $id
     * @param array|null $champs
     * @return int|null
     */
    private function updateDvd(?string $id, ?array $champs) : ?int
    {
    if (is_null($id) || empty($champs)) {
        return null;
    }

    $champsDocument = [
        "titre" => $champs["titre"],
        "image" => $champs["image"],
        "idGenre" => $champs["idGenre"],
        "idPublic" => $champs["idPublic"],
        "idRayon" => $champs["idRayon"],
        "id" => $id
    ];

    $requeteDocument = "update document 
                        set titre = :titre,
                            image = :image,
                            idGenre = :idGenre,
                            idPublic = :idPublic,
                            idRayon = :idRayon
                        where id = :id;";

    $resultDocument = $this->conn->updateBDD($requeteDocument, $champsDocument);

    $champsDvd = [
        "duree" => $champs["duree"],
        "realisateur" => $champs["realisateur"],
        "synopsis" => $champs["synopsis"],
        "id" => $id
    ];

    $requeteDvd = "update dvd
                   set duree = :duree,
                       realisateur = :realisateur,
                       synopsis = :synopsis
                   where id = :id;";

    $resultDvd = $this->conn->updateBDD($requeteDvd, $champsDvd);

    if ($resultDocument === null || $resultDvd === null) {
        return null;
    }

    return 1;
    }
    
    /**
     * suppression d'un DVD dans la BDD
     * @param array|null $champs
     * @return int|null
     */
     private function deleteDvd(?array $champs) : ?int
    {
    if (empty($champs)) {
        return null;
    }

    if (!array_key_exists("id", $champs) || empty($champs["id"])) {
        return null;
    }

    $params = ["id" => $champs["id"]];

    $requeteDvd = "delete from dvd where id = :id;";
    $resultDvd = $this->conn->updateBDD($requeteDvd, $params);
    if ($resultDvd === null || $resultDvd == 0) {
        return null;
    }

    $requeteLivresDvd = "delete from livres_dvd where id = :id;";
    $resultLivresDvd = $this->conn->updateBDD($requeteLivresDvd, $params);
    if ($resultLivresDvd === null || $resultLivresDvd == 0) {
        return null;
    }

    $requeteDocument = "delete from document where id = :id;";
    $resultDocument = $this->conn->updateBDD($requeteDocument, $params);
    if ($resultDocument === null || $resultDocument == 0) {
        return null;
    }

    return 1;
    }
    
 
    /**
     * Ajout d'une revue dans la BDD
     * @param array|null $champs
     * @return int|null
     */
    private function insertRevue(?array $champs) : ?int
    {
    if (empty($champs)) {
        return null;
    }
    
    $champsDocument = [
        "id" => $champs["id"],
        "titre" => $champs["titre"],
        "image" => $champs["image"],
        "idGenre" => $champs["idGenre"],
        "idPublic" => $champs["idPublic"],
        "idRayon" => $champs["idRayon"]
    ];
    $requeteDocument = "insert into document (id, titre, image, idGenre, idPublic, idRayon)
                        values (:id, :titre, :image, :idGenre, :idPublic, :idRayon);";

    $resultDocument = $this->conn->updateBDD($requeteDocument, $champsDocument);

    if ($resultDocument === null || $resultDocument == 0) {
        return null;
    }
    // 2. TABLE revue
    $champsRevue = [
        "id" => $champs["id"],
        "periodicite" => $champs["periodicite"],
        "delaiMiseADispo" => $champs["delaiMiseADispo"]
    ];
    $requeteRevue = "insert into revue (id, periodicite, delaiMiseADispo)
                     values (:id, :periodicite, :delaiMiseADispo);";
    $resultRevue = $this->conn->updateBDD($requeteRevue, $champsRevue);
    if ($resultRevue === null || $resultRevue == 0) {
        return null;
    }
    return 1;
    }
    
    /**
    * Modification d'une revue dans la BDD
    * @param string|null $id
    * @param array|null $champs
    * @return int|null
    */
    private function updateRevue(?string $id, ?array $champs) : ?int
    {
    if (is_null($id) || empty($champs)) {
        return null;
    }
    // 1. TABLE document
    $champsDocument = [
        "titre" => $champs["titre"],
        "image" => $champs["image"],
        "idGenre" => $champs["idGenre"],
        "idPublic" => $champs["idPublic"],
        "idRayon" => $champs["idRayon"],
        "id" => $id
    ];
    $requeteDocument = "update document
                        set titre = :titre,
                            image = :image,
                            idGenre = :idGenre,
                            idPublic = :idPublic,
                            idRayon = :idRayon
                        where id = :id;";

    $resultDocument = $this->conn->updateBDD($requeteDocument, $champsDocument);
    // 2. TABLE revue
    $champsRevue = [
        "periodicite" => $champs["periodicite"],
        "delaiMiseADispo" => $champs["delaiMiseADispo"],
        "id" => $id
    ];
    $requeteRevue = "update revue
                     set periodicite = :periodicite,
                         delaiMiseADispo = :delaiMiseADispo
                     where id = :id;";

    $resultRevue = $this->conn->updateBDD($requeteRevue, $champsRevue);
    if ($resultDocument === null || $resultRevue === null) {
        return null;
    }
    return 1;
    }  
    
    /**
     * suppression d'une revue dans la BDD
     * @param array|null $champs
     * @return int|null
     */
    
    public function deleteRevue(?array $champs) : ?int
    {
    if (empty($champs)) {
        return null;
    }

    if (!array_key_exists("id", $champs) || empty($champs["id"])) {
        return null;
    }

    $params = ["id" => $champs["id"]];

    $requeteRevue = "delete from revue where id = :id;";
    $resultRevue = $this->conn->updateBDD($requeteRevue, $params);
    if ($resultRevue === null || $resultRevue == 0) {
        return null;
    }

    $requeteDocument = "delete from document where id = :id;";
    $resultDocument = $this->conn->updateBDD($requeteDocument, $params);
    if ($resultDocument === null || $resultDocument == 0) {
        return null;
    }

    return 1;
    }
}

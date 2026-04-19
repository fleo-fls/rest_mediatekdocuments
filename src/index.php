<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

/*
 * Index.php : point d'entrée de l'API
 * - contrôle l'authentification
 * - récupère les variables envoyées
 * - récupère la méthode HTTP
 * - demande au contrôleur de gérer la demande
 */
include_once("Url.php");
include_once("Controle.php");

$url = Url::getInstance();
$controle = new Controle();

if (!$url->authentification()){
    $controle->unauthorized();
} else {
    $methodeHTTP = $url->recupMethodeHTTP();
    $table = $url->recupVariable("table");
    $id = $url->recupVariable("id");
    $champs = $url->recupVariable("champs", "json");

    $controle->demande($methodeHTTP, $table, $id, $champs);
}
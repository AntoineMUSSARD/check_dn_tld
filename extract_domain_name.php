// Function that return the domain name from an URL
// Handle 1rst level and 2nd level TLD.
// 🅰🅽🆃🅾🅸🅽🅴 🅼🆄🆂🆂🅰🆁🅳 / 🆅🆁🅳🅲🅸
// v0.9 2021-07
// v1.0 2025-27
<?php
/**
 * Vérifie si un TLD ou un nom de domaine existe via DNS.
 *
 * @param string $name Le TLD (ex : "com", "fr") ou le NDD complet (ex : "example.com")
 * @param string $type "tld" pour tester un TLD, "domain" pour tester un nom de domaine complet
 * @return bool true si l’entité existe, false sinon
 */
function dnsExiste(string $name, string $type = 'tld'): bool
{
    // Nettoyage de base
    $name = trim($name, ". \t\n\r\0\x0B");
    
    // Pour les IDN, conversion vers ASCII (Punycode)
    if (function_exists('idn_to_ascii')) {
        $name = idn_to_ascii($name, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    }
    
    // Si on veut tester un TLD, on ajoute le point final pour interroger la racine
    if ($type === 'tld') {
        $host = $name . '.';
        // On cherche un enregistrement NS
        if (function_exists('checkdnsrr')) {
            return checkdnsrr($host, 'NS');
        }
        if (function_exists('dns_get_record')) {
            $records = dns_get_record($host, DNS_NS);
            return !empty($records);
        }
    }

    // Si on veut tester un nom de domaine complet
    if ($type === 'domain') {
        $host = $name;
        // On considère qu’un domaine existe s’il possède un A ou un MX
        if (function_exists('checkdnsrr')) {
            return checkdnsrr($host, 'A') || checkdnsrr($host, 'MX');
        }
        if (function_exists('dns_get_record')) {
            $a = dns_get_record($host, DNS_A);
            $mx = dns_get_record($host, DNS_MX);
            return !empty($a) || !empty($mx);
        }
    }

    // Type inconnu ou fonctions DNS indisponibles
    return false;
}

// Exemples d’utilisation :
var_dump(dnsExiste('discount', 'tld'));          // true
var_dump(dnsExiste('inexistant', 'tld'));   // false
var_dump(dnsExiste('azy.discount', 'domain')); // true ou false selon disponibilité
var_dump(dnsExiste('inf.so', 'domain')); // true ou false selon disponibilité
var_dump(dnsExiste('vrd.ci', 'domain')); // true ou false selon disponibilité
var_dump(dnsExiste('vegtgrserthgsetrhg.discount', 'domain'));  // généralement false

<?php
namespace Gfc;

/**
 * Reponses JSON de l'API, conformes a specs/001-plateforme-gfc/contracts/api.md.
 *
 * Les messages d'erreur sont en francais : ils sont affiches tels quels par
 * l'application mobile (FR-029).
 */
final class Response
{
    /** Duree de cache par defaut des lectures stables, en secondes. */
    private const CACHE_LECTURE = 60;

    /**
     * @param int $maxAge duree de cache en secondes ; 0 pour no-cache
     *                    (obligatoire sur les ressources d'un match en direct)
     */
    public static function json(mixed $data, int $status = 200, int $maxAge = self::CACHE_LECTURE): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header($maxAge > 0
            ? 'Cache-Control: public, max-age=' . $maxAge
            : 'Cache-Control: no-cache');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Reponse d'un match en direct : jamais mise en cache. */
    public static function live(mixed $data, int $status = 200): never
    {
        self::json($data, $status, 0);
    }

    /**
     * Erreur au format du contrat : {"error": {"code": "...", "message": "..."}}
     *
     * @param string $code identifiant stable, que le client peut tester sans
     *                     dependre du libelle affiche
     */
    public static function error(string $message, int $status = 400, string $code = ''): never
    {
        if ($code === '') {
            $code = match ($status) {
                400     => 'requete_invalide',
                401     => 'non_authentifie',
                403     => 'acces_refuse',
                404     => 'introuvable',
                413     => 'fichier_trop_volumineux',
                422     => 'donnees_refusees',
                default => 'erreur_serveur',
            };
        }

        self::json(['error' => ['code' => $code, 'message' => $message]], $status, 0);
    }
}

<?php
declare(strict_types=1);

namespace Gfc\Core;

/**
 * CRUD generique pour le back office. Toutes les operations passent par POST
 * (l hebergement bloque PUT/DELETE) avec un champ _action = create|update|delete.
 */
final class Crud
{
    /**
     * @param array  $fields   spec des champs [['name'=>,'type'=>,'nullable'=>,'default'=>], ...]
     * @param array  $defaults valeurs serveur forcees a la creation (edition_id, etc.)
     */
    public static function handle(
        Request $req, Database $db, Auth $auth,
        string $table, array $fields, string $redirect, array $defaults = []
    ): never {
        $user   = $auth->user($req);
        $action = $req->str('_action');
        $id     = (int) ($req->int('id') ?? 0);

        try {
            if ($action === 'create') {
                $data = self::collect($req, $fields, false) + $defaults;
                self::autoSlug($fields, $data);
                $newId = $db->insert($table, $data);
                $auth->log($user['id'] ?? null, $table . '.create', $table, $newId);
            } elseif ($action === 'update' && $id > 0) {
                $data = self::collect($req, $fields, true);
                if ($data !== []) {
                    self::autoSlug($fields, $data);
                    $db->update($table, $id, $data);
                }
                $auth->log($user['id'] ?? null, $table . '.update', $table, $id);
            } elseif ($action === 'delete' && $id > 0) {
                $db->run("DELETE FROM {$table} WHERE id = ?", [$id]);
                $auth->log($user['id'] ?? null, $table . '.delete', $table, $id);
            }
        } catch (\Throwable $e) {
            Response::redirect($redirect . (str_contains($redirect, '?') ? '&' : '?') . 'err=' . urlencode($e->getMessage()));
        }

        Response::redirect($redirect . (str_contains($redirect, '?') ? '&' : '?') . 'ok=1');
    }

    /** @return array<string,mixed> */
    private static function collect(Request $req, array $fields, bool $isUpdate): array
    {
        $data = [];
        foreach ($fields as $f) {
            $name = $f['name'];
            $type = $f['type'] ?? 'text';

            if ($type === 'password') {
                $v = $req->str($name);
                if ($v !== '') {
                    $data['password_hash'] = password_hash($v, PASSWORD_BCRYPT);
                }
                continue;
            }

            $raw = $req->input($name);
            if ($type === 'checkbox') {
                $data[$name] = $raw ? 1 : 0;
                continue;
            }
            if ($raw === null) {
                if (!$isUpdate && array_key_exists('default', $f)) {
                    $data[$name] = $f['default'];
                }
                continue;
            }
            $v = is_string($raw) ? trim($raw) : $raw;
            if ($v === '' && !empty($f['nullable'])) {
                $v = null;
            }
            $data[$name] = $v;
        }
        return $data;
    }

    /** Genere un slug si le champ existe, est vide, et qu une source est definie. */
    private static function autoSlug(array $fields, array &$data): void
    {
        foreach ($fields as $f) {
            if (($f['type'] ?? '') !== 'slug') {
                continue;
            }
            $name = $f['name'];
            if (!empty($data[$name] ?? null)) {
                continue;
            }
            $src = $data[$f['from'] ?? 'name'] ?? '';
            $slug = strtolower(trim((string) $src));
            $slug = preg_replace('/[^a-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $slug) ?: $slug);
            $data[$name] = trim($slug, '-') . '-' . substr((string) time(), -4);
        }
    }
}

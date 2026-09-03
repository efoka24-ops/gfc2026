<?php
declare(strict_types=1);

namespace Gfc\Core;

abstract class Controller
{
    public function __construct(
        protected Database $db,
        protected Auth $auth,
        protected array $config,
    ) {
    }

    protected function view(string $template, array $data = []): never
    {
        Response::html(View::render($template, $data));
    }

    protected function currentEditionId(): int
    {
        return (int) ($this->db->value('SELECT id FROM editions WHERE is_current = 1 LIMIT 1') ?? 1);
    }
}

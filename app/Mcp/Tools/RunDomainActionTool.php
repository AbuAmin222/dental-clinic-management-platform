<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Contracts\McpToolInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Dispatches to the project's own Action classes (config('mcp.actions')) — never
 * re-implements their validation or business logic. This is the "Wrapper, not
 * modification" boundary from the brief: adding an action here means adding one config
 * entry, never touching this class or the Action itself.
 */
final class RunDomainActionTool implements McpToolInterface
{
    /**
     * @param array<string, array{class: class-string, description: string, input_schema: array<string, mixed>}> $actions
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $actions,
    ) {
    }

    public function name(): string
    {
        return 'run_domain_action';
    }

    public function description(): string
    {
        $available = implode(', ', array_keys($this->actions));

        return "Execute one of this project's own Action classes with a validated payload. "
            . "Available actions: {$available}. Call tools/list or read the "
            . 'action-specific input_schema before calling — invalid payloads are rejected '
            . 'with the same validation messages the web application itself would show.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['action', 'payload'],
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => array_keys($this->actions),
                ],
                'payload' => [
                    'type' => 'object',
                    'description' => 'Shape depends on which action is selected — see the action registry.',
                ],
            ],
        ];
    }

    public function isMutating(): bool
    {
        return true;
    }

    public function handle(array $arguments): array
    {
        $actionKey = (string) $arguments['action'];
        $payload = (array) $arguments['payload'];

        if (!isset($this->actions[$actionKey])) {
            throw new RuntimeException("Unknown action: {$actionKey}");
        }

        $actionClass = $this->actions[$actionKey]['class'];
        $action = $this->container->make($actionClass);

        if (!method_exists($action, 'execute')) {
            // Guards against config/mcp.php ever pointing at a class that doesn't fit
            // the contract this tool assumes — fails loudly instead of a fatal error
            // with a confusing stack trace at call time.
            throw new RuntimeException("{$actionClass} does not expose an execute(array \$data) method.");
        }

        try {
            $result = $action->execute($payload);
        } catch (ValidationException $exception) {
            return [
                'success' => false,
                'validation_errors' => $exception->errors(),
            ];
        }

        return [
            'success' => true,
            'result' => $this->serializeResult($result),
        ];
    }

    private function serializeResult(mixed $result): mixed
    {
        if (is_object($result) && method_exists($result, 'toArray')) {
            return $result->toArray();
        }

        return $result;
    }
}

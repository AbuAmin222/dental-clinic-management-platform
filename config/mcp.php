<?php

declare(strict_types=1);

use App\Actions\Appointment\BookAppointmentAction;
use App\Actions\Patient\RegisterPatientAction;
use App\Mcp\Resources\DatabaseSchemaResource;
use App\Mcp\Resources\EnumsResource;
use App\Mcp\Resources\RoutesResource;
use App\Mcp\Tools\ExecuteDatabaseQueryTool;
use App\Mcp\Tools\InspectSystemHealthTool;
use App\Mcp\Tools\RunDomainActionTool;

return [

    /*
    |--------------------------------------------------------------------------
    | Server identity
    |--------------------------------------------------------------------------
    | Reported verbatim in the `initialize` response's `serverInfo` block.
    */
    'server' => [
        'name' => env('MCP_SERVER_NAME', 'dental-clinic-mcp'),
        'version' => '1.0.0',
        'protocol_version' => '2024-11-05',
    ],

    /*
    |--------------------------------------------------------------------------
    | Global mutation kill-switch
    |--------------------------------------------------------------------------
    | McpServer checks this BEFORE calling any tool where isMutating() === true —
    | centrally, once, regardless of what any individual tool does internally.
    | Defaults to false: an MCP client connected to this server is read-only unless
    | someone explicitly opts in per environment. Never default this to true.
    */
    'allow_mutations' => (bool) env('MCP_ALLOW_MUTATIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Audit logging
    |--------------------------------------------------------------------------
    | Every tool call (request + result, mutating or not) is written to this log
    | channel when enabled. See config/logging.php — `mcp_audit` channel is added
    | by this project's logging config to write to storage/logs/mcp-audit.log,
    | kept separate from the application's regular log so it is easy to ship or
    | retain independently.
    */
    'audit' => [
        'enabled' => (bool) env('MCP_AUDIT_ENABLED', true),
        'channel' => env('MCP_AUDIT_CHANNEL', 'mcp_audit'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate limiting (SSE/HTTP transport only — stdio is already 1 client per process)
    |--------------------------------------------------------------------------
    */
    'rate_limit' => [
        'max_attempts' => (int) env('MCP_RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_seconds' => (int) env('MCP_RATE_LIMIT_DECAY_SECONDS', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tools
    |--------------------------------------------------------------------------
    | Adding a tool is: write the class (implements McpToolInterface), add its FQCN
    | here. McpServer/McpServiceProvider never need to change — Open/Closed.
    */
    'tools' => [
        ExecuteDatabaseQueryTool::class,
        RunDomainActionTool::class,
        InspectSystemHealthTool::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | ExecuteDatabaseQueryTool policy
    |--------------------------------------------------------------------------
    | Deliberately an allowlist, not a denylist — any table not listed here is
    | invisible to the tool, full stop. Tables holding data with no legitimate
    | reason to be read by an AI client (raw password/token columns aside — those
    | never leave the `users`/`personal_access_tokens` tables regardless) are
    | left off on purpose: `financial_audit_logs` and `salary_payments` are
    | intentionally excluded even though they're plain database tables, because
    | this tool is meant for schema/data exploration, not for reading
    | compensation data through a side channel that bypasses the RBAC permission
    | checks the actual application enforces on that data.
    */
    'database_query_tool' => [
        'allowed_tables' => [
            'users', 'doctors', 'patients', 'receptionists', 'financials',
            'departments', 'specializations',
            'appointments', 'dental_records', 'dental_charts', 'treatment_courses',
            'doctor_schedules', 'pricings',
            'invoices', 'invoice_items', 'payment_transactions', 'local_payment_methods',
            'roles', 'permissions', 'role_users', 'permission_roles', 'user_permissions',
        ],
        'max_rows' => (int) env('MCP_DB_TOOL_MAX_ROWS', 100),
        'timeout_seconds' => (int) env('MCP_DB_TOOL_TIMEOUT_SECONDS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | RunDomainActionTool registry
    |--------------------------------------------------------------------------
    | Keys are the public "action name" an MCP client requests by; values map to
    | the real Action class. Every listed action MUST expose execute(array $data)
    | — this is checked at boot by McpServiceProvider, not silently at call time.
    | Only two of the project's Actions are exposed here on purpose: these are the
    | two whose entire job is "validate a plain array and produce one Eloquent
    | model", which is exactly the shape an MCP tool call already is. The
    | Fortify/Jetstream actions (password reset, delete user) are intentionally
    | NOT exposed — those belong to authenticated-user self-service flows, not to
    | an AI-driven administrative channel.
    */
    'actions' => [
        'book_appointment' => [
            'class' => BookAppointmentAction::class,
            'description' => 'Book a new appointment for a patient with a doctor, enforcing the double-booking protection the application itself uses (pessimistic lock + overlap check).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['doctor_id', 'patient_id', 'appointment_date', 'start_time', 'end_time'],
                'properties' => [
                    'doctor_id' => ['type' => 'integer'],
                    'patient_id' => ['type' => 'integer'],
                    'appointment_date' => ['type' => 'string', 'format' => 'date'],
                    'start_time' => ['type' => 'string'],
                    'end_time' => ['type' => 'string'],
                    'reason_for_visit' => ['type' => 'string'],
                ],
            ],
        ],
        'register_patient' => [
            'class' => RegisterPatientAction::class,
            'description' => 'Register a new patient account the same way the Receptionist-facing registration flow does (identity_number is used as the initial password, matching the confirmed business rule).',
            'input_schema' => [
                'type' => 'object',
                'required' => ['first_name', 'last_name', 'email', 'identity_number'],
                'properties' => [
                    'first_name' => ['type' => 'string'],
                    'middle_name' => ['type' => 'string'],
                    'last_name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'phone' => ['type' => 'string'],
                    'identity_number' => ['type' => 'string'],
                    'gender' => ['type' => 'string'],
                    'date_of_birth' => ['type' => 'string', 'format' => 'date'],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    */
    'resources' => [
        DatabaseSchemaResource::class,
        RoutesResource::class,
        EnumsResource::class,
    ],
];

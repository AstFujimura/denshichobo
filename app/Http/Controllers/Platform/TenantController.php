<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Jobs\ProvisionTenant;
use App\Models\Control\Tenant;
use App\Models\Control\TenantAiSetting;
use App\Models\Control\TenantMigrationRun;
use App\Support\Platform\TenantActionOtp;
use App\Support\Tenancy\ProvisionDatabaseManager;
use App\Support\Tenancy\SupportEntryService;
use App\Support\Tenancy\TenantFeatureSync;
use App\Support\Tenancy\TenantResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $tenants = Tenant::query()
            ->with('features')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('prefix', 'like', "%{$q}%")
                        ->orWhere('db_database', 'like', "%{$q}%");
                });
            })
            ->orderBy('id')
            ->paginate(30)
            ->withQueryString();

        return view('platform.tenants.index', compact('tenants', 'q'));
    }

    public function create()
    {
        $this->authorizeProvisioning();

        return view('platform.tenants.create', [
            'features' => config('tenancy.features', []),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeProvisioning();

        $featureKeys = array_keys(config('tenancy.features', []));
        $dbSuffix = trim((string) $request->input('db_database_suffix', ''));
        if (str_starts_with($dbSuffix, 'denshichobo_')) {
            $dbSuffix = substr($dbSuffix, strlen('denshichobo_'));
        }
        $request->merge([
            'db_database' => $dbSuffix === '' ? '' : 'denshichobo_'.$dbSuffix,
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('control.tenants', 'code'),
            ],
            'prefix' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/',
                Rule::notIn(['platform']),
                Rule::unique('control.tenants', 'prefix'),
            ],
            'db_database' => [
                'required',
                'string',
                'max:64',
                'regex:/^denshichobo_[A-Za-z0-9_]+$/',
                Rule::unique('control.tenants', 'db_database'),
            ],
            'storage_mode' => ['required', Rule::in(['cloud', 'onpre'])],
            'storage_prefix' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/'],
            'file_upload_path' => ['nullable', 'required_if:storage_mode,onpre', 'string', 'max:1024'],
            'notes' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'boolean'],
            'ledger_ocr_provider' => ['required', Rule::in(['gemini', 'internal'])],
            'gemini_model' => ['nullable', 'string', 'max:128'],
        ], [
            'code.regex' => 'テナントコードは半角小文字・数字・アンダースコアのみ使用できます。',
            'prefix.regex' => 'URLプレフィックスは半角英数字・ハイフン・アンダースコア・ドットのみ使用できます。',
            'prefix.not_in' => 'URLプレフィックスに platform は使えません。',
            'db_database.required' => 'データベース名を入力してください。',
            'db_database.regex' => 'データベース名は denshichobo_ に続く半角英数字・アンダースコアにしてください。ハイフンは使えません。',
            'storage_prefix.regex' => '保存先プレフィックスは半角英数字・ハイフン・アンダースコア・ドットのみ使用できます。',
            'db_database.unique' => 'このデータベース名は既に使われています。',
        ]);

        $databaseExists = DB::connection('control')->selectOne(
            'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
            [$data['db_database']]
        );
        if ($databaseExists) {
            return back()
                ->withInput()
                ->withErrors(['db_database' => '指定したデータベースは既に存在します。新しいDB名を指定してください。']);
        }

        $this->consumeTenantOtp($request, 'create');

        $selectedFeatures = (array) ($data['features'] ?? []);
        $featureMap = [];
        foreach ($featureKeys as $key) {
            $meta = config("tenancy.features.{$key}", []);
            $featureMap[$key] = ! empty($meta['hidden'])
                ? (bool) ($meta['default'] ?? false)
                : ! empty($selectedFeatures[$key]);
        }
        $featureMap = $this->withoutOrphanCustomFeatures($featureMap);

        $tenant = Tenant::query()->create([
            'code' => $data['code'],
            'name' => $data['name'],
            'prefix' => $data['prefix'],
            'db_database' => $data['db_database'],
            'status' => Tenant::STATUS_PROVISIONING,
            'storage_mode' => $data['storage_mode'],
            'storage_prefix' => $data['storage_prefix'] ?: $data['prefix'],
            'file_upload_path' => $data['storage_mode'] === 'onpre'
                ? $data['file_upload_path']
                : null,
            'notes' => $data['notes'] ?? null,
        ]);

        ProvisionTenant::dispatch(
            $tenant->id,
            $featureMap,
            $data['ledger_ocr_provider'],
            $data['gemini_model'] ?: null
        )->afterResponse();

        return redirect()
            ->route('platform.tenants.show', $tenant)
            ->with('success', 'テナントを追加しました。データベースの作成が終わると状態が「利用中」になります。反映されていない場合は、この画面を開き直してください。');
    }

    public function show(Tenant $tenant, SupportEntryService $support)
    {
        $tenant->load(['features', 'aiSettings']);

        $features = [];
        foreach (config('tenancy.features', []) as $key => $meta) {
            $features[$key] = [
                'label' => $meta['label'] ?? $key,
                'enabled' => $tenant->featureEnabled($key),
            ];
        }

        $lastRun = TenantMigrationRun::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('id')
            ->first();

        $ai = $tenant->aiSettings;

        $tenantUsers = collect();
        $usersError = null;
        try {
            $tenantUsers = $support->listTenantUsers($tenant);
        } catch (\Throwable $e) {
            $usersError = $e->getMessage();
        }

        $canImpersonate = (bool) Auth::guard('platform')->user()?->can_impersonate;

        return view('platform.tenants.show', compact(
            'tenant',
            'features',
            'lastRun',
            'ai',
            'tenantUsers',
            'usersError',
            'canImpersonate'
        ));
    }

    public function update(
        Request $request,
        Tenant $tenant,
        TenantFeatureSync $featureSync,
        TenantResolver $resolver
    )
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:provisioning,active,suspended,archived'],
            'storage_mode' => ['required', 'in:cloud,onpre'],
            'storage_prefix' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9][A-Za-z0-9._-]*$/'],
            'file_upload_path' => ['nullable', 'required_if:storage_mode,onpre', 'string', 'max:1024'],
            'notes' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'boolean'],
            'ledger_ocr_provider' => ['required', 'in:gemini,internal'],
            'gemini_model' => ['nullable', 'string', 'max:128'],
        ], [
            'storage_prefix.regex' => '保存先プレフィックスは半角英数字・ハイフン・アンダースコア・ドットのみ使用できます。',
        ]);

        if ($data['storage_mode'] === 'cloud') {
            $data['file_upload_path'] = null;
        } elseif (! is_dir($data['file_upload_path'])) {
            return back()
                ->withInput()
                ->withErrors(['file_upload_path' => '指定したオンプレ保存先をサーバーから参照できません。']);
        }

        $featureMap = [];
        $selectedFeatures = (array) ($data['features'] ?? []);
        foreach (config('tenancy.features', []) as $key => $meta) {
            if (! empty($meta['hidden'])) {
                $featureMap[$key] = $tenant->featureEnabled($key);
                continue;
            }
            $featureMap[$key] = ! empty($selectedFeatures[$key]);
        }
        $featureMap = $this->withoutOrphanCustomFeatures($featureMap);
        $oldPrefix = $tenant->prefix;

        DB::connection('control')->transaction(function () use ($data, $tenant, $featureMap, $featureSync) {
            if ($data['status'] === Tenant::STATUS_SUSPENDED && ! $tenant->suspended_at) {
                $data['suspended_at'] = now();
            }
            if ($data['status'] === Tenant::STATUS_ACTIVE) {
                $data['suspended_at'] = null;
            }

            $tenant->fill([
                'name' => $data['name'],
                'status' => $data['status'],
                'storage_mode' => $data['storage_mode'],
                'storage_prefix' => $data['storage_prefix'],
                'file_upload_path' => $data['file_upload_path'],
                'notes' => $data['notes'] ?? null,
                'suspended_at' => $data['suspended_at'] ?? $tenant->suspended_at,
            ])->save();

            $featureSync->sync($tenant, $featureMap);

            TenantAiSetting::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'ledger_ocr_provider' => $data['ledger_ocr_provider'],
                    'gemini_model' => $data['gemini_model'] ?: null,
                ]
            );
        });

        $resolver->forgetPrefix($oldPrefix);
        $resolver->forgetPrefix($tenant->prefix);

        return back()->with('success', 'テナント設定を保存しました。');
    }

    public function discard(
        Request $request,
        Tenant $tenant,
        ProvisionDatabaseManager $provision,
        TenantResolver $resolver,
        SupportEntryService $support
    ) {
        $this->authorizeProvisioning();

        if ($tenant->status !== Tenant::STATUS_PROVISIONING || $tenant->provisioned_at !== null) {
            return back()->with('error', '取り消せるのは、作成が完了していない準備中のテナントだけです。');
        }

        $meta = [
            'code' => $tenant->code,
            'name' => $tenant->name,
            'prefix' => $tenant->prefix,
            'db_database' => $tenant->db_database,
        ];
        $prefix = $tenant->prefix;

        if ($provision->databaseExists($tenant->db_database)) {
            $provision->dropDatabase($tenant->db_database);
        }

        $tenant->delete();
        $resolver->forgetPrefix($prefix);
        $support->audit(
            Auth::guard('platform')->user(),
            null,
            'tenant_provision_discard',
            null,
            $request,
            $meta
        );

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', '準備中のテナントを取り消しました。');
    }

    public function destroy(
        Request $request,
        Tenant $tenant,
        ProvisionDatabaseManager $provision,
        TenantResolver $resolver,
        SupportEntryService $support
    ) {
        $this->authorizeProvisioning();
        $this->consumeTenantOtp($request, 'destroy', $tenant->id);

        $meta = [
            'code' => $tenant->code,
            'name' => $tenant->name,
            'prefix' => $tenant->prefix,
            'db_database' => $tenant->db_database,
        ];
        $prefix = $tenant->prefix;
        $database = $tenant->db_database;

        try {
            if ($provision->databaseExists($database)) {
                $provision->dropDatabase($database);
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'データベースを削除できませんでした。テナントは残しています: ' . $e->getMessage());
        }

        $tenant->delete();
        $resolver->forgetPrefix($prefix);
        $support->audit(
            Auth::guard('platform')->user(),
            null,
            'tenant_delete',
            null,
            $request,
            $meta
        );

        return redirect()
            ->route('platform.tenants.index')
            ->with('success', 'テナントとデータベースを削除しました。');
    }

    public function sendOtp(Request $request, TenantActionOtp $otp)
    {
        $data = $request->validate([
            'local' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._+-]+$/'],
            'purpose' => ['required', Rule::in(['create', 'destroy', 'support'])],
            'tenant_id' => ['nullable', 'integer'],
        ], [
            'local.required' => 'メールアドレスを入力してください。',
            'local.regex' => 'メールアドレスは @ より前の半角英数字を入力してください。',
        ]);

        if ($data['purpose'] !== 'support') {
            $this->authorizeProvisioning();
        }

        $tenant = null;
        $tenantId = null;
        if (in_array($data['purpose'], ['destroy', 'support'], true)) {
            $tenant = Tenant::query()->find($data['tenant_id'] ?? 0);
            if (! $tenant) {
                return response()->json(['message' => 'テナントが見つかりません。'], 422);
            }
            $tenantId = $tenant->id;
        }

        $email = $data['local'].'@'.config('tenancy.otp.domain', 'ast-sys.co.jp');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'メールアドレスの形式が正しくありません。'], 422);
        }

        try {
            $otp->send(
                (int) Auth::guard('platform')->id(),
                $email,
                $data['purpose'],
                $tenantId,
                $tenant
            );
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?: '確認コードを送信できませんでした。';

            return response()->json(['message' => $message], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => '確認コードを送信できませんでした。メール設定を確認してください。'], 500);
        }

        return response()->json(['message' => $email.' に確認コードを送信しました。']);
    }

    private function consumeTenantOtp(Request $request, string $purpose, ?int $tenantId = null): void
    {
        $request->validate([
            'otp_code' => ['required', 'digits:6'],
        ], [
            'otp_code.required' => '確認コードを入力してください。',
            'otp_code.digits' => '確認コードは6桁の数字です。',
        ]);

        app(TenantActionOtp::class)->consume(
            (int) Auth::guard('platform')->id(),
            (string) $request->input('otp_code'),
            $purpose,
            $tenantId
        );
    }

    /**
     * @param  array<string, bool>  $featureMap
     * @return array<string, bool>
     */
    private function withoutOrphanCustomFeatures(array $featureMap): array
    {
        foreach (config('tenancy.features', []) as $key => $meta) {
            $parent = $meta['parent'] ?? null;
            if ($parent && empty($featureMap[$parent])) {
                $featureMap[$key] = false;
            }
        }

        return $featureMap;
    }

    protected function authorizeProvisioning(): void
    {
        abort_unless(
            (bool) Auth::guard('platform')->user()?->can_provision,
            403,
            'テナント追加権限がありません。'
        );
    }
}

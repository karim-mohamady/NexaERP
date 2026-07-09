<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\CostCenter;
use App\Models\DashboardWidget;
use App\Models\Deal;
use App\Models\SavedReport;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Task;
use App\Models\UsageLimit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class EnterpriseDataController extends Controller
{
    private array $map = [
        'saved-reports' => SavedReport::class,
        'dashboard-widgets' => DashboardWidget::class,
        'attachments' => Attachment::class,
        'tasks' => Task::class,
        'cost-centers' => CostCenter::class,
        'stock-transfers' => StockTransfer::class,
        'stock-adjustments' => StockAdjustment::class,
        'deals' => Deal::class,
        'subscription-plans' => SubscriptionPlan::class,
        'subscriptions' => Subscription::class,
        'usage-limits' => UsageLimit::class,
    ];

    public function index(Request $request, string $resource)
    {
        $model = $this->model($resource);
        $query = $model::query();
        if ((new $model())->getConnection()->getSchemaBuilder()->hasColumn((new $model())->getTable(), 'company_id')) {
            $query->where('company_id', $request->user()->company_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request, string $resource)
    {
        $model = $this->model($resource);
        $payload = collect($request->all())->except(['id', 'created_at', 'updated_at'])->toArray();
        if ((new $model())->getConnection()->getSchemaBuilder()->hasColumn((new $model())->getTable(), 'company_id')) {
            $payload['company_id'] = $request->user()->company_id;
        }
        if ((new $model())->getTable() === 'dashboard_widgets') {
            $payload['user_id'] ??= $request->user()->id;
        }
        if ((new $model())->getTable() === 'saved_reports') {
            $payload['owner_id'] ??= $request->user()->id;
        }

        /** @var Model $record */
        $record = $model::create($payload);

        return response()->json($record, 201);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $model = $this->model($resource);
        $record = $model::findOrFail($id);
        $record->update($request->all());

        return response()->json($record->fresh());
    }

    public function destroy(string $resource, int $id)
    {
        $model = $this->model($resource);
        $model::findOrFail($id)->delete();

        return response()->json(['message' => 'Deleted']);
    }

    private function model(string $resource): string
    {
        abort_unless(isset($this->map[$resource]), 404);

        return $this->map[$resource];
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

abstract class GenericCrudController extends Controller
{
    protected string $modelClass;
    protected array $searchable = ['name', 'number', 'email', 'phone', 'sku', 'code', 'description'];
    protected array $validationRules = [];

    public function index(Request $request)
    {
        $model = new $this->modelClass();
        $query = $this->modelClass::query();

        $this->scopeCompany($query, $model, $request);

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->toString().'%';
            $query->where(function ($nested) use ($model, $term) {
                foreach ($this->searchable as $column) {
                    if (Schema::hasColumn($model->getTable(), $column)) {
                        $nested->orWhere($column, 'like', $term);
                    }
                }
            });
        }

        if ($request->filled('status') && Schema::hasColumn($model->getTable(), 'status')) {
            $query->where('status', $request->string('status'));
        }

        $sort = $request->string('sort', 'id')->toString();
        $direction = $request->string('direction', 'desc')->toString() === 'asc' ? 'asc' : 'desc';
        if (! Schema::hasColumn($model->getTable(), $sort)) {
            $sort = 'id';
        }

        return response()->json($query->orderBy($sort, $direction)->paginate((int) $request->integer('per_page', 10)));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $model = new $this->modelClass();

        if (Schema::hasColumn($model->getTable(), 'company_id') && empty($data['company_id'])) {
            $data['company_id'] = $request->user()?->company_id;
        }

        if (Schema::hasColumn($model->getTable(), 'branch_id') && empty($data['branch_id'])) {
            $data['branch_id'] = $request->user()?->branch_id;
        }

        $record = $this->modelClass::create($data);

        return response()->json($record, 201);
    }

    public function show(Request $request, int $id)
    {
        $model = new $this->modelClass();
        $query = $this->modelClass::query();
        $this->scopeCompany($query, $model, $request);

        return response()->json($query->findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $record = $this->findScoped($request, $id);
        $record->update($this->validatedData($request, true));

        return response()->json($record->refresh());
    }

    public function destroy(Request $request, int $id)
    {
        $this->findScoped($request, $id)->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    protected function findScoped(Request $request, int $id): Model
    {
        $model = new $this->modelClass();
        $query = $this->modelClass::query();
        $this->scopeCompany($query, $model, $request);

        return $query->findOrFail($id);
    }

    protected function scopeCompany($query, Model $model, Request $request): void
    {
        $user = $request->user();
        if ($user && $user->company_id && Schema::hasColumn($model->getTable(), 'company_id')) {
            $query->where('company_id', $user->company_id);
        }
    }

    protected function validatedData(Request $request, bool $partial = false): array
    {
        if ($this->validationRules !== []) {
            return $request->validate($this->validationRules);
        }

        return collect($request->all())
            ->except(['id', 'created_at', 'updated_at'])
            ->filter(fn ($value) => $partial || $value !== null)
            ->toArray();
    }
}
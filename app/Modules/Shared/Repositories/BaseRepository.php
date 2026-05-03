<?php

/**
 * @file: BaseRepository.php
 * @description: الـ Base Repository الذي يورث منه جميع Repositories في النظام
 * يوفر عمليات CRUD موحدة ومتقدمة
 * @author: Team Leader (Khalid)
 * @version: 2.0
 */

namespace App\Modules\Shared\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * الحصول على النموذج الحالي
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * البحث عن سجل بـ ID
     * @param int|string $id
     * @return Model|null
     */
    public function findById($id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * البحث عن سجل مع رفع استثناء إذا لم يُوجد
     * @param int|string $id
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByIdOrFail($id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * الحصول على جميع السجلات
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array $columns = ['*'])
    {
        return $this->model->select($columns)->get();
    }

    /**
     * الحصول على جميع السجلات مع Pagination
     * @param int $perPage
     * @param array $columns
     * @param int $page
     * @return Paginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], int $page = 1)
    {
        return $this->model->select($columns)->paginate($perPage, $columns, 'page', $page);
    }

    /**
     * البحث عن سجل حسب شرط معين
     * @param string $column
     * @param mixed $value
     * @return Model|null
     */
    public function findBy(string $column, $value): ?Model
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * الحصول على جميع السجلات حسب شرط معين
     * @param array $conditions مثال: ['status' => 'active', 'role' => 'admin']
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAllBy(array $conditions)
    {
        $query = $this->model;

        foreach ($conditions as $column => $value) {
            $query = $query->where($column, $value);
        }

        return $query->get();
    }

    /**
     * إنشاء سجل جديد
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * تحديث سجل معين
     * @param int|string $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        $model = $this->findById($id);

        if (!$model) {
            return false;
        }

        return $model->update($data);
    }

    /**
     * حذف سجل معين
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool
    {
        $model = $this->findById($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    /**
     * حذف سجلات متعددة
     * @param array $ids
     * @return int عدد السجلات المحذوفة
     */
    public function deleteMultiple(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * البحث المتقدم مع فلترة وترتيب
     * @param array $filters
     * @param array $sorts
     * @return Builder
     */
    public function search(array $filters = [], array $sorts = []): \Illuminate\Database\Eloquent\Builder
    {
        // التعديل الأساسي: نبدأ بـ newQuery لضمان إرجاع Builder
        $query = $this->model->newQuery();

        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $query->where($column, $value);
            }
        }

        foreach ($sorts as $column => $direction) {
            $query->orderBy($column, $direction);
        }

        return $query;
    }


    /**
     * الحصول على عدد السجلات
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * التحقق من وجود سجل
     * @param string $column
     * @param mixed $value
     * @return bool
     */
    public function exists(string $column, $value): bool
    {
        return $this->model->where($column, $value)->exists();
    }

    /**
     * تحديث أو إنشاء سجل
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    /**
     * البحث عن أول سجل أو إنشاء واحد جديد
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    /**
     * تطبيق شروط متعددة على الـ Query
     * @param \Closure $callback
     * @return Builder
     */
    public function when(\Closure $callback): Builder
    {
        return $this->model->when(true, $callback);
    }

    /**
     * تحميل العلاقات (Eager Loading)
     * @param array $relations
     * @return Builder
     */
    public function with(array $relations): Builder
    {
        return $this->model->with($relations);
    }
}

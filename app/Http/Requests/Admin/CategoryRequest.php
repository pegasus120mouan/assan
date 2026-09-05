<?php

namespace App\Http\Requests\Admin;

use App\Enums\Status;
use App\Models\Category;
use App\Support\CategoryIcons;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isStaff() ?? false;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(Status::class)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'icon' => ['required', 'string', Rule::in(CategoryIcons::keys())],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $category = $this->route('category');
            $parentId = $this->integer('parent_id') ?: null;

            if ($category instanceof Category && $parentId && $this->createsCycle($category, $parentId)) {
                $validator->errors()->add('parent_id', 'Une catégorie ne peut pas être parente d\'elle-même ou d\'une de ses sous-catégories.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function catalogData(): array
    {
        return [
            'name' => $this->validated('name'),
            'slug' => $this->validated('slug'),
            'parent_id' => $this->validated('parent_id') ?: null,
            'description' => $this->validated('description'),
            'status' => $this->validated('status'),
            'sort_order' => (int) ($this->validated('sort_order') ?? 0),
            'icon' => $this->validated('icon'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'icon' => 'icône',
            'image' => 'image',
            'parent_id' => 'catégorie parente',
        ];
    }

    private function createsCycle(Category $category, int $parentId): bool
    {
        if ($parentId === $category->id) {
            return true;
        }

        $parent = Category::query()->find($parentId);

        while ($parent) {
            if ($parent->id === $category->id) {
                return true;
            }

            $parent = $parent->parent;
        }

        return false;
    }
}

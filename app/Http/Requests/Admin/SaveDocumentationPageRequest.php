<?php

namespace App\Http\Requests\Admin;

use App\Models\DocumentationPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveDocumentationPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $parent = $this->input('parent_id');
        if ($parent === '' || $parent === null) {
            $this->merge(['parent_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'documentation_category_id' => 'required|exists:documentation_categories,id',
            'parent_id' => 'nullable|exists:documentation_pages,id',
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[\p{Arabic}a-zA-Z0-9\s-]+$/u'],
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($v): void {
            if (! empty($this->input('parent_id'))) {
                $parent = DocumentationPage::find($this->input('parent_id'));
                if ($parent && (int) $parent->documentation_category_id !== (int) $this->input('documentation_category_id')) {
                    $v->errors()->add('parent_id', 'الصفحة الأب يجب أن تنتمي لنفس القسم');
                }
            }

            $docPage = $this->route('documentation_page');
            if ($docPage instanceof DocumentationPage
                && $this->filled('parent_id')
                && (int) $this->input('parent_id') === (int) $docPage->id) {
                $v->errors()->add('parent_id', 'لا يمكن أن تكون الصفحة أباً لنفسها');
            }
        });
    }

    protected function passedValidation(): void
    {
        if (empty($this->input('parent_id'))) {
            $this->merge(['parent_id' => null]);
        }

        $slug = $this->normalizeSlug($this->input('slug'), $this->input('title'));
        $this->merge(['slug' => $slug]);

        $ignoreId = null;
        $docPage = $this->route('documentation_page');
        if ($docPage instanceof DocumentationPage) {
            $ignoreId = $docPage->id;
        }

        $this->assertPageSlugUnique(
            (int) $this->input('documentation_category_id'),
            $this->input('parent_id') ? (int) $this->input('parent_id') : null,
            $slug,
            $ignoreId
        );
    }

    private function normalizeSlug(?string $slug, string $title): string
    {
        $s = $slug !== null && $slug !== '' ? Str::slug($slug) : Str::slug($title);

        return $s !== '' ? $s : 'page-'.time();
    }

    private function assertPageSlugUnique(int $categoryId, ?int $parentId, string $slug, ?int $ignoreId = null): void
    {
        $q = DocumentationPage::where('documentation_category_id', $categoryId)
            ->where('slug', $slug);

        if ($parentId === null) {
            $q->whereNull('parent_id');
        } else {
            $q->where('parent_id', $parentId);
        }

        if ($ignoreId) {
            $q->where('id', '!=', $ignoreId);
        }

        if ($q->exists()) {
            throw ValidationException::withMessages([
                'slug' => 'هذا الرابط مستخدم بالفعل ضمن نفس القسم والمستوى',
            ]);
        }
    }
}

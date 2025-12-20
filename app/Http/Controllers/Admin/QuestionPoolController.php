<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuestionPool;
use App\Models\QuestionPoolItem;
use App\Models\QuestionBank;
use App\Models\Course;
use App\Models\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionPoolController extends Controller
{
    /**
     * Display a listing of question pools.
     */
    public function index(Request $request)
    {
        $query = QuestionPool::with(['course', 'creator'])
            ->withCount('poolItems')
            ->orderBy('created_at', 'desc');

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $pools = $query->paginate(15);
        $courses = Course::where('is_published', true)->get();

        return view('admin.pages.question-pools.index', compact('pools', 'courses'));
    }

    /**
     * Show the form for creating a new pool.
     */
    public function create()
    {
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::where('is_active', true)->get();

        // Load all active questions by default so the table is populated
        // (can be further filtered in the view via JS / selects)
        $questions = QuestionBank::where('is_active', true)
            ->with('questionType')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.pages.question-pools.create', compact('courses', 'questionTypes', 'questions'));
    }

    /**
     * Store a newly created pool.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle checkbox
        $validated['is_active'] = $request->has('is_active');

        // Set creator
        $validated['created_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $pool = QuestionPool::create($validated);

            // Add questions to pool if provided
            if ($request->has('question_ids')) {
                $this->addQuestionsToPool($pool, $request->input('question_ids'));
            }

            DB::commit();

            return redirect()->route('question-pools.show', $pool->id)
                ->with('success', 'تم إنشاء مجموعة الأسئلة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء إنشاء مجموعة الأسئلة: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified pool.
     */
    public function show($id)
    {
        $pool = QuestionPool::with([
            'course',
            'creator',
            'poolItems.question.questionType',
            'poolItems.question.options'
        ])->findOrFail($id);

        // Calculate pool statistics
        $stats = [
            'total_questions' => $pool->poolItems()->count(),
            'total_points' => $pool->poolItems()->sum('max_score'),
            'by_type' => $pool->poolItems()
                ->join('question_bank', 'question_pool_items.question_id', '=', 'question_bank.id')
                ->join('question_types', 'question_bank.question_type_id', '=', 'question_types.id')
                ->select('question_types.display_name', DB::raw('COUNT(*) as count'))
                ->groupBy('question_types.display_name')
                ->get(),
            'by_difficulty' => $pool->poolItems()
                ->join('question_bank', 'question_pool_items.question_id', '=', 'question_bank.id')
                ->select('question_bank.difficulty', DB::raw('COUNT(*) as count'))
                ->groupBy('question_bank.difficulty')
                ->get(),
        ];

        return view('admin.pages.question-pools.show', compact('pool', 'stats'));
    }

    /**
     * Show the form for editing the specified pool.
     */
    public function edit($id)
    {
        $pool = QuestionPool::with('poolItems.question')->findOrFail($id);
        $courses = Course::where('is_published', true)->get();
        $questionTypes = QuestionType::where('is_active', true)->get();

        // Get available questions for this course
        $availableQuestions = QuestionBank::where('course_id', $pool->course_id)
            ->where('is_active', true)
            ->with('questionType')
            ->get();

        return view('admin.pages.question-pools.edit', compact('pool', 'courses', 'questionTypes', 'availableQuestions'));
    }

    /**
     * Update the specified pool.
     */
    public function update(Request $request, $id)
    {
        $pool = QuestionPool::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        // Handle checkbox
        $validated['is_active'] = $request->has('is_active');

        // Set updater
        $validated['updated_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $pool->update($validated);

            // Update pool items if provided
            if ($request->has('question_ids')) {
                // Remove all existing items
                $pool->poolItems()->delete();

                // Add new items
                $this->addQuestionsToPool($pool, $request->input('question_ids'));
            }

            DB::commit();

            return redirect()->route('question-pools.show', $pool->id)
                ->with('success', 'تم تحديث مجموعة الأسئلة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'حدث خطأ أثناء تحديث مجموعة الأسئلة: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified pool.
     */
    public function destroy($id)
    {
        $pool = QuestionPool::findOrFail($id);

        try {
            $pool->delete();

            return redirect()->route('question-pools.index')
                ->with('success', 'تم حذف مجموعة الأسئلة بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء حذف مجموعة الأسئلة: ' . $e->getMessage()]);
        }
    }

    /**
     * Add a question to the pool.
     */
    public function addQuestion(Request $request, $id)
    {
        $pool = QuestionPool::findOrFail($id);

        $validated = $request->validate([
            'question_id' => 'required|exists:question_bank,id',
            'max_score' => 'nullable|numeric|min:0',
            'selection_probability' => 'nullable|numeric|min:0|max:1',
        ]);

        try {
            $question = QuestionBank::findOrFail($validated['question_id']);

            // Check if question already exists in pool
            if ($pool->poolItems()->where('question_id', $validated['question_id'])->exists()) {
                return back()->withErrors(['error' => 'السؤال موجود بالفعل في هذه المجموعة']);
            }

            QuestionPoolItem::create([
                'pool_id' => $pool->id,
                'question_id' => $validated['question_id'],
                'max_score' => $validated['max_score'] ?? $question->points,
                'selection_probability' => $validated['selection_probability'] ?? 1.0,
                'item_order' => $pool->poolItems()->count() + 1,
            ]);

            return back()->with('success', 'تم إضافة السؤال إلى المجموعة بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء إضافة السؤال: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove a question from the pool.
     */
    public function removeQuestion($id, $itemId)
    {
        $pool = QuestionPool::findOrFail($id);
        $poolItem = QuestionPoolItem::where('pool_id', $pool->id)
            ->where('id', $itemId)
            ->firstOrFail();

        try {
            $poolItem->delete();

            return back()->with('success', 'تم إزالة السؤال من المجموعة بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء إزالة السؤال: ' . $e->getMessage()]);
        }
    }

    /**
     * Update question order in pool.
     */
    public function updateOrder(Request $request, $id)
    {
        $pool = QuestionPool::findOrFail($id);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:question_pool_items,id',
            'items.*.order' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                QuestionPoolItem::where('id', $item['id'])
                    ->where('pool_id', $pool->id)
                    ->update(['item_order' => $item['order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث ترتيب الأسئلة بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الترتيب'
            ], 500);
        }
    }

    /**
     * Generate random questions from pool.
     */
    public function generateQuestions(Request $request, $id)
    {
        $pool = QuestionPool::with('poolItems.question')->findOrFail($id);

        $validated = $request->validate([
            'count' => 'required|integer|min:1',
            'by_probability' => 'nullable|boolean',
        ]);

        $count = min($validated['count'], $pool->poolItems()->count());
        $byProbability = $validated['by_probability'] ?? false;

        try {
            if ($byProbability) {
                // Use weighted random selection based on probability
                $questions = $this->selectByProbability($pool, $count);
            } else {
                // Simple random selection
                $questions = $pool->poolItems()
                    ->inRandomOrder()
                    ->limit($count)
                    ->with('question')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'questions' => $questions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء توليد الأسئلة'
            ], 500);
        }
    }

    /**
     * Duplicate a pool.
     */
    public function duplicate($id)
    {
        $pool = QuestionPool::with('poolItems')->findOrFail($id);

        DB::beginTransaction();
        try {
            // Create duplicate pool
            $duplicate = $pool->replicate();
            $duplicate->name = $pool->name . ' (نسخة)';
            $duplicate->created_by = auth()->id();
            $duplicate->updated_by = null;
            $duplicate->save();

            // Duplicate pool items
            foreach ($pool->poolItems as $item) {
                $duplicateItem = $item->replicate();
                $duplicateItem->pool_id = $duplicate->id;
                $duplicateItem->save();
            }

            DB::commit();

            return redirect()->route('question-pools.edit', $duplicate->id)
                ->with('success', 'تم نسخ مجموعة الأسئلة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء نسخ مجموعة الأسئلة: ' . $e->getMessage()]);
        }
    }

    /**
     * Get pool statistics (AJAX).
     */
    public function getStatistics($id)
    {
        $pool = QuestionPool::findOrFail($id);

        $stats = [
            'total_questions' => $pool->poolItems()->count(),
            'total_points' => $pool->poolItems()->sum('max_score'),
            'average_points' => $pool->poolItems()->avg('max_score'),
            'by_type' => $pool->poolItems()
                ->join('question_bank', 'question_pool_items.question_id', '=', 'question_bank.id')
                ->join('question_types', 'question_bank.question_type_id', '=', 'question_types.id')
                ->select('question_types.display_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(question_pool_items.max_score) as total_points'))
                ->groupBy('question_types.display_name')
                ->get(),
            'by_difficulty' => $pool->poolItems()
                ->join('question_bank', 'question_pool_items.question_id', '=', 'question_bank.id')
                ->select('question_bank.difficulty', DB::raw('COUNT(*) as count'), DB::raw('SUM(question_pool_items.max_score) as total_points'))
                ->groupBy('question_bank.difficulty')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Add multiple questions to pool.
     */
    private function addQuestionsToPool(QuestionPool $pool, array $questionIds): void
    {
        foreach ($questionIds as $index => $questionId) {
            $question = QuestionBank::find($questionId);

            if ($question) {
                QuestionPoolItem::create([
                    'pool_id' => $pool->id,
                    'question_id' => $questionId,
                    'max_score' => $question->points,
                    'selection_probability' => 1.0,
                    'item_order' => $index + 1,
                ]);
            }
        }
    }

    /**
     * Select questions by probability weight.
     */
    private function selectByProbability(QuestionPool $pool, int $count)
    {
        $items = $pool->poolItems()->with('question')->get();

        // Create weighted array
        $weighted = [];
        foreach ($items as $item) {
            $weight = max(1, (int)($item->selection_probability * 100));
            for ($i = 0; $i < $weight; $i++) {
                $weighted[] = $item;
            }
        }

        // Shuffle and select
        shuffle($weighted);
        $selected = [];
        $selectedIds = [];

        foreach ($weighted as $item) {
            if (count($selected) >= $count) {
                break;
            }

            if (!in_array($item->id, $selectedIds)) {
                $selected[] = $item;
                $selectedIds[] = $item->id;
            }
        }

        return collect($selected);
    }
}

<?php
// /src/Controller/QuestionsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionField;
use App\Models\Section;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;

/**
 * Manages the Questions within a Section of the current Company Admin's
 * question bank, including each question's answer options and
 * fill-in-the-blank fields.
 */
class QuestionsController
{
    use RecentActivityLogger;

    /**
     * List the questions for a given (encoded) section id -- always AJAX,
     * used both for the page's initial section and for switching tabs.
     */
    public function index(string $encodedSectionId): void
    {
        $currentUser = AuthService::currentUser();
        $companyId = $currentUser->company_id ?? 0;

        $sectionId = IdEncoder::decode($encodedSectionId);
        $section = $sectionId ? Section::where('id', $sectionId)->where('company_id', $companyId)->first() : null;

        header('Content-Type: application/json');

        if (!$section) {
            echo json_encode(['success' => false, 'messages' => ['Section not found.']]);
            exit;
        }

        $questions = Question::with(['options', 'fields'])
            ->where('company_id', $companyId)
            ->where('section_id', $section->id)
            ->where('status_id', 1)
            ->orderBy('pos_index')
            ->get();

        echo json_encode([
            'success' => true,
            'data' => array_map(fn(Question $q) => ['rowHtml' => self::renderRow($q)], $questions->all()),
        ]);
        exit;
    }

    /**
     * Render a single question as a card, including its answer options and
     * fill-in-the-blank fields as small inline tags.
     */
    public static function renderRow(Question $question): string
    {
        $GLOBALS['assetBase'] = getAssetBase();

        $path = __DIR__ . '/../../resources/views/components/questions/question-row.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<div class='p-4 text-red-500 text-sm'>Render Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        return ob_get_clean();
    }

    /**
     * Create or update a question, including syncing its answer options and
     * fill-in-the-blank fields (both are fully replaced on every save --
     * simpler and just as correct as diffing, since nothing else in the app
     * references an individual option/field row yet).
     */
    public function save(array $data): array
    {
        try {
            $currentUser = AuthService::currentUser();
            $companyId = $currentUser->company_id ?? 0;
            if (!$companyId) throw new \Exception("No company associated with this account.");

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);

            $sectionId = IdEncoder::decode((string)($data['section_id'] ?? ''));
            $section = $sectionId ? Section::where('id', $sectionId)->where('company_id', $companyId)->first() : null;
            if (!$section) throw new \Exception("Section not found.");

            $title = trim($data['question_title'] ?? '');
            if ($title === '') throw new \Exception("Question text is required.");

            $questionId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $question = $questionId ? Question::where('id', $questionId)->where('company_id', $companyId)->first() : new Question();
            if (!$question) throw new \Exception("Question not found.");

            $question->company_id = $companyId;
            $question->section_id = $section->id;
            $question->question_title = $title;
            $question->answer_mode = (int)($data['answer_mode'] ?? 0) === 1 ? 1 : 0;
            $question->is_condition = !empty($data['is_condition']);

            if ($isNew) {
                $question->pos_index = Question::where('section_id', $section->id)->count();
                $question->status_id = 1;
            }

            $question->save();

            QuestionOption::where('question_id', $question->id)->delete();
            $options = is_array($data['options'] ?? null) ? array_values($data['options']) : [];
            foreach ($options as $index => $text) {
                $text = trim((string)$text);
                if ($text === '') continue;
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $text,
                    'pos_index'   => $index,
                ]);
            }

            QuestionField::where('question_id', $question->id)->delete();
            $fields = is_array($data['fields'] ?? null) ? array_values($data['fields']) : [];
            foreach ($fields as $index => $field) {
                $prefix = trim((string)($field['prefix'] ?? ''));
                $suffix = trim((string)($field['suffix'] ?? ''));
                if ($prefix === '' && $suffix === '') continue;
                QuestionField::create([
                    'question_id' => $question->id,
                    'prefix'      => $prefix ?: null,
                    'suffix'      => $suffix ?: null,
                    'pos_index'   => $index,
                ]);
            }

            $question->load(['options', 'fields']);

            $actionLabel = $isNew ? "Created question" : "Updated question";
            static::logActivity("{$actionLabel}: {$question->question_title}", 'Questions');

            return [
                'success'  => true,
                'rowHtml'  => self::renderRow($question),
                'messages' => ['Question saved successfully.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    public function delete(?string $id): array
    {
        try {
            $currentUser = AuthService::currentUser();
            $companyId = $currentUser->company_id ?? 0;

            $rawId = $id ? IdEncoder::decode($id) : null;
            $question = $rawId ? Question::where('id', $rawId)->where('company_id', $companyId)->first() : null;
            if (!$question) {
                return ['success' => false, 'messages' => ['Question not found.']];
            }

            $title = $question->question_title;
            QuestionOption::where('question_id', $question->id)->delete();
            QuestionField::where('question_id', $question->id)->delete();
            $question->delete();

            static::logActivity("Deleted question: {$title}", 'Questions');

            return ['success' => true, 'messages' => ['Question deleted successfully.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Persist a section's questions' new top-to-bottom order.
     */
    public function reorder(array $encodedIds): array
    {
        try {
            $currentUser = AuthService::currentUser();
            $companyId = $currentUser->company_id ?? 0;

            foreach ($encodedIds as $index => $encodedId) {
                $rawId = IdEncoder::decode((string)$encodedId);
                if (!$rawId) continue;
                Question::where('id', $rawId)->where('company_id', $companyId)->update(['pos_index' => $index]);
            }

            static::logActivity('Reordered questions', 'Questions');

            return ['success' => true, 'messages' => ['Question order updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

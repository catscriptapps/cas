<?php
// /src/Controller/FaqsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Faq;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;

class FaqsController
{
    use RecentActivityLogger;

    /**
     * Retrieve all active FAQs for public display
     */
    public static function allPublished()
    {
        return Faq::where('status_id', Faq::STATUS_ACTIVE)
            ->orderBy('display_order', 'asc')
            ->get();
    }

    /**
     * Handle Index / Search for Admin View
     */
    public function index(): void
    {
        $query = $_GET['q'] ?? '';
        $builder = Faq::query();

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('question', 'LIKE', "%{$query}%")
                    ->orWhere('answer', 'LIKE', "%{$query}%");
            });
        }

        $faqs = $builder->orderBy('display_order', 'asc')->get();

        // AJAX response for live search (Inside index() method)
        if (isset($_GET['q'])) {
            header('Content-Type: application/json');

            // Transform the collection into an array of objects that have rowHtml
            $responseData = array_map(function ($f) {
                return [
                    'id' => $f->id,
                    'rowHtml' => self::renderRow($f)
                ];
            }, $faqs->all());

            echo json_encode([
                'success' => true,
                'data'    => $responseData, // Now map() has something to work with
                'meta'    => [
                    'total' => Faq::count(),
                    'count' => count($faqs)
                ]
            ]);
            exit;
        }

        $html = '';
        foreach ($faqs as $faq) {
            $html .= self::renderRow($faq);
        }

        $GLOBALS['faqRows'] = $html;
        $GLOBALS['title'] = "Manage FAQs";
    }

    /**
     * Render individual FAQ item (Accordion/Card)
     */
    public static function renderRow(\App\Models\Faq $faq): string
    {
        $isLoggedIn = !empty($_SESSION['user_id']);
        $isAdmin = (AuthService::isAdmin());
        $encodedId = IdEncoder::encode((int)$faq->id);

        // Variables extracted here are available inside faq-card.php
        $path = __DIR__ . '/../../resources/views/components/faqs/faq-card.php';

        if (!file_exists($path)) {
            return "<div class='p-4 text-red-500'>Component not found: faq-card.php</div>";
        }

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<div class='p-4 border-red-500 text-red-500'>Render Error: " . $e->getMessage() . "</div>";
        }
        return ob_get_clean();
    }

    /**
     * Save or Update FAQ
     */
    public function save(array $data): array
    {
        try {
            $encodedId = $data['encoded_id'] ?? $data['encodedId'] ?? null;
            $isNew = empty($encodedId);
            $id = !$isNew ? IdEncoder::decode($encodedId) : null;
            $faq = $id ? Faq::find($id) : new Faq();

            $newOrder = (int)($data['display_order'] ?? 0);

            // --- DISPLACEMENT LOGIC ---
            // Increment all items that currently have this order or higher
            // to "push them down" the list.
            Faq::where('display_order', '>=', $newOrder)
                ->where('id', '!=', $id) // Don't push the item we are currently editing
                ->increment('display_order');

            $faq->question      = $data['question'] ?? '';
            $faq->answer        = $data['answer'] ?? '';
            $faq->display_order = $newOrder;
            $faq->status_id     = (int)($data['status_id'] ?? 1);

            if ($isNew) {
                $faq->orig_user_id = $_SESSION['user_id'] ?? 1;
            }

            $faq->save();

            // --- RENORMALIZATION (Optional but Recommended) ---
            // After shifting, there might be gaps (0, 2, 3). 
            // We re-sequence them to 0, 1, 2, 3...
            $all = Faq::orderBy('display_order', 'asc')->get();
            foreach ($all as $index => $item) {
                $item->display_order = $index;
                $item->save();
            }

            // Return the full list HTML because multiple rows changed
            $faqs = Faq::orderBy('display_order', 'asc')->get();
            $fullHtml = implode('', array_map(fn($f) => self::renderRow($f), $faqs->all()));

            return [
                'success'  => true,
                'fullHtml' => $fullHtml, // Send everything back since order shifted
                'messages' => ['FAQ order updated and saved.']
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Delete FAQ
     */
    public function delete($id): array
    {
        try {
            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $faq = Faq::find($rawId);

            if ($faq) {
                $faq->delete();

                // Renormalize remaining items
                $all = Faq::orderBy('display_order', 'asc')->get();
                foreach ($all as $index => $item) {
                    $item->display_order = $index;
                    $item->save();
                }

                $fullHtml = implode('', array_map(fn($f) => self::renderRow($f), $all->all()));

                return [
                    'success' => true,
                    'fullHtml' => $fullHtml,
                    'messages' => ['FAQ deleted and list re-sequenced.']
                ];
            }
            return ['success' => false, 'messages' => ['Failed to delete.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

<?php
// /src/Controller/CompaniesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Company;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;

class CompaniesController
{
    use RecentActivityLogger;

    /**
     * Handle Delete
     * @param string|null $id
     * @return array
     */
    public function delete(?string $id): array
    {
        try {
            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $company = Company::find($rawId);

            if ($company) {
                $companyName = $company->company_name;

                if ($company->delete()) {
                    static::logActivity("Deleted company: {$companyName}", 'Companies');
                    return ['success' => true, 'messages' => ['Company deleted successfully.']];
                }
            }
            return ['success' => false, 'messages' => ['Failed to delete company.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Prepare data for the Companies List Page
     * Supports infinite scroll and search
     * @return void
     */
    public function index(): void
    {
        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = Company::with(['country', 'region'])
            ->leftJoin('countries', 'companies.country_id', '=', 'countries.id')
            ->leftJoin('regions', 'companies.region_id', '=', 'regions.id')
            ->select('companies.*');

        // A Company Admin only ever sees their own company -- the global
        // Admin is the only role that browses the full directory.
        if (!AuthService::isAdmin()) {
            $currentUser = AuthService::currentUser();
            $builder->where('companies.id', $currentUser->company_id ?? 0);
        }

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('companies.company_name', 'LIKE', "%{$query}%")
                    ->orWhere('companies.email', 'LIKE', "%{$query}%")
                    ->orWhere('companies.city', 'LIKE', "%{$query}%")
                    ->orWhere('countries.country', 'LIKE', "%{$query}%")
                    ->orWhere('regions.region', 'LIKE', "%{$query}%");
            });
        }

        $totalFiltered = $builder->count();

        $companies = $builder->orderBy('companies.date_created', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // AJAX response
        if (isset($_GET['q']) || isset($_GET['page'])) {
            header('Content-Type: application/json');

            echo json_encode([
                'success' => true,
                'data' => array_map(fn($c) => ['rowHtml' => self::renderRow($c)], $companies->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $companies->count(),
                    'hasMore' => ($offset + $companies->count()) < $totalFiltered
                ]
            ]);
            exit;
        }

        // Standard Page Load
        $html = '';
        foreach ($companies as $company) {
            $html .= self::renderRow($company);
        }

        $GLOBALS['companyRows'] = $html;
        $GLOBALS['title'] = "Companies";
        $GLOBALS['totalCompaniesCount'] = $totalFiltered;
    }

    /**
     * Render individual table row HTML
     * @param Company $company
     * @return string
     */
    public static function renderRow(Company $company): string
    {
        $rowItem = $company->toArray();

        $GLOBALS['assetBase'] = getAssetBase();

        $rowItem['country_name'] = $company->country->country ?? 'N/A';
        $rowItem['region_name']  = $company->region->region ?? 'N/A';

        $rowItem['encoded_id'] = IdEncoder::encode((int)$company->id);
        $rowItem['created_at_formatted'] = $company->date_created ? $company->date_created->format('M j, Y') : 'N/A';

        $path = __DIR__ . '/../../resources/views/components/companies/data-row.php';

        ob_start();
        try {
            $assetBase = getAssetBase();
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='6'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    /**
     * Handle Create or Update for Companies
     * @param array $data
     * @return array
     */
    public function save(array $data): array
    {
        try {
            $encodedId = $data['encoded_id'] ?? null;
            $companyName = trim($data['company_name'] ?? '');
            $isNew = empty($encodedId);

            if (empty($companyName)) throw new \Exception("Company name is required.");

            $companyId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $company = $companyId ? Company::find($companyId) : new Company();

            if (!$company) throw new \Exception("Company not found.");

            // A Company Admin may edit their own company's details, but only
            // the platform Admin can create a new company or edit any other
            // company -- CompaniesController::index() already scopes a
            // Company Admin's row list to just their own company, so this is
            // really just closing off the API endpoint to match what the UI
            // already only ever lets them reach.
            $isAdmin = AuthService::isAdmin();
            if (!$isAdmin) {
                $currentUser = AuthService::currentUser();
                $isOwnCompany = AuthService::isCompanyAdmin() && $company->exists && (int)$company->id === (int)($currentUser->company_id ?? 0);
                if (!$isOwnCompany) {
                    throw new \Exception("You don't have permission to do that.");
                }
            }

            $email = trim($data['email'] ?? '');
            if (!empty($email)) {
                $existingQuery = Company::where('email', $email);
                if ($company->exists) {
                    $existingQuery->where('id', '!=', $company->id);
                }
                if ($existingQuery->exists()) {
                    throw new \Exception("The email address '{$email}' is already in use by another company.");
                }
            }

            $company->company_name = $companyName;
            $company->email        = $email ?: null;
            $company->phone        = $data['phone'] ?? null;
            $company->toll_free    = $data['toll_free'] ?? null;
            $company->website      = $data['website'] ?? null;
            $company->slogan       = $data['slogan'] ?? null;
            $company->address      = $data['address'] ?? null;
            $company->city         = $data['city'] ?? null;
            $company->country_id   = (int)($data['country_id'] ?? 0) ?: null;
            $tableRegionId          = (int)($data['region_id'] ?? 0);
            $company->region_id    = $tableRegionId > 0 ? $tableRegionId : null;
            $company->postal_code  = $data['postal_code'] ?? null;
            $company->general_summary = trim((string)($data['general_summary'] ?? '')) ?: null;

            if ($isNew) {
                $company->status_id = 1;
            } elseif ($isAdmin && array_key_exists('status_id', $data)) {
                // Current/Archived is Admin-only -- a Company Admin editing
                // their own profile can't accidentally (or deliberately)
                // archive their own company out from under themselves.
                $company->status_id = (int)$data['status_id'] === 1 ? 1 : 0;
            }

            $company->save();
            $company->load(['country', 'region']);

            if ($isNew) {
                // Every new company starts with the default CanNACHI
                // Standards of Practice pages, editable/deletable afterward
                // (see StandardsController::seedDefaults()) -- the same
                // content a full DB reset seeds for every company.
                StandardsController::seedDefaults((int)$company->id);
            }

            $actionLabel = $isNew ? "Created company" : "Updated company";
            static::logActivity("{$actionLabel}: {$company->company_name}", 'Companies');

            return [
                'success'  => true,
                'company_id' => $company->id,
                'data'     => $company->toArray(),
                'rowHtml'  => self::renderRow($company),
                'messages' => ['Company saved successfully.']
            ];
        } catch (\Throwable $e) {
            static::logActivity("Company save error: " . $e->getMessage(), 'Companies');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

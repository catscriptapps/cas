<?php
// /src/Controller/StandardsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Standard;
use App\Utils\IdEncoder;
use App\Traits\RecentActivityLogger;
use Src\Service\AuthService;

/**
 * Manages a company's Standards of Practice -- an ordered sequence of
 * free-form rich-text "pages" referenced verbatim on every PDF report the
 * company generates (see PDF generation, not yet built). No section/
 * category grouping and no per-inspection selection, matching legacy's
 * hit_standards_txts exactly in scope.
 */
class StandardsController
{
    use RecentActivityLogger;

    /**
     * Tags allowed through from the rich-text editor's output. Strips
     * anything else (script/iframe/on*-handler-bearing markup) before a
     * page is ever persisted -- Quill's own output never uses these tags,
     * so this is pure defense-in-depth against a crafted paste, not a
     * feature restriction.
     */
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><strike><ol><ul><li><h1><h2><h3><h4><h5><h6><blockquote><a><span><div>';

    private static function sanitize(string $html): string
    {
        return strip_tags($html, self::ALLOWED_TAGS);
    }

    /**
     * List the signed-in Company Admin's Standards pages, ordered by
     * pos_index (legacy defined this column but never actually applied it
     * in its queries -- worth actually enforcing here).
     */
    public function index(): void
    {
        $companyId = AuthService::currentUser()->company_id ?? 0;

        $pages = Standard::where('company_id', $companyId)
            ->orderBy('pos_index', 'asc')
            ->get();

        $html = '';
        foreach ($pages as $index => $page) {
            $html .= self::renderCard($page, $index + 1);
        }

        $GLOBALS['standardsRows'] = $html;
        $GLOBALS['standardsCount'] = $pages->count();
    }

    /**
     * Render a single Standards page as its list-view card.
     */
    public static function renderCard(Standard $page, int $pageNumber): string
    {
        $encodedId = IdEncoder::encode((int)$page->id);
        $path = __DIR__ . '/../../resources/views/components/standards/standard-card.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<div class='p-4 text-red-500'>Render Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        return ob_get_clean();
    }

    /**
     * Create or update a Standards page. New pages are appended to the end
     * of the ordering; editing a page never changes its position -- that's
     * handled separately by reorder(), matching Cover Pages' split between
     * content editing and the click-to-place reorder icon.
     */
    public function save(array $data): array
    {
        try {
            $companyId = AuthService::currentUser()->company_id ?? 0;
            if (!$companyId) throw new \Exception("No company associated with this account.");

            $encodedId = $data['encoded_id'] ?? null;
            $isNew = empty($encodedId);

            $content = self::sanitize(trim($data['content'] ?? ''));
            if ($content === '' || $content === '<p><br></p>') {
                throw new \Exception("Page content cannot be empty.");
            }

            $pageId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $page = $pageId ? Standard::where('id', $pageId)->where('company_id', $companyId)->first() : new Standard();
            if (!$page) throw new \Exception("Standards page not found.");

            $page->company_id = $companyId;
            $page->content = $content;

            if ($isNew) {
                $page->pos_index = (int)(Standard::where('company_id', $companyId)->max('pos_index') ?? -1) + 1;
            }

            $page->save();

            static::logActivity($isNew ? "Added a Standards page" : "Updated a Standards page", 'Standards');

            $pages = Standard::where('company_id', $companyId)->orderBy('pos_index', 'asc')->get();
            $fullHtml = '';
            foreach ($pages as $index => $p) {
                $fullHtml .= self::renderCard($p, $index + 1);
            }

            return [
                'success'  => true,
                'fullHtml' => $fullHtml,
                'messages' => [$isNew ? 'Page added.' : 'Page updated.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Delete a Standards page and re-sequence the remaining pages'
     * pos_index so there's no gap.
     */
    public function delete(?string $encodedId): array
    {
        try {
            $companyId = AuthService::currentUser()->company_id ?? 0;
            $pageId = $encodedId ? IdEncoder::decode($encodedId) : null;
            $page = $pageId ? Standard::where('id', $pageId)->where('company_id', $companyId)->first() : null;

            if (!$page) {
                return ['success' => false, 'messages' => ['Standards page not found.']];
            }

            $page->delete();

            $remaining = Standard::where('company_id', $companyId)->orderBy('pos_index', 'asc')->get();
            foreach ($remaining as $index => $p) {
                $p->pos_index = $index;
                $p->save();
            }

            static::logActivity("Deleted a Standards page", 'Standards');

            $fullHtml = '';
            foreach ($remaining as $index => $p) {
                $fullHtml .= self::renderCard($p, $index + 1);
            }

            return [
                'success'  => true,
                'fullHtml' => $fullHtml,
                'messages' => ['Page deleted.'],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Persist a click-to-place reorder -- the full set of encoded ids in
     * their new order, mirroring Cover Pages' reorder endpoint.
     */
    public function reorder(array $encodedIds): array
    {
        try {
            $companyId = AuthService::currentUser()->company_id ?? 0;

            foreach ($encodedIds as $index => $encodedId) {
                $id = IdEncoder::decode((string)$encodedId);
                if (!$id) continue;
                Standard::where('id', $id)->where('company_id', $companyId)->update(['pos_index' => $index]);
            }

            static::logActivity("Reordered Standards pages", 'Standards');

            return ['success' => true, 'messages' => ['Order updated.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Seeds the default Standards of Practice pages for one company, only
     * if it doesn't already have any -- safe to call from both a full DB
     * reset (see scripts/reset/standards.php) and new-company creation
     * (see CompaniesController::save()) without ever clobbering a company
     * that has already customized or deleted its pages. Content is
     * transcribed from the CanNACHI Standards of Practice pages of
     * legacy_inspection_report.pdf, the same source document the default
     * Questions bank was built from -- one page per Building System
     * category, matching the granularity a company would actually want to
     * edit or reorder independently rather than the source PDF's own
     * incidental page breaks.
     */
    public static function seedDefaults(int $companyId): int
    {
        if (Standard::where('company_id', $companyId)->exists()) {
            return 0;
        }

        $inserted = 0;
        foreach (self::defaultPages() as $index => $content) {
            Standard::create([
                'company_id' => $companyId,
                'content'    => $content,
                'pos_index'  => $index,
            ]);
            $inserted++;
        }
        return $inserted;
    }

    /**
     * Builds one Building-System category page: a heading followed by the
     * "required" and "not required" bullet lists the source PDF renders as
     * a two-column table -- rendered sequentially instead, since Quill's
     * default toolbar (no table module) can't author real tables, and this
     * is just as readable as free-form rich text.
     */
    private static function categoryPage(string $category, array $required, array $notRequired): string
    {
        $requiredItems = implode('', array_map(fn($item) => '<li>' . $item . '</li>', $required));
        $notRequiredItems = implode('', array_map(fn($item) => '<li>' . $item . '</li>', $notRequired));

        return "<h3>{$category}</h3>"
            . '<p><strong>The inspector is required to:</strong></p>'
            . "<ul>{$requiredItems}</ul>"
            . '<p><strong>The inspector is not required to:</strong></p>'
            . "<ul>{$notRequiredItems}</ul>";
    }

    /**
     * @return string[] ordered list of default page HTML content
     */
    private static function defaultPages(): array
    {
        return [
            // 1. General conditions and limitations of the inspection
            '<h2>Standards of Practice</h2>'
            . '<h3>General Conditions and Limitations of the Inspection</h3>'
            . '<p>A Home Inspection is a non-invasive, visual examination of a residential dwelling, performed for a fee in accordance with the CanNACHI Standards of Practice, which is designed to identify observed material defects within specific components of said dwelling. Components may include any combination of mechanical, structural, electrical, plumbing, or other essential systems or portions of the home, as identified and agreed to by the Client and Inspector, prior to the inspection process.</p>'
            . '<p>A Home Inspection is intended to assist in evaluation of the overall condition of the dwelling. The inspection is based on observation of the visible and apparent condition of the structure and its components on the date of the inspection and not a prediction of future conditions. The inspector is not expected to perform calculations or analyze any part of the building or component thereof for strength, adequacy, or compliance with any regulatory requirements.</p>'
            . '<p>A Home Inspection will not reveal every concern that exists or ever could exist, but only those material defects observed on the day of the inspection.</p>'
            . '<p>A Material Defect is a condition of a residential real property or any portion of it that would have a significant adverse impact on the value of the real property or that involves an unreasonable risk to people on the property. The fact that a structural element, system or subsystem is near, at or beyond the end of the normal useful life of such a structural element, system or subsystem is not by itself a material defect.</p>'
            . '<p>The inspector is not required to provide cost estimates, quotations, or comment on construction techniques for any repairs, modifications or improvements.</p>'
            . '<p>The inspection will not include anything that is concealed, underground, not available for inspection, and not accessible for inspection at the time of inspection.</p>'
            . '<p>The Inspection Report shall describe and identify in written format the inspected systems, structures, and components of the dwelling and shall identify material defects observed. Inspection Reports may contain recommendations regarding conditions reported or recommendations for correction, monitoring or further evaluation by professionals, but this is not required.</p>'
            . '<p>The inspector will not perform any task, enter any area, or disturb any existing conditions where, in the inspector\'s judgment, the safety of the inspector is endangered or damage could result.</p>'
            . '<p>The CanNACHI Standards of Practice does not cover asbestos, radon gas, lead paint, urea formaldehyde, toxic or inflammable chemicals, etc. Where inspectors are qualified to carry out such inspections, they may do so after receiving approval from the client and for an additional fee.</p>'
            . '<p><em>Canadian National Association of Certified Home Inspectors</em></p>',

            // 2. Electrical
            self::categoryPage('Electrical', [
                'Service entrance cable and location and integrity of the insulation, drip loop, or separation of conductors at weatherheads and clearances from grade or rooftops.',
                'Main service panel, auxiliary panels and location.',
                'The means for disconnecting the service main.',
                'Panel overcurrent protection and system grounding.',
                'Branch circuit wiring and related overcurrent protection.',
                'Report on any unused circuit breaker panel openings that are not filled.',
                'Amperage and voltage ratings of the main service panel.',
                'A representative number of switches, receptacles, lighting fixtures, AFCI receptacles.',
                'Test all Ground Fault Circuit Interrupter (GFCI) receptacles and GFCI circuit breakers observed and deemed to be GFCI\'s during the inspection using a GFCI tester.',
                'Outlets noted above are to be checked for polarity and grounding.',
                'All exterior outlets and those within five feet of plumbing fixtures will be checked for polarity, grounding and ground fault circuit protection.',
                'Report the absence of smoke detectors.',
                'Report the presence of solid conductor aluminum branch circuit wiring if readily visible.',
            ], [
                'Insert any tool, probe or device into the main panel board, sub-panels, distribution panel boards, or electrical fixtures.',
                'Secondary wiring systems such as low voltage wiring, telephone wiring, cable television wiring, etc.',
                'Any components not related to the primary electrical systems such as security systems, swimming pool wiring and time-control devices.',
                'Inspect private or emergency electrical supply sources, including but not limited to generators, windmills, solar panels, or battery or electrical storage facilities.',
                'Provide or remove power for equipment.',
                'Inspect or test de-icing equipment.',
                'Conduct voltage drop calculations.',
                'Determine the accuracy of labelling.',
                'Verify the service ground.',
                'Test the operation of smoke detectors.',
                'Dismantle, remove, adjust or perform any task on any electrical equipment that would require a qualified tradesperson to perform.',
                'Insert or remove fuses, or operate circuit breakers.',
            ]),

            // 3. Permanently Installed Heating and Cooling Systems
            self::categoryPage('Permanently Installed Heating and Cooling Systems', [
                'The heating systems using normal operating controls and describe the energy source and heating methods.',
                'Furnace and distribution system, including fans, ducts, dampers, supports, filters, insulation, registers and humidifiers.',
                'Boilers and distribution system including pumps, piping, valves, supports, insulation, radiators and convectors.',
                'Flue piping, vents, and chimneys.',
                'Heat recovery ventilator.',
                'Interior fuel storage equipment supply piping, venting, supports, and evidence of leakage.',
                'Cooling equipment and distribution system including fans, ducts, dampers, supports, filters, insulation, registers and piping.',
                'The presence of manufacturer\'s build-in safety controls.',
                'The presence of a heat source in each room.',
                'Test system using the thermostat or other similar standard operating controls.',
                'Readily accessible and removable panel covers designed for homeowner access may be removed for inspection purposes.',
            ], [
                'Inspect or evaluate interiors of flues or chimneys, fire chambers, heat exchangers, humidifiers, dehumidifiers, electronic air filters, solar heating systems or fuel tanks.',
                'Determine the uniformity, temperature, flow, balance, distribution, size, capacity, adequacy, BTU, or supply adequacy of the heating system.',
                'Any portable heating/cooling, humidifying, dehumidifying or air cleaning equipment.',
                'Activate any HVAC systems when ambient temperatures or when other circumstances are not conducive to safe operation or may damage the equipment.',
                'Evaluate fuel quality.',
                'Verify thermostat calibration, heat anticipation or automatic setbacks, timers, programs or clocks.',
                'Examine electrical current, coolant fluids or gases, or coolant leakage.',
                'Dismantle, remove, adjust or perform any function on any heating or cooling equipment that would require a qualified tradesperson to perform.',
                'Light or ignite pilot flames.',
                'Change settings or conditions on equipment.',
            ]),

            // 4. Roofing
            self::categoryPage('Roofing', [
                'Roof covering materials.',
                'Roof penetrations and flashings.',
                'Chimneys.',
                'Skylights.',
                'Roof drainage components including gutters and downspouts.',
                'Observe and report evidence of water penetration.',
                'General structure of the roof from the readily accessible panels, doors or stairs or hatch.',
            ], [
                'Accessories that do not make up part of the roofing such as lightning arrestor systems, antennae, solar heating systems, de-icing equipment.',
                'Predict the service life expectancy of the roof.',
                'Inspect underground downspout diverter drainage pipes.',
                'Move or disturb insulation.',
                'Perform a water test.',
                'Warrant or certify or guarantee the roof.',
                'Walk on roofing where in judgement of the inspector could be dangerous or cause damage.',
            ]),

            // 5. Exterior
            self::categoryPage('Exterior', [
                'Exterior wall covering/surfaces, eaves and trim.',
                'Doors, windows, and flashings.',
                'Garages and carports that are attached to the main building.',
                'All exterior doors, decks, stoops, steps, stairs, porches, railings, eaves, soffits and fascia(s).',
                'Balconies including stairs, guards and railings.',
                'Observe and report impact of lot grading and vegetation.',
                'Retaining walls when these are likely to adversely affect the structure.',
                'Walkways and driveways on the building.',
                'Test the operation of power operated garage door openers, including the stop and automatic reverse functions.',
            ], [
                'Geological, hydrological and/or ground and soil conditions.',
                'Yard fencing.',
                'Seasonal accessories such as removable storm windows, storm doors, screens and shutters.',
                'Storage sheds and other structures not part of the building. Any items or facilities not directly related to the building structure, such as swimming pools, saunas, hot tubs, tennis courts, etc.',
                'Seawalls, break-walls and docks.',
                'Playground equipment or recreation facilities.',
                'Erosion control and earth stabilization measures.',
                'Drain fields or drywells, septic systems or cesspools.',
                'Water wells or springs.',
                'Determine the integrity of the thermal window seals or damaged glass.',
                'Verify or certify safe operation of any auto reverse or safety function of a garage door.',
            ]),

            // 6. Structure
            self::categoryPage('Structure', [
                'Visible foundation walls.',
                'Floors, columns, walls, roofs, attics.',
                'Report any general indications of foundation movement observed by the inspector, such as but not limited to drywall cracks, brick cracks, out-of-square door frames or floor slopes and concrete wall cracks.',
                'Report on any cutting, notching and boring of framing members which may present a structural or safety concern.',
                'Chimneys.',
                'Wood in contact or near soil.',
                'Crawl spaces, basements.',
                'Observe and report any evidence of water penetration and condensation.',
                'Observe and report any evidence of deterioration from insects, rot, or fire.',
            ], [
                'Inspect areas that are not reasonably accessible or visible.',
                'Enter any crawlspaces that are not readily accessible or where entry could cause damage or pose a hazard to the inspector.',
                'Move stored items or debris.',
                'Identify size, spacing, span, location or determine adequacy of foundation bolting, bracing, joists, joist spans or support systems.',
                'Provide any engineering or architectural service.',
                'Report on the adequacy of any structural system or component.',
            ]),

            // 7. Insulation & Ventilation
            self::categoryPage('Insulation & Ventilation', [
                'Insulation and vapour barriers in accessible attics, crawlspaces and unfinished basements.',
                'Ventilation of attics and unheated crawl spaces.',
                'Report on the general absence or lack of insulation.',
                'Operate exhaust fan ventilation systems.',
            ], [
                'Concealed insulation and vapour barrier systems.',
                'Inspect areas that are not reasonably accessible or visible.',
                'Move, touch, or disturb insulation or vapour barriers.',
                'Identify the composition or exact R-value of insulation material.',
                'Determine the types of materials used in insulation or wrapping of pipes, ducts, jackets, boilers, and wiring.',
                'Determine the adequacy of ventilation.',
            ]),

            // 8. Plumbing
            self::categoryPage('Plumbing', [
                'Verify the presence of and identify the location of the main water shutoff valve.',
                'Water supply piping into house and within house, pipe supports and insulation.',
                'Drain, waste, and vent piping, pipe supports and insulation.',
                'Inspect the water heating equipment, including combustion air, venting, connections, energy sources, seismic bracing, and verify the presence or absence of temperature-pressure relief valves and/or Watts 210 valves.',
                'Inspect the drainage sump pumps and test pumps with accessible floats.',
                'Presence of cross-connections that could contaminate the potable water.',
                'Water volume and pressure should be tested by opening the faucets to obtain a reasonable flow of one or more fixtures simultaneously, and at various locations in the house.',
                'Water drainage should be tested by draining one or more fixtures simultaneously, and at various locations.',
                'Test the water supply by operating valves and faucets.',
                'Observe and report any leaks in the piping systems.',
                'Determine if the water supply is public or private.',
                'Inspect and report as in need of repair toilets that have cracks in the ceramic material, are improperly mounted on the floor, leak, or have inoperable tank components.',
                'Determine the presence and location of accessible clean-outs for the drain/waste/vent piping.',
            ], [
                'Ignite or extinguish fires, pilot lights, change settings or conditions on equipment.',
                'Determine the exact flow rate, volume, pressure, temperature, or adequacy of the water supply.',
                'Inspect interiors of flues or chimneys, water softening or filtering systems, well pumps, tanks, safety or shut-off valves, floor drains, lawn sprinkler systems or fire sprinkler systems.',
                'Operate any valves other than those used on a regular or daily basis.',
                'Determine the water quality or potability or the reliability of the water supply or source.',
                'Foundation drainage system and yard piping.',
                'Inspect clothes washing machines or their connections.',
                'Test shower pans, tub and shower surrounds or enclosures for leakage.',
                'Evaluate the compliance with local conservation or energy standards, or the proper design or sizing of any water, waste or venting components, fixtures or piping.',
                'Determine the effectiveness of anti-siphon, back-flow prevention or drain-stop devices.',
                'Determine whether there are sufficient clean-outs for effective cleaning of drains.',
                'Evaluate gas, liquid propane or oil storage tanks.',
                'Inspect any private sewage waste disposal or septic system or component thereof.',
                'Inspect water treatment systems or water filters.',
                'Inspect water storage tanks, pressure pumps or bladder tanks.',
                'Evaluate wait time to obtain hot water at fixtures, or perform testing of any kind to water heater elements.',
                'Test, operate, open or close safety controls, manual stop valves and/or temperature or pressure relief valves.',
                'Determine the existence or condition of polybutylene plumbing.',
                'Dismantle, remove, adjust or perform any function on any plumbing equipment that would require a qualified tradesperson to perform.',
            ]),

            // 9. Interiors
            self::categoryPage('Interiors', [
                'Floors, walls, ceilings and trim.',
                'Fire separating walls and party walls.',
                'Stairs, guards and railings.',
                'Observe condition of permanently installed counters and cabinets.',
                'Observe and report on any evidence of water penetration and condensation.',
                'The presence of smoke detectors.',
                'Randomly select and operate where reasonably accessible a representative number of doors and windows.',
            ], [
                'Treatments such as paint, wallpaper, carpeting, blinds, drapes, and other similar treatments.',
                'Kitchen, bathroom, and laundry appliances.',
                'Observe fireplace insert installation.',
                'Solid fuel burning appliances, including fireplaces and wood stoves.',
                'Any items or facilities not directly related to the interior systems and components such as swimming pools, saunas, hot tubs, ponds and water falls.',
                'Move furniture, stored items, or any coverings like carpets or rugs in order to inspect the concealed floor structure.',
                'Move drop ceiling tiles.',
                'Operate or examine any sauna, steam-jenny, kiln, toaster, plug-in kitchen appliances, or other ancillary devices.',
                'Inspect elevators.',
                'Inspect remote controls.',
                'Inspect appliances.',
                'Inspect items not permanently installed.',
                'Examine or operate any above-ground, movable, freestanding, or non-permanently installed pool/spa, recreational equipment or self-contained equipment.',
            ]),
        ];
    }
}

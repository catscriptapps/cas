<?php
// /scripts/reset/questions.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionField;

/**
 * Resets the questions table and its two child tables (answer options and
 * fill-in-the-blank fields), then seeds the default question bank -- based
 * on HomeWorks Advantages Inc.'s legacy CanNACHI-standard inspection report
 * (legacy_inspection_report.pdf) -- identically for every company. This is
 * the starting template every company gets; the inspector-entered findings
 * from that source report (checked boxes, narrative notes) are deliberately
 * NOT carried over, only the reusable question/option library itself.
 */
function resetQuestionsTable(): array
{
    $messages = [];
    try {
        Capsule::schema()->disableForeignKeyConstraints();

        Capsule::schema()->dropIfExists('question_fields');
        Capsule::schema()->dropIfExists('question_options');
        Capsule::schema()->dropIfExists('questions');

        Capsule::schema()->create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('section_id');
            $table->string('question_title', 500);
            // 0 = Observe & Report, 1 = Perform Tasks
            $table->unsignedTinyInteger('answer_mode')->default(0);
            $table->boolean('is_condition')->default(false);
            $table->integer('pos_index')->default(0);
            $table->integer('status_id')->default(1);
            $table->datetime('date_created')->nullable();
            $table->datetime('timestamp')->nullable();
        });

        Capsule::schema()->create('question_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('question_id');
            $table->string('option_text', 300);
            $table->integer('pos_index')->default(0);
        });

        Capsule::schema()->create('question_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('question_id');
            $table->string('prefix', 300)->nullable();
            $table->string('suffix', 300)->nullable();
            $table->integer('pos_index')->default(0);
        });

        $messages[] = "created 'questions', 'question_options', and 'question_fields' table structures.";

        // Each question: title, mode (0=Observe&Report, 1=Perform Tasks),
        // options[], fields[] (each field is [prefix, suffix]).
        $sectionQuestions = [
            'Roofing' => [
                ['Main Roof', 0, ['Multiple layers noted', 'Roof covering: Shingles', 'Roof covering: Steel', 'Roof covering: Wood', 'Roof covering: Slate', 'Roof covering: Other product', 'Roof venting recommended', 'Roof deflections noted', 'Ice damming noted', 'Snow cover not visible'], []],
                ['Lower Roof', 0, ['Multiple layers noted', 'Roof covering: Shingles', 'Roof covering: Steel', 'Roof covering: Wood', 'Roof covering: Slate', 'Roof covering: Other product', 'Roof venting recommended', 'Roof deflections noted', 'Ice damming noted', 'Not visible due to snow cover'], []],
                ['Flat Roof / Drainage', 0, ['Rolled asphalt', 'Tar & Gravel', 'Rubber', 'Ice damming noted'], []],
                ['Flashings', 0, ['Missing', 'Repair recommended'], []],
                ['Roof Penetrations', 0, ['Repair recommended'], []],
                ['Skylights / Sun Tunnel', 0, ['Operable', 'Not tested'], []],
                ['Chimneys', 0, ['Mason chimney noted', 'Mason cap with drip edge recommended', 'Manufactured chimney rain cap recommended', 'Chimney liner recommended'], []],
                ['Flue Pipe', 0, ['Rain cap recommended', 'Repairs recommended'], []],
                ['Plumbing Vent Pipes', 0, [], [['Minimum height above roof surface', 'inches']]],
                ['Gutters & Downspouts', 1, ['Recommend cleaning and downspout extension (min. 6 ft from home)', 'Extend downpipe to lower gutters/ground and away from home', 'Downspouts below grade not inspected - monitor as needed', 'Downpipes on walkways/driveways are a slip/trip hazard'], []],
                ['Evidence of Water Penetration', 0, ['None at time of inspection'], []],
            ],
            'Exterior' => [
                ['Wall Surfaces', 0, ['Brick', 'Stone', 'Stone/brick veneer', 'Vinyl', 'Aluminum', 'Wood', 'Engineered siding', 'Stucco', 'Other'], []],
                ['Brick / Stone Veneer', 0, ['Repoint and seal as needed - possible water penetration', 'Step cracks noted', 'Step cracks/movement - monitor, repair as needed', 'Spalling bricks noted - repair as needed'], []],
                ['Grading', 0, ['Wood/soil contact not recommended - can increase wood rot', 'Brick/soil contact not recommended - can cause brick damage', 'Foundation wall should be exposed 8 inches above grade', 'Not visible due to snow, storage, or other obstruction', 'Grade repair needed - all grade must slope away from home'], []],
                ['Hurricane Straps (Mobile / Modular Homes)', 0, ['Unknown if professionally installed', 'Not seen'], []],
                ['Window Wells', 0, ['Not all window wells are egress - repair as needed', 'Weeping tile not noted in all window wells', 'Concern for water penetration if drainage is poor'], []],
                ['Exposed Foundation Wall Cracking', 0, ['Minor', 'Moderate', 'Extensive', 'Not visible (snow, storage, shrubs, vines, etc.)'], []],
                ['Parging of Exposed Foundation Walls', 0, ['Repair recommended', 'Recommended'], []],
                ['Eaves, Soffit & Fascia', 0, ['Wood', 'Aluminum', 'Vinyl', 'Vented', 'Not vented', 'Solid wood behind covers unknown - repair as needed'], []],
                ['Entryway Doors, Flashing & Trim', 0, ['Repair weatherstripping/seals as needed'], []],
                ['Doorbell', 1, ['Repair recommended as needed for safety', 'Camera doorbell not part of inspection'], []],
                ['Exterior Caulking', 0, ['Exterior caulking repair recommended - reduces energy loss and water penetration'], []],
                ['Exterior Paint and Stains', 0, ['Repairs needed for weather protection'], []],
                ['Garages', 1, ['Garage door opener tested', 'Garage door opener repair recommended', 'No garage door opener installed', 'Garage door opener installed one side only'], []],
                ['Porches', 0, ['Deflections noted'], []],
                ['Decks', 0, ['Repair/replace as needed', 'Wood/soil contact not recommended - can increase wood rot'], []],
                ['Stairs', 0, ['Does not follow the 7-11 rule (7 in. risers / 11 in. treads)', 'Trip hazards noted - repair as needed for safety', 'Smallest tread should be no less than 9 1/4 inches', 'All risers should be even heights', 'Stair stringers too long - added supports recommended'], []],
                ['Guards & Railings', 0, ['Handrails recommended at 3+ steps', 'Guardrails recommended where fall risk exceeds 23 5/8 inches', 'Pickets must not exceed 4 inches apart'], []],
                ['Vegetation', 0, ['Trim/remove trees from around home as needed'], []],
                ['Patios / Walkways', 0, ['Trip/slip hazard'], []],
                ['Driveways', 0, ['Grade repair'], []],
                ['Snow Cover', 0, ['Full exterior snow cover reduces visibility of roof, decks, patios, walkways, foundation, grading, etc.'], []],
                ['Exterior Out Buildings', 0, ['Not inspected'], []],
            ],
            'Structure' => [
                ['Foundation', 0, ['Exposed interior wall cracking: Minor', 'Exposed interior wall cracking: Moderate', 'Exposed interior wall cracking: Extensive', 'Block foundation', 'Poured concrete', 'Wood (may carry insurance premium)', 'Stone (may carry insurance premium)', 'Not visible (insulation, storage, finished basement, other)'], []],
                ['Exposed Concrete Floor Cracking', 0, ['Minor', 'Moderate', 'Extensive', 'Dirt floor', 'Not visible'], []],
                ['Garage Concrete Floor Cracking', 0, ['Minor', 'Moderate', 'Extensive', 'Dirt floor', 'Not visible'], []],
                ['Floors', 0, ['Floor deflections noted', 'Contractor recommended for repair/assessment'], []],
                ['Beams', 0, ['Not visible'], []],
                ['Columns', 0, ['Not visible'], []],
                ['Joists', 0, ['Not visible'], []],
                ['Wood Frame Walls', 0, ['Wood/concrete contact noted - not recommended, can increase wood rot and possible mold'], []],
                ['Roof Sheathing', 0, ['Board', 'Plywood sheeting', 'OSB sheeting', 'Other'], []],
                ['Chimney / Flue Pipe', 0, ['Flue pipe', 'Mason chimney', 'Manufactured chimney'], []],
                ['Evidence of Deterioration from Insects / Fire', 0, ['Not at time of inspection'], []],
                ['Evidence of Abnormal Condensation', 0, ['None', 'Slight', 'Moderate', 'Extensive'], []],
                ['Evidence of Moisture Seepage', 0, ['None', 'Slight', 'Moderate', 'Extensive', 'Wet', 'Stains', 'Mould', 'Efflorescence', 'Rust', 'Peeling paint', 'Wall damage', 'Lifting floor tiles'], []],
                ['Anticipation of Moisture Seepage', 0, ['Low', 'Typical', 'High'], []],
            ],
            'Insulation and Ventilation' => [
                ['Attic Hatch', 1, ['Seal/repair recommended', 'Undersized access - repair as needed', 'Possible mold growth noted at access', 'Treat as untested - recommended', 'Yearly attic inspections recommended'], []],
                ['Attic', 0, ['Improper power exhaust venting', 'Power exhaust venting not visible'], []],
                ['Insulation (Attic)', 0, ['Inconsistent', 'Incomplete', 'Disturbed', 'Wet', 'Possible asbestos - testing needed to confirm', 'Insulation top-up recommended'], []],
                ['Ventilation Obstructed At', 0, ['Ridge', 'Roof', 'Soffit', 'Condensation noted', 'Mildew/possible mold - testing recommended to confirm'], []],
                ['Basement / Crawl Space Height', 0, ['Basement height requirement: 7 ft 7 in', 'Crawl space'], []],
                ['Moisture Barrier', 0, ['Not visible due to finished basement/storage/other', 'Damaged', 'Incomplete', 'Dirt floor coverage - repair/replace as needed', 'Not visible'], []],
                ['Insulation (Basement / Crawl Space)', 0, ['Incomplete', 'Damaged', 'Not visible due to finished basement', 'Not visible (storage or other)'], []],
                ['Ventilation (Basement / Crawl Space)', 0, ['Obstructed', 'Condensation', 'Rot', 'Mildew/possible mold - testing recommended to confirm'], []],
                ['Wall Insulation, Main & Upper Levels', 0, ['Not visible'], []],
                ['Pipes in Unheated Areas', 0, ['Condensation', 'Insulation present', 'Possible asbestos wrap - treat as untested'], []],
                ['Ducts in Unheated Areas', 0, ['Insulation present', 'Condensation', 'Rust', 'Possible asbestos wrap - treat as untested', 'Not visible'], []],
                ['Power Exhaust Fan Ventilation', 0, ['Power venting should be at least 12 inches from grade/decking', 'Mice entry and snow load concerns', 'Not all exit venting locations may be found', 'Improper venting can return moist air into the attic and cause mold'], []],
                ['Kitchen Exhaust', 1, ['Recirculating', 'Exhaust', 'Repair recommended if hood vent to surface distance is out of spec', 'Gas stove hood vent must vent to exterior - repair as needed', 'Solid steel smooth finish recommended'], []],
                ['Bathroom Exhaust', 1, ['Not found in all bathrooms', 'Repair recommended - operable window and/or power vent required', 'Vent pipes in attic not insulated - repair as needed', 'Power vent over shower/bath must be rated for wet locations and GFCI protected'], []],
                ['Radon Gas Venting', 0, ['Testing of radon gas not part of inspection', 'Rough-in not found', 'Radon venting not tested'], []],
                ['Dryer Vent', 0, ['Recommend straight metal duct for performance and fire safety', 'Solid steel smooth-finish insulated vent pipe recommended'], []],
            ],
            'Electrical' => [
                ['Service Entrance', 0, ['Underground', 'Overhead - recommend clear of trees/items within 10 ft radius'], []],
                ['Service Box / Meter', 0, ['Not found', 'Not accessible', 'Possible tampering', 'Recommend electrician to confirm tampering/amperage'], [['Amperage reading', 'Amps']]],
                ['Service Panel', 1, ['Not accessible', 'Fuses', 'Breakers', 'GFCI', 'Arc fault', 'Rust', 'Obsolete', 'Loose', 'Double taps', 'Crowded', 'Undersized', 'No inspection tags noted', 'Update recommended - electrician to review all panels', 'Panelboards must not be in coal bins, closets, bathrooms, stairways, or hazardous locations'], [['Panel amperage', 'Amps']]],
                ['Wires', 0, ['Damaged', 'Overheated', 'Loose', 'Abandoned'], []],
                ['Fuses / Breakers', 0, ['Damaged', 'Loose', 'Overfused', 'Linking', 'Worn/unlabeled - confirm amp size with electrician', 'Labeled but not confirmed accurate', 'No breaker/fuse noted - monitor for safety'], []],
                ['Dedicated Circuits', 0, ['Range/Stove', 'Dryer', 'A/C', 'Furnace', 'Baseboard heaters', 'Hot Tub', 'Sauna', 'Electric vehicle charger', 'Water Heater', 'Other'], []],
                ['Grounding', 0, ['Water pipe', 'Grounding rods', 'Not visible'], []],
                ['Branch Circuit Wiring', 0, ['Copper', 'Tin-coated copper - insurance concern, ESA inspection recommended', 'Aluminum - insurance concern, ESA inspection recommended', 'Knob & Tube - insurance concern, ESA inspection recommended', 'Paper-wrapped wire noted', 'Exposed', 'Loose', 'Unsupported', 'Abandoned', 'Damaged'], []],
                ['Knob & Tube Wiring', 0, ['Electrician recommended to confirm and repair as needed - insurance and safety concern'], []],
                ['Aluminum Wiring', 0, ['Anti-oxidant compound not noted - electrician recommended to repair'], []],
                ['Junction Boxes', 0, ['Required', 'Cover missing'], []],
                ['Receptacles', 0, ['Open ground - repair for safety', 'Open neutral - repair for safety', 'Hot/ground reverse', 'Reverse polarity - repair for safety', 'Open ground outlets (no ground plug)', 'Damaged', 'Loose', 'Burnt/overheated outlets noted', 'Inoperative', 'Painted over - repair/replace as needed'], []],
                ['Ground Fault Circuit Interrupters (GFCI)', 1, ['Exterior GFCI failed - repair for safety', 'Interior GFCI failed - repair for safety', 'Weather boxes recommended', 'Two GFCI breakers noted on same line - not recommended'], []],
                ['Switches', 1, ['Damaged', 'Loose', 'Three-way switch repair needed', 'Inoperative', 'Worn'], []],
                ['Lights', 1, ['Damaged', 'Loose', 'Inoperative - recommend testing all lights before closing'], []],
                ['Cover Plates', 0, ['Damaged', 'Loose', 'Missing - repair/replace as needed'], []],
            ],
            'Heating and Cooling' => [
                ['System Description', 0, ['Furnace', 'Boiler', 'Electric heating', 'Conventional efficiency', 'Mid efficiency', 'High efficiency'], []],
                ['Failure Probability', 0, ['Based on typical age/life cycle - manufacture date not found', 'Exact date of manufacture not found'], [['Estimated failure probability (1-15+ scale)', '']]],
                ['Furnace', 0, ['Forced air', 'Gravity', 'Fire/smoke dampers not tested', 'Humidifier (not tested)', 'Noisy fan/motor', 'Rust', 'Flaking in exchanger', 'Suggest obtaining HIP insurance', 'Recommend servicing by licensed technician', 'Dirty filter - replace as needed', 'Filter cover missing - replace as needed', 'No service tags noted'], []],
                ['Boiler', 1, ['Hot water', 'Steam', 'Noisy pump', 'Leakage', 'Corrosion', 'Recommend servicing by licensed technician', 'No service tags noted - annual inspection recommended'], []],
                ['Pumps', 0, ['Convection', 'Radiant', 'Noisy', 'Inoperative', 'Rust', 'Damaged', 'Overfusing', 'Recommend electrician'], []],
                ['Venting', 0, ['Chimney', 'Flue pipe', 'Power vent', 'Black ABS piping - upgrade required at time of replacement', 'System 636 piping'], []],
                ['Fuel Storage', 0, ['Natural Gas', 'Oil', 'LP', 'Supply piping', 'Venting', 'Supports', 'Leakage'], []],
                ['Presence of Automatic Safety Controls', 0, ['No gas shutoff found', 'Power wire should be BX steel protected', 'Switch in wrong location - repair as needed', 'Furnace on/off switch'], []],
                ['Thermostat', 1, ['Programmable'], []],
                ['Heat / Energy Recovery Ventilator (HRV)', 1, ['Not tested', 'Working, not performance tested', 'Dirty filters - clean/replace as needed', 'HRV required by code since January 1, 2017'], []],
                ['Electronic Air Filter', 1, ['Not tested'], []],
                ['Electric Heaters', 0, ['In-floor heaters', 'Baseboard heaters', 'Other', 'Heater damaged', 'Inoperative', 'Not tested', 'Repair by qualified personnel recommended'], []],
                ['Cooling', 1, ['Air conditioning', 'Heat pump', 'Condensate leakage', 'Unusual operating noise', 'Not tested', 'Winter conditions prevented testing', 'Coils damaged', 'Unit not level', 'Corrosion', 'Lines appear damaged', 'Insulation missing/deteriorated', 'No service tags noted'], [['Approx. age', 'Yrs']]],
                ['Presence of Permanent Heat Source in Each Room', 0, ['Not found in each room', 'Balancing of duct airflow recommended', 'Duct cleaning recommended'], []],
                ['Geothermal', 0, ["Not part of inspection - beyond home inspector's scope, full service by certified personnel recommended"], []],
            ],
            'Plumbing' => [
                ['Supply Piping', 0, ['Private', 'Public', 'Curb water shut-off located', 'Curb water shut-off not located', 'Plastic', 'Copper', 'Galvanized - leak and insurance concern', 'Lead - leak, insurance and health concern', 'Leaks - repair as needed', 'Support repair recommended'], []],
                ['Well', 0, ['Expert recommended to test flow rate/recovery/potability', 'Drilled well: min. 50 ft from septic system', 'Dug well: min. 100 ft from septic system', 'Well lid should be min. 18 inches above grade', 'Well not found'], []],
                ['Pump / Pressure Tank / Expansion Tank', 0, ['Pump in well not seen', 'Pressure tank air pressure not tested', 'Pressure tank not found', 'PRV noted'], []],
                ['Water Flow / Pressure', 1, ['Above average', 'Average', 'Below average', 'Expansion tank not tested for air pressure'], []],
                ['Distribution Water Lines', 0, ['Copper', 'Plastic', 'Galvanized steel - leak and insurance concern', 'Leaks - repair as needed', 'Rust - monitor, repair as needed', 'Water hammer/noise - repair as needed', 'Cross-connections - repair recommended', 'Support repairs recommended'], []],
                ['Waste Piping', 0, ['Plastic', 'Copper', 'Galvanized steel', 'Cast iron', 'Lead', 'S-traps noted - repair to P-trap recommended', 'P-traps recommended', 'Support repairs recommended', 'Rust noted - monitor, repair as needed', 'Active/past leaks - monitor, repair as needed', 'Slope repairs recommended', 'Sludge pump tested', 'Sludge pump repair recommended', 'Municipal sewer', 'Private septic - pump-out and inspection recommended', 'Backflow preventer noted, not tested'], []],
                ['Waste Pipe Ventilation', 0, ['Cheater venting noted - monitor, repair/replace as needed', 'Vent must be within 5 feet of fixture/trap for good drainage'], []],
                ['Sump Pumps', 0, ['Battery back-up power supply recommended', 'Recommend new pump/repair', 'Backflow preventer recommended', 'Extend waste pipe as far from home as possible', 'Hooked up to sewer/septic - repair recommended', 'Sump pump must be properly set up to prevent flooding', 'Regular testing recommended', 'Install correct sump pump well with lid to reduce humidity'], []],
                ['Floor Drain', 0, ['Primer valve repair recommended to stop sewer gas entry', 'Odour', 'Unsealed openings'], []],
                ['Water Heater', 0, ['Hot water on demand', 'Gas', 'Electric', 'Oil', 'Over 10 years old - may need replacement for insurance', 'PRV pipe extension needed for safety'], []],
                ['Water Heater Automatic Safety Controls', 0, ['Mixer valve not found'], [['Approx. age', 'Yrs'], ['Capacity', 'Litres']]],
                ['Fuel Storage', 0, ['Oil', 'Propane', 'Natural Gas', 'Supply piping'], []],
                ['Venting', 0, ['Chimney', 'Flue', 'Power vent', 'Black ABS - upgrade required at time of replacement', 'System 636 piping'], []],
                ['Main Valves', 0, ['Main water valve not found - ask owner for location'], []],
                ['Exterior Taps', 1, ['Interior shutoff valves not found - ask owner for proper winterizing'], []],
                ['Faucets', 1, ['Reverse tap(s) noted - repair as needed for safety'], []],
                ['Sinks', 1, ['Rust/finish damage noted', 'Leaks - repair as needed'], []],
                ['Bathtub(s) and Enclosure', 0, ['Damage noted - repair as needed', 'Loose plumbing - repair as needed'], []],
                ['Jet Tub', 1, ['Not tested', 'Jet tub not working', 'GFCI protection not found'], []],
                ['Shower Stall(s)', 1, ['Damage noted - repair as needed'], []],
                ['Toilet(s)', 1, ['Loose/leaking toilet noted - repair as needed', 'Water shutoffs not found - repair as needed'], []],
                ['Bathroom Rough-In', 0, ['Plumbing below cement floor not inspected/tested'], []],
                ['Bidet', 1, ['Not tested'], []],
            ],
            'Interior' => [
                ['Floor Coverings', 0, ['Incomplete', 'Transition repairs recommended', 'Damaged', 'Worn', 'Multiple layers'], []],
                ['Wall Damage', 0, ['Minor', 'Moderate', 'Extensive', 'Repairs noted'], []],
                ['Ceiling Damage', 0, ['Minor', 'Moderate', 'Extensive', 'Repairs noted'], []],
                ['Trim', 0, ['Incomplete', 'Damaged'], []],
                ['Stairways', 1, ['Should not be under 33.85 inches wide', 'Ceiling height should not be less than 6 ft 8 in from stair tread', 'Does not follow the 7-11 rule (7 in. risers / 11 in. treads)', 'Trip hazards noted - repair as needed for safety', 'Smallest tread should be no less than 9 1/4 inches, risers even'], []],
                ['Guards', 1, ['Guardrails recommended where fall risk exceeds 23 5/8 inches', 'Pickets must not exceed 4 inches apart', 'Guardrail height: 36 in for drops under 5 ft 9 in, 42 in for drops over'], []],
                ['Railings', 1, ['Handrails recommended at 3+ steps', 'Stair rails must be 36 inches high'], []],
                ['Doors', 1, ['Damaged', 'Fit not closing', 'Not latching', 'Missing', 'Balancing of doors recommended'], []],
                ['Windows', 1, ['Restricted operation due to winter conditions or furnishings'], []],
                ['Screens (Windows/Doors)', 0, ['Missing', 'Damaged'], []],
                ['Interior Caulking', 0, ['Repair recommended'], []],
                ['Separation Wall Between Garage & Dwelling', 0, ['No door closure', 'Door closure needs adjusting', 'Gas/fireproof seal compromised - repair holes and seal doors'], []],
                ['Party Walls', 0, ['Attic party wall fire protection compromised - repair for safety'], []],
                ['Smoke / Carbon Monoxide Detectors', 0, ['Not tested', 'Not found on every level', 'Recommended on every level and as required by law'], []],
                ['Gas Fireplace', 0, ['Not tested', 'No pilot', 'Other', 'On/off switch', 'Thermostat controlled', 'Blower fan not noted', 'Service by certified personnel recommended'], []],
                ['WETT Inspection Recommended', 0, ['Wood burning fireplace', 'Woodstove', 'Pellet stove', 'Wood burning furnace', 'Other'], []],
                ['Evidence of Water Penetration', 0, ['Not at time of inspection', 'Past water penetration noted'], []],
                ['Evidence of Abnormal Condensation', 0, ['Not at time of inspection', 'Past condensation noted'], []],
                ['Odour', 0, ['Not at time of inspection'], []],
                ['Mold Testing', 1, ['Air quality testing', 'Bulk samples', 'See additional comments'], []],
                ['Asbestos Testing', 1, ['Bulk samples - may help but never fully eliminate risk', 'Older building products may contain asbestos - treat as untested', 'No testing done day of inspection'], []],
            ],
        ];

        $companyIds = [1, 2];

        $questionCount = 0;
        foreach ($companyIds as $companyId) {
            foreach ($sectionQuestions as $sectionName => $questions) {
                $sectionId = Section::where('company_id', $companyId)->where('name', $sectionName)->value('id');
                if (!$sectionId) {
                    continue;
                }

                foreach ($questions as $posIndex => [$title, $mode, $options, $fields]) {
                    $question = Question::create([
                        'company_id'     => $companyId,
                        'section_id'     => $sectionId,
                        'question_title' => $title,
                        'answer_mode'    => $mode,
                        'is_condition'   => false,
                        'pos_index'      => $posIndex,
                        'status_id'      => 1,
                        'date_created'   => date('Y-m-d'),
                    ]);
                    $questionCount++;

                    foreach ($options as $optionIndex => $optionText) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $optionText,
                            'pos_index'   => $optionIndex,
                        ]);
                    }

                    foreach ($fields as $fieldIndex => [$prefix, $suffix]) {
                        QuestionField::create([
                            'question_id' => $question->id,
                            'prefix'      => $prefix,
                            'suffix'      => $suffix,
                            'pos_index'   => $fieldIndex,
                        ]);
                    }
                }
            }
        }

        $messages[] = "Successfully imported $questionCount questions with their answer options and fields.";

        $messages = array_merge($messages, seedSectionConditions([1, 2]));
    } catch (\Throwable $e) {
        $messages[] = 'questions tables error: ' . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}

/**
 * The standard "Conditions / Limitations" boilerplate every section of a
 * CanNACHI-standard report carries -- ported verbatim from legacy's master
 * "Home Inspections" template company (company_id 2 there), where these
 * live as ordinary hit_questions rows with is_condition=1, not as fixed PDF
 * template text. Same reason the new app's Question model already has an
 * is_condition column and resources/views/pdf/section.php already knows how
 * to render a "Conditions & Limitations" block: this is what it was built
 * for, just never seeded until now.
 *
 * Each entry is [title, options[]] -- answer_mode is always Observe & Report
 * (legacy never reads a "tasks" value for these) and none of them carry
 * fill-in fields (legacy's data has none either). Order matches legacy's
 * actual question_number render order per section, not alphabetized.
 */
function sectionConditionsData(): array
{
    return [
        'Roofing' => [
            ['Roof inspected by:', ['Ground Visual', 'Ladder at Edge', 'Binoculars', 'Drone', 'Walking on']],
            ['Inspection restricted due to:', ['Height', 'Slope', 'Snow', 'Ice', 'Rain', 'Wet', 'Trees', 'Potential Danger', 'Damage']],
            ['Restricted / No access to:', []],
            ['All water leaks past and active can lead to mold concerns', []],
            ['This report is an opinion of the general quality and condition of the roofing. As such the inspector cannot and does not offer an opinion or warranty as to whether the roof has leaked in the past, leaks now or is subject to future leakage.', []],
            ['Gutters, down pipes and subsurface drains are not water tested for leakage or blockage. These components require regular maintenance to avoid water problems at the roof and foundation.', []],
        ],
        'Exterior' => [
            ['All water leaks past and active can lead to mold concerns', []],
            ['Restricted / No access to:', []],
            ['Snow covering over:', []],
            ['Grading not visible due to snow', []],
            ['Restricted inspection due to:', ['Trees', 'Vines', 'Shrubs', 'Decks', 'Other']],
            ['This report does not include geological or soil conditions. For this information a Geotechnical Engineer should be consulted.', []],
            ['Outbuildings such as storage sheds etc. not related to the house are not included in the inspection.', []],
            ['All exterior window wells must have proper drainage which may not be visible at time of inspection due to being below grade.', []],
        ],
        'Structure' => [
            ['Restricted / No access to:', ['Attic space inspected from access hatch', 'Crawl space inspected from access hatch']],
            ['Concealed and/or structural components not inspected.', []],
            ['No engineering or structural analysis is performed during this inspection. A structural engineer should be consulted if necessary.', []],
            ['All water leaks past and active can lead to mold concerns', []],
        ],
        'Insulation and Ventilation' => [
            ['Restricted / No access to:', []],
            ['Power ventilator not tested', []],
            ['Air / vapour barrier continuity not inspected', []],
            ['Concealed insulation not tested', []],
            ['Determining the presence of asbestos or other hazardous materials is beyond the scope of this inspection.', []],
            ['Asbestos, Urea Formaldehyde Foam Insulation (UFFI) not part of inspection -- testing of such material recommended as needed', []],
            ['All spray rigid foam insulation must contain a fire retardant or be protected with such fire protection', []],
            ['Recommend all pipes and heat ducts in unheated areas be insulated', []],
        ],
        'Electrical' => [
            ['Restricted / No access to:', []],
            ['Recommend an electrician for service to the electrical system and for all electrical investigations and repairs', []],
            ['Services less than 100 amps may need upgrading for operation of larger electrical appliances.', []],
            ['Concealed or obstructed electrical components not inspected.', []],
            ['Power disconnected / shut off', []],
            ['Newer homes have GFCI protection for safety in wet areas; an upgrade is recommended for older homes not equipped with these devices.', []],
            ['Aluminum / Knob and Tube wiring connections should be checked by a licensed electrician familiar with said wiring systems.', []],
        ],
        'Heating and Cooling' => [
            ['Heating not tested', ['Gas Shut Off', 'Power Disconnected', 'Shut Off']],
            ['Automatic safety controls not tested', ['Fuel Storage not Visible', 'Circulating Pump not Tested', 'Chimney Clean Out not Opened']],
            ['Zone valves not tested or adjusted. Thermostats are not checked for calibration of timed functions.', []],
            ['Inspection of the furnace heat exchanger for evidence of cracks or holes can only be done by dismantling the unit. This is beyond the scope of this inspection. No pressure tests are performed on coolant systems, and no representation is made regarding coolant charge or line integrity.', []],
            ['Underground fuel storage tanks are not part of this inspection. Fuel storage requires inspection by certified personnel.', []],
            ['Recommend boiler and hot water steam systems be checked by certified personnel (beyond the scope of this inspection).', []],
        ],
        'Plumbing' => [
            ['Concealed / underground plumbing not inspected or judged for leaks or deterioration. Water treatment systems not inspected. Circulation / relief and main valves not tested. Solar systems not part of this inspection.', []],
            ['Restricted / No access to:', ['Gas Shut Off', 'Water Shut Off', 'Fixtures not Tested']],
            ['Testing for water quality, lead and other hazardous materials is not part of this inspection. Integrity of septic tanks and leaching beds is not part of this inspection. A licensed well driller should be consulted.', []],
            ['Integrity and capacity of well water supply installations is not part of this inspection. A licensed well driller should be consulted.', []],
            ['Septic system pump out and inspection recommended.', []],
            ['Well water recovery, flow and potability testing recommended.', []],
            ["Recommended minimum well to septic clearance is 50 ft for drilled and 100 ft for dug wells.", []],
            ['Septic tank baffle wall not fully seen without a septic pump-out', []],
            ['All water leaks past and active can lead to mold concerns', []],
        ],
        'Interior' => [
            ['Lack of historical clues due to new finishes and/or recent construction.', ['Yes']],
            ['Restricted access due to storage / furnishings.', []],
            ['Building products including, but not limited to, drywall, drywall compounds, floor tiles, ceiling tiles and stucco cannot be determined to be asbestos-free without further testing', []],
            ['Possibility of lead paint cannot be determined without further testing', []],
            ['It is always recommended that you obtain multiple quotes for all work to be performed by an independent contractor.', []],
            ['All water leaks active and past can lead to mold concerns', []],
            ['Restricted / No access to:', []],
            ['Detector types not determined: Smoke and/or Carbon Monoxide', []],
            ['Cosmetic finishes not commented on. Chimney efficiency is not commented on or judged. Condition of walls behind wallpaper, paneling and furnishings cannot be judged.', []],
            ['Any and all fixtures like fans, ceiling fans, central vacuums, and all appliances not inspected for operation or condition', []],
            ['Determining odours or stains is not included. Condition of flooring hidden by furniture, carpet or other covering is not inspected. Determining the rating of fire walls is beyond the scope of this inspection.', []],
            ['The inspection does not address compliance of basement apartments and accessory units. Consult your local town/city for regulatory requirements.', []],
        ],
    ];
}

/**
 * Adds the standard Conditions/Limitations questions to each given
 * company's existing sections -- additive and idempotent (skips any
 * company+section+title combination that already exists), so it's safe to
 * call both from a full table reset and, separately, against a live
 * database that already has real question banks and inspection answers in
 * it (see scripts/seed-section-conditions.php).
 */
function seedSectionConditions(array $companyIds): array
{
    $sectionConditions = sectionConditionsData();
    $count = 0;

    foreach ($companyIds as $companyId) {
        foreach ($sectionConditions as $sectionName => $conditions) {
            $sectionId = Section::where('company_id', $companyId)->where('name', $sectionName)->value('id');
            if (!$sectionId) {
                continue;
            }

            $nextPos = (int)(Question::where('company_id', $companyId)->where('section_id', $sectionId)->max('pos_index') ?? -1) + 1;

            foreach ($conditions as [$title, $options]) {
                $exists = Question::where('company_id', $companyId)
                    ->where('section_id', $sectionId)
                    ->where('is_condition', true)
                    ->where('question_title', $title)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $question = Question::create([
                    'company_id'     => $companyId,
                    'section_id'     => $sectionId,
                    'question_title' => $title,
                    'answer_mode'    => Question::MODE_OBSERVE,
                    'is_condition'   => true,
                    'pos_index'      => $nextPos++,
                    'status_id'      => 1,
                    'date_created'   => date('Y-m-d'),
                ]);
                $count++;

                foreach ($options as $optionIndex => $optionText) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => $optionText,
                        'pos_index'   => $optionIndex,
                    ]);
                }
            }
        }
    }

    return ["Seeded $count Condition/Limitation question(s)."];
}

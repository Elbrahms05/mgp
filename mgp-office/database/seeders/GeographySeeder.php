<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Domains\Geography\Models\Region;
use App\Domains\Geography\Models\Department;
use App\Domains\Geography\Models\Commune;

class GeographySeeder extends Seeder
{
    /**
     * Seed regions, departments, communes et localités du Niger.
     */
    public function run(): void
    {
        $this->seedRegions();
        $this->seedDepartments();
        $this->seedCommunes();
    }

    // -------------------------------------------------------------------------
    // RÉGIONS
    // -------------------------------------------------------------------------

    private function seedRegions(): void
    {
        $regions = [
            ['id' => 1, 'code' => 'AZ', 'name' => 'AGADEZ'],
            ['id' => 2, 'code' => 'DA', 'name' => 'DIFFA'],
            ['id' => 3, 'code' => 'DO', 'name' => 'DOSSO'],
            ['id' => 4, 'code' => 'MI', 'name' => 'MARADI'],
            ['id' => 5, 'code' => 'TA', 'name' => 'TAHOUA'],
            ['id' => 6, 'code' => 'TI', 'name' => 'TILLABERI'],
            ['id' => 7, 'code' => 'ZR', 'name' => 'ZINDER'],
            ['id' => 8, 'code' => 'NY', 'name' => 'NIAMEY'],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }
    }

    // -------------------------------------------------------------------------
    // DÉPARTEMENTS
    // -------------------------------------------------------------------------

    private function seedDepartments(): void
    {
        $departments = [
            // Région AGADEZ (1)
            ['id' =>  1, 'region_id' => 1, 'code' => 'AZ-01', 'name' => 'ADERBISSINAT'],
            ['id' =>  2, 'region_id' => 1, 'code' => 'AZ-02', 'name' => 'ARLIT'],
            ['id' =>  3, 'region_id' => 1, 'code' => 'AZ-03', 'name' => 'BILMA'],
            ['id' =>  4, 'region_id' => 1, 'code' => 'AZ-04', 'name' => 'IFEROUANE'],
            ['id' =>  5, 'region_id' => 1, 'code' => 'AZ-05', 'name' => 'INGALL'],
            ['id' =>  6, 'region_id' => 1, 'code' => 'AZ-06', 'name' => 'TCHIROZERINE'],

            // Région DIFFA (2)
            ['id' =>  7, 'region_id' => 2, 'code' => 'DA-01', 'name' => 'BOSSO'],
            ['id' =>  8, 'region_id' => 2, 'code' => 'DA-02', 'name' => 'DIFFA'],
            ['id' =>  9, 'region_id' => 2, 'code' => 'DA-03', 'name' => 'GOUDOUMARIA'],
            ['id' => 10, 'region_id' => 2, 'code' => 'DA-04', 'name' => 'MAINE-SOROA'],
            ['id' => 11, 'region_id' => 2, 'code' => 'DA-05', 'name' => "N'GOURTI"],
            ['id' => 12, 'region_id' => 2, 'code' => 'DA-06', 'name' => "N'GUIGMI"],

            // Région DOSSO (3)
            ['id' => 13, 'region_id' => 3, 'code' => 'DO-01', 'name' => 'BOBOYE'],
            ['id' => 14, 'region_id' => 3, 'code' => 'DO-02', 'name' => 'DIOUNDIOU'],
            ['id' => 15, 'region_id' => 3, 'code' => 'DO-03', 'name' => 'DOGONDOUTCHI'],
            ['id' => 16, 'region_id' => 3, 'code' => 'DO-04', 'name' => 'DOSSO'],
            ['id' => 17, 'region_id' => 3, 'code' => 'DO-05', 'name' => 'FALMEY'],
            ['id' => 18, 'region_id' => 3, 'code' => 'DO-06', 'name' => 'GAYA'],
            ['id' => 19, 'region_id' => 3, 'code' => 'DO-07', 'name' => 'LOGA'],
            ['id' => 20, 'region_id' => 3, 'code' => 'DO-08', 'name' => 'TIBIRI (DOUTCHI)'],

            // Région MARADI (4)
            ['id' => 21, 'region_id' => 4, 'code' => 'MI-01', 'name' => 'AGUIE'],
            ['id' => 22, 'region_id' => 4, 'code' => 'MI-02', 'name' => 'BERMO'],
            ['id' => 23, 'region_id' => 4, 'code' => 'MI-03', 'name' => 'DAKORO'],
            ['id' => 24, 'region_id' => 4, 'code' => 'MI-04', 'name' => 'GAZAOUA'],
            ['id' => 25, 'region_id' => 4, 'code' => 'MI-05', 'name' => 'GUIDAN-ROUMDJI'],
            ['id' => 26, 'region_id' => 4, 'code' => 'MI-06', 'name' => 'MADAROUNFA'],
            ['id' => 27, 'region_id' => 4, 'code' => 'MI-07', 'name' => 'MAYAHI'],
            ['id' => 28, 'region_id' => 4, 'code' => 'MI-08', 'name' => 'TESSAOUA'],
            ['id' => 29, 'region_id' => 4, 'code' => 'MI-09', 'name' => 'VILLE DE MARADI'],

            // Région TAHOUA (5)
            ['id' => 30, 'region_id' => 5, 'code' => 'TA-01', 'name' => 'ABALAK'],
            ['id' => 31, 'region_id' => 5, 'code' => 'TA-02', 'name' => 'BAGAROUA'],
            ['id' => 32, 'region_id' => 5, 'code' => 'TA-03', 'name' => "BIRNI N'KONNI"],
            ['id' => 33, 'region_id' => 5, 'code' => 'TA-04', 'name' => 'BOUZA'],
            ['id' => 34, 'region_id' => 5, 'code' => 'TA-05', 'name' => 'ILLELA'],
            ['id' => 35, 'region_id' => 5, 'code' => 'TA-06', 'name' => 'KEITA'],
            ['id' => 36, 'region_id' => 5, 'code' => 'TA-07', 'name' => 'MADAOUA'],
            ['id' => 37, 'region_id' => 5, 'code' => 'TA-08', 'name' => 'MALBAZA'],
            ['id' => 38, 'region_id' => 5, 'code' => 'TA-09', 'name' => 'TAHOUA'],
            ['id' => 39, 'region_id' => 5, 'code' => 'TA-10', 'name' => 'TASSARA'],
            ['id' => 40, 'region_id' => 5, 'code' => 'TA-11', 'name' => 'TCHINTABARADEN'],
            ['id' => 41, 'region_id' => 5, 'code' => 'TA-12', 'name' => 'TILLIA'],
            ['id' => 42, 'region_id' => 5, 'code' => 'TA-13', 'name' => 'VILLE DE TAHOUA'],

            // Région TILLABERI (6)
            ['id' => 43, 'region_id' => 6, 'code' => 'TI-01', 'name' => 'ABALA'],
            ['id' => 44, 'region_id' => 6, 'code' => 'TI-02', 'name' => 'AYEROU'],
            ['id' => 45, 'region_id' => 6, 'code' => 'TI-03', 'name' => 'BALLEYARA'],
            ['id' => 46, 'region_id' => 6, 'code' => 'TI-04', 'name' => 'BANIBANGOU'],
            ['id' => 47, 'region_id' => 6, 'code' => 'TI-05', 'name' => 'BANKILARE'],
            ['id' => 48, 'region_id' => 6, 'code' => 'TI-06', 'name' => 'FILINGUE'],
            ['id' => 49, 'region_id' => 6, 'code' => 'TI-07', 'name' => 'GOTHEYE'],
            ['id' => 50, 'region_id' => 6, 'code' => 'TI-08', 'name' => 'KOLLO'],
            ['id' => 51, 'region_id' => 6, 'code' => 'TI-09', 'name' => 'OUALLAM'],
            ['id' => 52, 'region_id' => 6, 'code' => 'TI-10', 'name' => 'SAY'],
            ['id' => 53, 'region_id' => 6, 'code' => 'TI-11', 'name' => 'TERA'],
            ['id' => 54, 'region_id' => 6, 'code' => 'TI-12', 'name' => 'TILLABERI'],
            ['id' => 55, 'region_id' => 6, 'code' => 'TI-13', 'name' => 'TORODI'],

            // Région ZINDER (7)
            ['id' => 56, 'region_id' => 7, 'code' => 'ZR-01', 'name' => 'BELBEDJI'],
            ['id' => 57, 'region_id' => 7, 'code' => 'ZR-02', 'name' => 'DAMAGARAM TAKAYA'],
            ['id' => 58, 'region_id' => 7, 'code' => 'ZR-03', 'name' => 'DUNGASS'],
            ['id' => 59, 'region_id' => 7, 'code' => 'ZR-04', 'name' => 'GOURE'],
            ['id' => 60, 'region_id' => 7, 'code' => 'ZR-05', 'name' => 'KANTCHE'],
            ['id' => 61, 'region_id' => 7, 'code' => 'ZR-06', 'name' => 'MAGARIA'],
            ['id' => 62, 'region_id' => 7, 'code' => 'ZR-07', 'name' => 'MIRRIAH'],
            ['id' => 63, 'region_id' => 7, 'code' => 'ZR-08', 'name' => 'TAKEITA'],
            ['id' => 64, 'region_id' => 7, 'code' => 'ZR-09', 'name' => 'TANOUT'],
            ['id' => 65, 'region_id' => 7, 'code' => 'ZR-10', 'name' => 'TESKER'],
            ['id' => 66, 'region_id' => 7, 'code' => 'ZR-11', 'name' => 'VILLE DE ZINDER'],

            // Région NIAMEY (8)
            ['id' => 67, 'region_id' => 8, 'code' => 'NY-01', 'name' => 'VILLE DE NIAMEY'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }

    // -------------------------------------------------------------------------
    // COMMUNES
    // -------------------------------------------------------------------------

    private function seedCommunes(): void
    {
        $communes = [
            // Département ADERBISSINAT (1)
            ['id' =>   1, 'department_id' =>  1, 'code' => 'AZ-01-01', 'name' => 'ADERBISSINAT'],

            // Département ARLIT (2)
            ['id' =>   2, 'department_id' =>  2, 'code' => 'AZ-02-01', 'name' => 'ARLIT'],
            ['id' =>   3, 'department_id' =>  2, 'code' => 'AZ-02-02', 'name' => 'DANNET'],
            ['id' =>   4, 'department_id' =>  2, 'code' => 'AZ-02-03', 'name' => 'GOUGARAM'],

            // Département BILMA (3)
            ['id' =>   5, 'department_id' =>  3, 'code' => 'AZ-03-01', 'name' => 'BILMA'],
            ['id' =>   6, 'department_id' =>  3, 'code' => 'AZ-03-02', 'name' => 'DIRKOU'],
            ['id' =>   7, 'department_id' =>  3, 'code' => 'AZ-03-03', 'name' => 'DJADO'],
            ['id' =>   8, 'department_id' =>  3, 'code' => 'AZ-03-04', 'name' => 'FACHI'],

            // Département IFEROUANE (4)
            ['id' =>   9, 'department_id' =>  4, 'code' => 'AZ-04-01', 'name' => 'IFEROUANE'],
            ['id' =>  10, 'department_id' =>  4, 'code' => 'AZ-04-02', 'name' => 'TIMIA'],

            // Département INGALL (5)
            ['id' =>  11, 'department_id' =>  5, 'code' => 'AZ-05-01', 'name' => 'INGALL'],

            // Département TCHIROZERINE (6)
            ['id' =>  12, 'department_id' =>  6, 'code' => 'AZ-06-01', 'name' => 'AGADEZ'],
            ['id' =>  13, 'department_id' =>  6, 'code' => 'AZ-06-02', 'name' => 'DABAGA'],
            ['id' =>  14, 'department_id' =>  6, 'code' => 'AZ-06-03', 'name' => 'TABELOT'],
            ['id' =>  15, 'department_id' =>  6, 'code' => 'AZ-06-04', 'name' => 'TCHIROZERINE'],

            // Département BOSSO (7)
            ['id' =>  16, 'department_id' =>  7, 'code' => 'DA-01-01', 'name' => 'BOSSO'],
            ['id' =>  17, 'department_id' =>  7, 'code' => 'DA-01-02', 'name' => 'TOUMOUR'],

            // Département DIFFA (8)
            ['id' =>  18, 'department_id' =>  8, 'code' => 'DA-02-01', 'name' => 'CHETIMARI'],
            ['id' =>  19, 'department_id' =>  8, 'code' => 'DA-02-02', 'name' => 'DIFFA'],
            ['id' =>  20, 'department_id' =>  8, 'code' => 'DA-02-03', 'name' => 'GUESKEROU'],

            // Département GOUDOUMARIA (9)
            ['id' =>  21, 'department_id' =>  9, 'code' => 'DA-03-01', 'name' => 'GOUDOUMARIA'],

            // Département MAINE-SOROA (10)
            ['id' =>  22, 'department_id' => 10, 'code' => 'DA-04-01', 'name' => 'FOULATARI'],
            ['id' =>  23, 'department_id' => 10, 'code' => 'DA-04-02', 'name' => 'MAINE SOROA'],
            ['id' =>  24, 'department_id' => 10, 'code' => 'DA-04-03', 'name' => "N'GUELBELY"],

            // Département N'GOURTI (11)
            ['id' =>  25, 'department_id' => 11, 'code' => 'DA-05-01', 'name' => "N'GOURTI"],

            // Département N'GUIGMI (12)
            ['id' =>  26, 'department_id' => 12, 'code' => 'DA-06-01', 'name' => 'KABLEWA'],
            ['id' =>  27, 'department_id' => 12, 'code' => 'DA-06-02', 'name' => "N'GUIGMI"],

            // Département BOBOYE (13)
            ['id' =>  28, 'department_id' => 13, 'code' => 'DO-01-01', 'name' => "BIRNI N'GAOURE"],
            ['id' =>  29, 'department_id' => 13, 'code' => 'DO-01-02', 'name' => 'FABIDJI'],
            ['id' =>  30, 'department_id' => 13, 'code' => 'DO-01-03', 'name' => 'FAKARA'],
            ['id' =>  31, 'department_id' => 13, 'code' => 'DO-01-04', 'name' => 'HARIKANASSOU'],
            ['id' =>  32, 'department_id' => 13, 'code' => 'DO-01-05', 'name' => 'KANKANDI'],
            ['id' =>  33, 'department_id' => 13, 'code' => 'DO-01-06', 'name' => 'KIOTA'],
            ['id' =>  34, 'department_id' => 13, 'code' => 'DO-01-07', 'name' => 'KOYGOLO'],
            ['id' =>  35, 'department_id' => 13, 'code' => 'DO-01-08', 'name' => "N'GONGA"],

            // Département DIOUNDIOU (14)
            ['id' =>  36, 'department_id' => 14, 'code' => 'DO-02-01', 'name' => 'DIOUNDIOU'],
            ['id' =>  37, 'department_id' => 14, 'code' => 'DO-02-02', 'name' => 'KARAKARA'],
            ['id' =>  38, 'department_id' => 14, 'code' => 'DO-02-03', 'name' => 'ZABORI'],

            // Département DOGONDOUTCHI (15)
            ['id' =>  39, 'department_id' => 15, 'code' => 'DO-03-01', 'name' => 'DAN-KASSARI'],
            ['id' =>  40, 'department_id' => 15, 'code' => 'DO-03-02', 'name' => 'DOGONDOUTCHI'],
            ['id' =>  41, 'department_id' => 15, 'code' => 'DO-03-03', 'name' => 'DOGONKIRIA'],
            ['id' =>  42, 'department_id' => 15, 'code' => 'DO-03-04', 'name' => 'KIECHE'],
            ['id' =>  43, 'department_id' => 15, 'code' => 'DO-03-05', 'name' => 'MATANKARI'],
            ['id' =>  44, 'department_id' => 15, 'code' => 'DO-03-06', 'name' => 'SOUCOUCOUTANE'],

            // Département DOSSO (16)
            ['id' =>  45, 'department_id' => 16, 'code' => 'DO-04-01', 'name' => 'DOSSO'],
            ['id' =>  46, 'department_id' => 16, 'code' => 'DO-04-02', 'name' => 'FAREY'],
            ['id' =>  47, 'department_id' => 16, 'code' => 'DO-04-03', 'name' => 'GARANKEDEY'],
            ['id' =>  48, 'department_id' => 16, 'code' => 'DO-04-04', 'name' => 'GOLLE'],
            ['id' =>  49, 'department_id' => 16, 'code' => 'DO-04-05', 'name' => 'GOROUBANKASSAM'],
            ['id' =>  50, 'department_id' => 16, 'code' => 'DO-04-06', 'name' => 'KARGUIBANGOU'],
            ['id' =>  51, 'department_id' => 16, 'code' => 'DO-04-07', 'name' => 'MOKKO'],
            ['id' =>  52, 'department_id' => 16, 'code' => 'DO-04-08', 'name' => 'SAMBERA'],
            ['id' =>  53, 'department_id' => 16, 'code' => 'DO-04-09', 'name' => 'TESSA'],
            ['id' =>  54, 'department_id' => 16, 'code' => 'DO-04-10', 'name' => 'TOMBOKOIREY I'],
            ['id' =>  55, 'department_id' => 16, 'code' => 'DO-04-11', 'name' => 'TOMBOKOIREY II'],

            // Département FALMEY (17)
            ['id' =>  56, 'department_id' => 17, 'code' => 'DO-05-01', 'name' => 'FALMEY'],
            ['id' =>  57, 'department_id' => 17, 'code' => 'DO-05-02', 'name' => 'GUILLADJE'],
            ['id' =>  58, 'department_id' => 17, 'code' => 'DO-05-03', 'name' => 'TANDA'],

            // Département GAYA (18)
            ['id' =>  59, 'department_id' => 18, 'code' => 'DO-06-01', 'name' => 'BENGOU'],
            ['id' =>  60, 'department_id' => 18, 'code' => 'DO-06-02', 'name' => 'BELLA'],
            ['id' =>  61, 'department_id' => 18, 'code' => 'DO-06-03', 'name' => 'GAYA'],
            ['id' =>  62, 'department_id' => 18, 'code' => 'DO-06-04', 'name' => 'GUELELE'],
            ['id' =>  63, 'department_id' => 18, 'code' => 'DO-06-05', 'name' => 'KOGORI'],
            ['id' =>  64, 'department_id' => 18, 'code' => 'DO-06-06', 'name' => 'SANSANE-HAOUSSA'],

            // Département LOGA (19)
            ['id' =>  65, 'department_id' => 19, 'code' => 'DO-07-01', 'name' => 'CHADAKORI'],
            ['id' =>  66, 'department_id' => 19, 'code' => 'DO-07-02', 'name' => 'LOGA'],
            ['id' =>  67, 'department_id' => 19, 'code' => 'DO-07-03', 'name' => 'SOKORBÉ'],

            // Département TIBIRI (DOUTCHI) (20)
            ['id' =>  68, 'department_id' => 20, 'code' => 'DO-08-01', 'name' => 'TIBIRI'],

            // Département AGUIE (21)
            ['id' =>  69, 'department_id' => 21, 'code' => 'MI-01-01', 'name' => 'AGUIE'],
            ['id' =>  70, 'department_id' => 21, 'code' => 'MI-01-02', 'name' => 'TESSAOUA-AGUIE'],

            // Département BERMO (22)
            ['id' =>  71, 'department_id' => 22, 'code' => 'MI-02-01', 'name' => 'BERMO'],

            // Département DAKORO (23)
            ['id' =>  72, 'department_id' => 23, 'code' => 'MI-03-01', 'name' => 'DAKORO'],
            ['id' =>  73, 'department_id' => 23, 'code' => 'MI-03-02', 'name' => 'GADABEDJI'],
            ['id' =>  74, 'department_id' => 23, 'code' => 'MI-03-03', 'name' => 'GANGARA (DAKORO)'],
            ['id' =>  75, 'department_id' => 23, 'code' => 'MI-03-04', 'name' => 'KORNAKA'],
            ['id' =>  76, 'department_id' => 23, 'code' => 'MI-03-05', 'name' => 'ROUMBOU'],
            ['id' =>  77, 'department_id' => 23, 'code' => 'MI-03-06', 'name' => 'SABON MACHI'],

            // Département GAZAOUA (24)
            ['id' =>  78, 'department_id' => 24, 'code' => 'MI-04-01', 'name' => 'GAZAOUA'],

            // Département GUIDAN-ROUMDJI (25)
            ['id' =>  79, 'department_id' => 25, 'code' => 'MI-05-01', 'name' => 'CHADAKORI (MARADI)'],
            ['id' =>  80, 'department_id' => 25, 'code' => 'MI-05-02', 'name' => 'GUIDAN-ROUMDJI'],
            ['id' =>  81, 'department_id' => 25, 'code' => 'MI-05-03', 'name' => 'GUIDAN-SORI'],
            ['id' =>  82, 'department_id' => 25, 'code' => 'MI-05-04', 'name' => 'SAFO'],

            // Département MADAROUNFA (26)
            ['id' =>  83, 'department_id' => 26, 'code' => 'MI-06-01', 'name' => 'DAN-ISSA'],
            ['id' =>  84, 'department_id' => 26, 'code' => 'MI-06-02', 'name' => 'GABI'],
            ['id' =>  85, 'department_id' => 26, 'code' => 'MI-06-03', 'name' => 'MADAROUNFA'],
            ['id' =>  86, 'department_id' => 26, 'code' => 'MI-06-04', 'name' => 'TIBIRI (MARADI)'],

            // Département MAYAHI (27)
            ['id' =>  87, 'department_id' => 27, 'code' => 'MI-07-01', 'name' => 'ATTANTANE'],
            ['id' =>  88, 'department_id' => 27, 'code' => 'MI-07-02', 'name' => 'GUIDAN AMOUMOUNE'],
            ['id' =>  89, 'department_id' => 27, 'code' => 'MI-07-03', 'name' => 'MAYAHI'],
            ['id' =>  90, 'department_id' => 27, 'code' => 'MI-07-04', 'name' => 'SARKIN YAMMA'],

            // Département TESSAOUA (28)
            ['id' =>  91, 'department_id' => 28, 'code' => 'MI-08-01', 'name' => 'BAOUDETA'],
            ['id' =>  92, 'department_id' => 28, 'code' => 'MI-08-02', 'name' => 'DJIRATAOUA'],
            ['id' =>  93, 'department_id' => 28, 'code' => 'MI-08-03', 'name' => 'KORGOM'],
            ['id' =>  94, 'department_id' => 28, 'code' => 'MI-08-04', 'name' => 'KOURFA'],
            ['id' =>  95, 'department_id' => 28, 'code' => 'MI-08-05', 'name' => 'TESSAOUA'],

            // Département VILLE DE MARADI (29)
            ['id' =>  96, 'department_id' => 29, 'code' => 'MI-09-01', 'name' => 'MARADI COMMUNE 1'],
            ['id' =>  97, 'department_id' => 29, 'code' => 'MI-09-02', 'name' => 'MARADI COMMUNE 2'],
            ['id' =>  98, 'department_id' => 29, 'code' => 'MI-09-03', 'name' => 'MARADI COMMUNE 3'],

            // Département ABALAK (30)
            ['id' =>  99, 'department_id' => 30, 'code' => 'TA-01-01', 'name' => 'ABALAK'],
            ['id' => 100, 'department_id' => 30, 'code' => 'TA-01-02', 'name' => 'TAMAYA'],

            // Département BAGAROUA (31)
            ['id' => 101, 'department_id' => 31, 'code' => 'TA-02-01', 'name' => 'BAGAROUA'],

            // Département BIRNI N'KONNI (32)
            ['id' => 102, 'department_id' => 32, 'code' => 'TA-03-01', 'name' => "BIRNI N'KONNI"],
            ['id' => 103, 'department_id' => 32, 'code' => 'TA-03-02', 'name' => 'ALLELA'],
            ['id' => 104, 'department_id' => 32, 'code' => 'TA-03-03', 'name' => 'KALFOU'],
            ['id' => 105, 'department_id' => 32, 'code' => 'TA-03-04', 'name' => 'TASSYA'],

            // Département BOUZA (33)
            ['id' => 106, 'department_id' => 33, 'code' => 'TA-04-01', 'name' => 'BOUZA'],
            ['id' => 107, 'department_id' => 33, 'code' => 'TA-04-02', 'name' => 'DOGUÉRAOUA'],
            ['id' => 108, 'department_id' => 33, 'code' => 'TA-04-03', 'name' => 'GALMI'],
            ['id' => 109, 'department_id' => 33, 'code' => 'TA-04-04', 'name' => 'TASARA'],

            // Département ILLELA (34)
            ['id' => 110, 'department_id' => 34, 'code' => 'TA-05-01', 'name' => 'BADAGUICHIRI'],
            ['id' => 111, 'department_id' => 34, 'code' => 'TA-05-02', 'name' => 'ILLELA'],
            ['id' => 112, 'department_id' => 34, 'code' => 'TA-05-03', 'name' => 'TAJAÉ'],

            // Département KEITA (35)
            ['id' => 113, 'department_id' => 35, 'code' => 'TA-06-01', 'name' => 'KEITA'],
            ['id' => 114, 'department_id' => 35, 'code' => 'TA-06-02', 'name' => 'TABALAK'],

            // Département MADAOUA (36)
            ['id' => 115, 'department_id' => 36, 'code' => 'TA-07-01', 'name' => 'MADAOUA'],
            ['id' => 116, 'department_id' => 36, 'code' => 'TA-07-02', 'name' => 'OURNO'],
            ['id' => 117, 'department_id' => 36, 'code' => 'TA-07-03', 'name' => 'TSERNAOUA'],

            // Département MALBAZA (37)
            ['id' => 118, 'department_id' => 37, 'code' => 'TA-08-01', 'name' => 'MALBAZA'],

            // Département TAHOUA (38)
            ['id' => 119, 'department_id' => 38, 'code' => 'TA-09-01', 'name' => "AFALA N'TARKA"],
            ['id' => 120, 'department_id' => 38, 'code' => 'TA-09-02', 'name' => 'KALFOU'],
            ['id' => 121, 'department_id' => 38, 'code' => 'TA-09-03', 'name' => 'TAHOUA'],

            // Département TASSARA (39)
            ['id' => 122, 'department_id' => 39, 'code' => 'TA-10-01', 'name' => 'TASSARA'],

            // Département TCHINTABARADEN (40)
            ['id' => 123, 'department_id' => 40, 'code' => 'TA-11-01', 'name' => 'AKOUBOUNOU'],
            ['id' => 124, 'department_id' => 40, 'code' => 'TA-11-02', 'name' => 'TCHINTABARADEN'],

            // Département TILLIA (41)
            ['id' => 125, 'department_id' => 41, 'code' => 'TA-12-01', 'name' => 'TILLIA'],

            // Département VILLE DE TAHOUA (42)
            ['id' => 126, 'department_id' => 42, 'code' => 'TA-13-01', 'name' => 'TAHOUA COMMUNE 1'],
            ['id' => 127, 'department_id' => 42, 'code' => 'TA-13-02', 'name' => 'TAHOUA COMMUNE 2'],

            // Département ABALA (43)
            ['id' => 128, 'department_id' => 43, 'code' => 'TI-01-01', 'name' => 'ABALA'],
            ['id' => 129, 'department_id' => 43, 'code' => 'TI-01-02', 'name' => 'SANAM'],

            // Département AYEROU (44)
            ['id' => 130, 'department_id' => 44, 'code' => 'TI-02-01', 'name' => 'AYEROU'],
            ['id' => 131, 'department_id' => 44, 'code' => 'TI-02-02', 'name' => 'MANGAIZE'],

            // Département BALLEYARA (45)
            ['id' => 132, 'department_id' => 45, 'code' => 'TI-03-01', 'name' => 'BALLEYARA'],

            // Département BANIBANGOU (46)
            ['id' => 133, 'department_id' => 46, 'code' => 'TI-04-01', 'name' => 'BANIBANGOU'],

            // Département BANKILARE (47)
            ['id' => 134, 'department_id' => 47, 'code' => 'TI-05-01', 'name' => 'BANKILARE'],

            // Département FILINGUE (48)
            ['id' => 135, 'department_id' => 48, 'code' => 'TI-06-01', 'name' => 'FILINGUE'],
            ['id' => 136, 'department_id' => 48, 'code' => 'TI-06-02', 'name' => 'KOURFEYE CENTRE'],
            ['id' => 137, 'department_id' => 48, 'code' => 'TI-06-03', 'name' => 'TAGAZAR'],
            ['id' => 138, 'department_id' => 48, 'code' => 'TI-06-04', 'name' => 'TONDIKANDIA'],

            // Département GOTHEYE (49)
            ['id' => 139, 'department_id' => 49, 'code' => 'TI-07-01', 'name' => 'GOTHEYE'],
            ['id' => 140, 'department_id' => 49, 'code' => 'TI-07-02', 'name' => 'LOGA-GOTHEYE'],
            ['id' => 141, 'department_id' => 49, 'code' => 'TI-07-03', 'name' => 'TONDIKIWINDI'],

            // Département KOLLO (50)
            ['id' => 142, 'department_id' => 50, 'code' => 'TI-08-01', 'name' => 'KARMA'],
            ['id' => 143, 'department_id' => 50, 'code' => 'TI-08-02', 'name' => 'KOLLO'],
            ['id' => 144, 'department_id' => 50, 'code' => 'TI-08-03', 'name' => 'LIBORÉ'],
            ['id' => 145, 'department_id' => 50, 'code' => 'TI-08-04', 'name' => 'HAMDALLAYE'],
            ['id' => 146, 'department_id' => 50, 'code' => 'TI-08-05', 'name' => 'TIAGUIRIRE'],

            // Département OUALLAM (51)
            ['id' => 147, 'department_id' => 51, 'code' => 'TI-09-01', 'name' => 'OUALLAM'],
            ['id' => 148, 'department_id' => 51, 'code' => 'TI-09-02', 'name' => 'TONDIKOARE'],
            ['id' => 149, 'department_id' => 51, 'code' => 'TI-09-03', 'name' => 'DAMANA'],

            // Département SAY (52)
            ['id' => 150, 'department_id' => 52, 'code' => 'TI-10-01', 'name' => 'SAY'],
            ['id' => 151, 'department_id' => 52, 'code' => 'TI-10-02', 'name' => 'TAMOU'],
            ['id' => 152, 'department_id' => 52, 'code' => 'TI-10-03', 'name' => 'TORODI-SAY'],

            // Département TERA (53)
            ['id' => 153, 'department_id' => 53, 'code' => 'TI-11-01', 'name' => 'TERA'],
            ['id' => 154, 'department_id' => 53, 'code' => 'TI-11-02', 'name' => 'DIAGOUROU'],
            ['id' => 155, 'department_id' => 53, 'code' => 'TI-11-03', 'name' => 'DARGOL'],
            ['id' => 156, 'department_id' => 53, 'code' => 'TI-11-04', 'name' => 'GOROUOL'],

            // Département TILLABERI (54)
            ['id' => 157, 'department_id' => 54, 'code' => 'TI-12-01', 'name' => 'TILLABERI'],
            ['id' => 158, 'department_id' => 54, 'code' => 'TI-12-02', 'name' => 'DESSA'],

            // Département TORODI (55)
            ['id' => 159, 'department_id' => 55, 'code' => 'TI-13-01', 'name' => 'TORODI'],

            // Département BELBEDJI (56)
            ['id' => 160, 'department_id' => 56, 'code' => 'ZR-01-01', 'name' => 'BELBEDJI'],

            // Département DAMAGARAM TAKAYA (57)
            ['id' => 161, 'department_id' => 57, 'code' => 'ZR-02-01', 'name' => 'DAMAGARAM TAKAYA'],
            ['id' => 162, 'department_id' => 57, 'code' => 'ZR-02-02', 'name' => 'MAZAMNI'],
            ['id' => 163, 'department_id' => 57, 'code' => 'ZR-02-03', 'name' => 'WAME'],

            // Département DUNGASS (58)
            ['id' => 164, 'department_id' => 58, 'code' => 'ZR-03-01', 'name' => 'DOGO-DOGO'],
            ['id' => 165, 'department_id' => 58, 'code' => 'ZR-03-02', 'name' => 'DUNGASS'],
            ['id' => 166, 'department_id' => 58, 'code' => 'ZR-03-03', 'name' => 'GOUCHI'],
            ['id' => 167, 'department_id' => 58, 'code' => 'ZR-03-04', 'name' => 'MALAWA'],

            // Département GOURE (59)
            ['id' => 168, 'department_id' => 59, 'code' => 'ZR-04-01', 'name' => 'ALAKOSS'],
            ['id' => 169, 'department_id' => 59, 'code' => 'ZR-04-02', 'name' => 'BOUNE'],
            ['id' => 170, 'department_id' => 59, 'code' => 'ZR-04-03', 'name' => 'GAMOU'],
            ['id' => 171, 'department_id' => 59, 'code' => 'ZR-04-04', 'name' => 'GOURE'],
            ['id' => 172, 'department_id' => 59, 'code' => 'ZR-04-05', 'name' => 'GUIDIGUIR'],
            ['id' => 173, 'department_id' => 59, 'code' => 'ZR-04-06', 'name' => 'KELLE'],

            // Département KANTCHE (60)
            ['id' => 174, 'department_id' => 60, 'code' => 'ZR-05-01', 'name' => 'DAN BARTO'],
            ['id' => 175, 'department_id' => 60, 'code' => 'ZR-05-02', 'name' => 'DAOUCHE'],
            ['id' => 176, 'department_id' => 60, 'code' => 'ZR-05-03', 'name' => 'DOUNGOU'],
            ['id' => 177, 'department_id' => 60, 'code' => 'ZR-05-04', 'name' => 'ICHIRNAWA'],
            ['id' => 178, 'department_id' => 60, 'code' => 'ZR-05-05', 'name' => 'KANTCHE'],
            ['id' => 179, 'department_id' => 60, 'code' => 'ZR-05-06', 'name' => 'KOURNI'],
            ['id' => 180, 'department_id' => 60, 'code' => 'ZR-05-07', 'name' => 'MATAMEY'],
            ['id' => 181, 'department_id' => 60, 'code' => 'ZR-05-08', 'name' => 'TSAOUNI'],
            ['id' => 182, 'department_id' => 60, 'code' => 'ZR-05-09', 'name' => 'YAOURI'],

            // Département MAGARIA (61)
            ['id' => 183, 'department_id' => 61, 'code' => 'ZR-06-01', 'name' => 'BANDE'],
            ['id' => 184, 'department_id' => 61, 'code' => 'ZR-06-02', 'name' => 'DANTCHIAO'],
            ['id' => 185, 'department_id' => 61, 'code' => 'ZR-06-03', 'name' => 'KWAYA'],
            ['id' => 186, 'department_id' => 61, 'code' => 'ZR-06-04', 'name' => 'MAGARIA'],
            ['id' => 187, 'department_id' => 61, 'code' => 'ZR-06-05', 'name' => 'SASSOUMBROUM'],
            ['id' => 188, 'department_id' => 61, 'code' => 'ZR-06-06', 'name' => 'WACHA'],
            ['id' => 189, 'department_id' => 61, 'code' => 'ZR-06-07', 'name' => 'YEKOUA'],

            // Département MIRRIAH (62)
            ['id' => 190, 'department_id' => 62, 'code' => 'ZR-07-01', 'name' => 'DOGO'],
            ['id' => 191, 'department_id' => 62, 'code' => 'ZR-07-02', 'name' => 'DROUM'],
            ['id' => 192, 'department_id' => 62, 'code' => 'ZR-07-03', 'name' => 'GAFFATI'],
            ['id' => 193, 'department_id' => 62, 'code' => 'ZR-07-04', 'name' => 'GOUNA'],
            ['id' => 194, 'department_id' => 62, 'code' => 'ZR-07-05', 'name' => 'HAMDARA'],
            ['id' => 195, 'department_id' => 62, 'code' => 'ZR-07-06', 'name' => 'KOLLERAM'],
            ['id' => 196, 'department_id' => 62, 'code' => 'ZR-07-07', 'name' => 'MIRRIAH'],
            ['id' => 197, 'department_id' => 62, 'code' => 'ZR-07-08', 'name' => 'ZERMOU'],

            // Département TAKEITA (63)
            ['id' => 198, 'department_id' => 63, 'code' => 'ZR-08-01', 'name' => 'DAKOUSSA'],
            ['id' => 199, 'department_id' => 63, 'code' => 'ZR-08-02', 'name' => 'GARAGOUMSA'],
            ['id' => 200, 'department_id' => 63, 'code' => 'ZR-08-03', 'name' => 'TIRMINI'],

            // Département TANOUT (64)
            ['id' => 201, 'department_id' => 64, 'code' => 'ZR-09-01', 'name' => 'FALENKO'],
            ['id' => 202, 'department_id' => 64, 'code' => 'ZR-09-02', 'name' => 'GANGARA (TANOUT)'],
            ['id' => 203, 'department_id' => 64, 'code' => 'ZR-09-03', 'name' => 'OLLELEWA'],
            ['id' => 204, 'department_id' => 64, 'code' => 'ZR-09-04', 'name' => 'TANOUT'],
            ['id' => 205, 'department_id' => 64, 'code' => 'ZR-09-05', 'name' => 'TENHYA'],

            // Département TESKER (65)
            ['id' => 206, 'department_id' => 65, 'code' => 'ZR-10-01', 'name' => 'TESKER'],

            // Département VILLE DE ZINDER (66)
            ['id' => 207, 'department_id' => 66, 'code' => 'ZR-11-01', 'name' => 'ZINDER ARRONDISSEMENT 1'],
            ['id' => 208, 'department_id' => 66, 'code' => 'ZR-11-02', 'name' => 'ZINDER ARRONDISSEMENT 2'],
            ['id' => 209, 'department_id' => 66, 'code' => 'ZR-11-03', 'name' => 'ZINDER ARRONDISSEMENT 3'],
            ['id' => 210, 'department_id' => 66, 'code' => 'ZR-11-04', 'name' => 'ZINDER ARRONDISSEMENT 4'],
            ['id' => 211, 'department_id' => 66, 'code' => 'ZR-11-05', 'name' => 'ZINDER ARRONDISSEMENT 5'],

            // Département VILLE DE NIAMEY (67)
            ['id' => 212, 'department_id' => 67, 'code' => 'NY-01-01', 'name' => 'NIAMEY ARRONDISSEMENT 1'],
            ['id' => 213, 'department_id' => 67, 'code' => 'NY-01-02', 'name' => 'NIAMEY ARRONDISSEMENT 2'],
            ['id' => 214, 'department_id' => 67, 'code' => 'NY-01-03', 'name' => 'NIAMEY ARRONDISSEMENT 3'],
            ['id' => 215, 'department_id' => 67, 'code' => 'NY-01-04', 'name' => 'NIAMEY ARRONDISSEMENT 4'],
            ['id' => 216, 'department_id' => 67, 'code' => 'NY-01-05', 'name' => 'NIAMEY ARRONDISSEMENT 5'],
        ];

        foreach ($communes as $commune) {
            Commune::create($commune);
        }
    }

    // -------------------------------------------------------------------------
    // LOCALITÉS  - chargées depuis le fichier LocalitySeeder.php
    // -------------------------------------------------------------------------

    
}

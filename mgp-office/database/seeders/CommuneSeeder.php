<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Domains\Geography\Models\Commune;
use App\Domains\Geography\Models\Department;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $communes = [
            ['id' => 217, 'name' => 'MALAWA', 'department_id' => 58],
            ['id' => 218, 'name' => 'ALAKOSS', 'department_id' => 59],
            ['id' => 219, 'name' => 'BOUNE', 'department_id' => 59],
            ['id' => 220, 'name' => 'GAMOU', 'department_id' => 59],
            ['id' => 221, 'name' => 'GOURE', 'department_id' => 59],
            ['id' => 222, 'name' => 'GUidIGUIR', 'department_id' => 59],
            ['id' => 223, 'name' => 'KELLE', 'department_id' => 59],
            ['id' => 224, 'name' => 'DAN BARTO', 'department_id' => 60],
            ['id' => 225, 'name' => 'DAOUCHE', 'department_id' => 60],
            ['id' => 226, 'name' => 'DOUNGOU', 'department_id' => 60],
            ['id' => 227, 'name' => 'ICHIRNAWA', 'department_id' => 60],
            ['id' => 228, 'name' => 'KANTCHE', 'department_id' => 60],
            ['id' => 229, 'name' => 'KOURNI', 'department_id' => 60],
            ['id' => 230, 'name' => 'MATAMEY', 'department_id' => 60],
            ['id' => 231, 'name' => 'TSAOUNI', 'department_id' => 60],
            ['id' => 232, 'name' => 'YAOURI', 'department_id' => 60],
            ['id' => 233, 'name' => 'BANDE', 'department_id' => 61],
            ['id' => 234, 'name' => 'DANTCHIAO', 'department_id' => 61],
            ['id' => 235, 'name' => 'KWAYA', 'department_id' => 61],
            ['id' => 236, 'name' => 'MAGARIA', 'department_id' => 61],
            ['id' => 237, 'name' => 'SASSOUMBROUM', 'department_id' => 61],
            ['id' => 238, 'name' => 'WACHA', 'department_id' => 61],
            ['id' => 239, 'name' => 'YEKOUA', 'department_id' => 61],
            ['id' => 240, 'name' => 'DOGO', 'department_id' => 62],
            ['id' => 241, 'name' => 'DROUM', 'department_id' => 62],
            ['id' => 242, 'name' => 'GAFFATI', 'department_id' => 62],
            ['id' => 243, 'name' => 'GOUNA', 'department_id' => 62],
            ['id' => 244, 'name' => 'HAMDARA', 'department_id' => 62],
            ['id' => 245, 'name' => 'KOLLERAM', 'department_id' => 62],
            ['id' => 246, 'name' => 'MIRRIAH', 'department_id' => 62],
            ['id' => 247, 'name' => 'ZERMOU', 'department_id' => 62],
            ['id' => 248, 'name' => 'DAKOUSSA', 'department_id' => 63],
            ['id' => 249, 'name' => 'GARAGOUMSA', 'department_id' => 63],
            ['id' => 250, 'name' => 'TIRMINI', 'department_id' => 63],
            ['id' => 251, 'name' => 'FALENKO', 'department_id' => 64],
            ['id' => 252, 'name' => 'GANGARA (TANOUT)', 'department_id' => 64],
            ['id' => 253, 'name' => 'OLLELEWA', 'department_id' => 64],
            ['id' => 254, 'name' => 'TANOUT', 'department_id' => 64],
            ['id' => 255, 'name' => 'TENHYA', 'department_id' => 64],
            ['id' => 256, 'name' => 'TESKER', 'department_id' => 65],
            ['id' => 257, 'name' => 'ZINDER ARRONDISSEMENT 1', 'department_id' => 66],
            ['id' => 258, 'name' => 'ZINDER ARRONDISSEMENT 2', 'department_id' => 66],
            ['id' => 259, 'name' => 'ZINDER ARRONDISSEMENT 3', 'department_id' => 66],
            ['id' => 260, 'name' => 'ZINDER ARRONDISSEMENT 4', 'department_id' => 66],
            ['id' => 261, 'name' => 'ZINDER ARRONDISSEMENT 5', 'department_id' => 66],
            ['id' => 262, 'name' => 'NIAMEY ARRONDISSEMENT 1', 'department_id' => 67],
            ['id' => 263, 'name' => 'NIAMEY ARRONDISSEMENT 2', 'department_id' => 67],
            ['id' => 264, 'name' => 'NIAMEY ARRONDISSEMENT 3', 'department_id' => 67],
            ['id' => 265, 'name' => 'NIAMEY ARRONDISSEMENT 4', 'department_id' => 67],
            ['id' => 266, 'name' => 'NIAMEY ARRONDISSEMENT 5', 'department_id' => 67],
        ];

        foreach ($communes as $communeData) {
            $department = Department::find($communeData['department_id']);
            if ($department) {
                $communeData['code'] = $department->code . '-' . $communeData['id'];
                Commune::create($communeData);
            }
        }
    }
}

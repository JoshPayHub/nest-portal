<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SssContribution;

class SSSContributionSeeder extends Seeder
{
    public function run(): void
    {
        $sss = [
            ['min_salary' => 0,        'max_salary' => 5249.99,  'msc' => 5000,  'ee_share' => 250,  'er_share' => 500,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 5250,     'max_salary' => 5749.99,  'msc' => 5500,  'ee_share' => 275,  'er_share' => 550,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 5750,     'max_salary' => 6249.99,  'msc' => 6000,  'ee_share' => 300,  'er_share' => 600,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 6250,     'max_salary' => 6749.99,  'msc' => 6500,  'ee_share' => 325,  'er_share' => 650,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 6750,     'max_salary' => 7249.99,  'msc' => 7000,  'ee_share' => 350,  'er_share' => 700,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 7250,     'max_salary' => 7749.99,  'msc' => 7500,  'ee_share' => 375,  'er_share' => 750,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 7750,     'max_salary' => 8249.99,  'msc' => 8000,  'ee_share' => 400,  'er_share' => 800,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 8250,     'max_salary' => 8749.99,  'msc' => 8500,  'ee_share' => 425,  'er_share' => 850,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 8750,     'max_salary' => 9249.99,  'msc' => 9000,  'ee_share' => 450,  'er_share' => 900,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 9250,     'max_salary' => 9749.99,  'msc' => 9500,  'ee_share' => 475,  'er_share' => 950,  'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 9750,     'max_salary' => 10249.99, 'msc' => 10000, 'ee_share' => 500,  'er_share' => 1000, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 10250,    'max_salary' => 10749.99, 'msc' => 10500, 'ee_share' => 525,  'er_share' => 1050, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 10750,    'max_salary' => 11249.99, 'msc' => 11000, 'ee_share' => 550,  'er_share' => 1100, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 11250,    'max_salary' => 11749.99, 'msc' => 11500, 'ee_share' => 575,  'er_share' => 1150, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 11750,    'max_salary' => 12249.99, 'msc' => 12000, 'ee_share' => 600,  'er_share' => 1200, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 12250,    'max_salary' => 12749.99, 'msc' => 12500, 'ee_share' => 625,  'er_share' => 1250, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 12750,    'max_salary' => 13249.99, 'msc' => 13000, 'ee_share' => 650,  'er_share' => 1300, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 13250,    'max_salary' => 13749.99, 'msc' => 13500, 'ee_share' => 675,  'er_share' => 1350, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 13750,    'max_salary' => 14249.99, 'msc' => 14000, 'ee_share' => 700,  'er_share' => 1400, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],
            ['min_salary' => 14250,    'max_salary' => 14749.99, 'msc' => 14500, 'ee_share' => 725,  'er_share' => 1450, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 10],

            // EC Increases to 30 at 14,750 range
            ['min_salary' => 14750,    'max_salary' => 15249.99, 'msc' => 15000, 'ee_share' => 750,  'er_share' => 1500, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 15250,    'max_salary' => 15749.99, 'msc' => 15500, 'ee_share' => 775,  'er_share' => 1550, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 15750,    'max_salary' => 16249.99, 'msc' => 16000, 'ee_share' => 800,  'er_share' => 1600, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 16250,    'max_salary' => 16749.99, 'msc' => 16500, 'ee_share' => 825,  'er_share' => 1650, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 16750,    'max_salary' => 17249.99, 'msc' => 17000, 'ee_share' => 850,  'er_share' => 1700, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 17250,    'max_salary' => 17749.99, 'msc' => 17500, 'ee_share' => 875,  'er_share' => 1750, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 17750,    'max_salary' => 18249.99, 'msc' => 18000, 'ee_share' => 900,  'er_share' => 1800, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 18250,    'max_salary' => 18749.99, 'msc' => 18500, 'ee_share' => 925,  'er_share' => 1850, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 18750,    'max_salary' => 19249.99, 'msc' => 19000, 'ee_share' => 950,  'er_share' => 1900, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 19250,    'max_salary' => 19749.99, 'msc' => 19500, 'ee_share' => 975,  'er_share' => 1950, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],
            ['min_salary' => 19750,    'max_salary' => 20249.99, 'msc' => 20000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 0,    'wisp_er' => 0,    'ec_er' => 30],

            // MPF (WISP) starts here. Regular SS EE/ER stays capped at 1000/2000.
            ['min_salary' => 20250,    'max_salary' => 20749.99, 'msc' => 20500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 25,   'wisp_er' => 50,   'ec_er' => 30],
            ['min_salary' => 20750,    'max_salary' => 21249.99, 'msc' => 21000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 50,   'wisp_er' => 100,  'ec_er' => 30],
            ['min_salary' => 21250,    'max_salary' => 21749.99, 'msc' => 21500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 75,   'wisp_er' => 150,  'ec_er' => 30],
            ['min_salary' => 21750,    'max_salary' => 22249.99, 'msc' => 22000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 100,  'wisp_er' => 200,  'ec_er' => 30],
            ['min_salary' => 22250,    'max_salary' => 22749.99, 'msc' => 22500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 125,  'wisp_er' => 250,  'ec_er' => 30],
            ['min_salary' => 22750,    'max_salary' => 23249.99, 'msc' => 23000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 150,  'wisp_er' => 300,  'ec_er' => 30],
            ['min_salary' => 23250,    'max_salary' => 23749.99, 'msc' => 23500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 175,  'wisp_er' => 350,  'ec_er' => 30],
            ['min_salary' => 23750,    'max_salary' => 24249.99, 'msc' => 24000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 200,  'wisp_er' => 400,  'ec_er' => 30],
            ['min_salary' => 24250,    'max_salary' => 24749.99, 'msc' => 24500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 225,  'wisp_er' => 450,  'ec_er' => 30],
            ['min_salary' => 24750,    'max_salary' => 25249.99, 'msc' => 25000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 250,  'wisp_er' => 500,  'ec_er' => 30],
            ['min_salary' => 25250,    'max_salary' => 25749.99, 'msc' => 25500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 275,  'wisp_er' => 550,  'ec_er' => 30],
            ['min_salary' => 25750,    'max_salary' => 26249.99, 'msc' => 26000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 300,  'wisp_er' => 600,  'ec_er' => 30],
            ['min_salary' => 26250,    'max_salary' => 26749.99, 'msc' => 26500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 325,  'wisp_er' => 650,  'ec_er' => 30],
            ['min_salary' => 26750,    'max_salary' => 27249.99, 'msc' => 27000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 350,  'wisp_er' => 700,  'ec_er' => 30],
            ['min_salary' => 27250,    'max_salary' => 27749.99, 'msc' => 27500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 375,  'wisp_er' => 750,  'ec_er' => 30],
            ['min_salary' => 27750,    'max_salary' => 28249.99, 'msc' => 28000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 400,  'wisp_er' => 800,  'ec_er' => 30],
            ['min_salary' => 28250,    'max_salary' => 28749.99, 'msc' => 28500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 425,  'wisp_er' => 850,  'ec_er' => 30],
            ['min_salary' => 28750,    'max_salary' => 29249.99, 'msc' => 29000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 450,  'wisp_er' => 900,  'ec_er' => 30],
            ['min_salary' => 29250,    'max_salary' => 29749.99, 'msc' => 29500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 475,  'wisp_er' => 950,  'ec_er' => 30],
            ['min_salary' => 29750,    'max_salary' => 30249.99, 'msc' => 30000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 500,  'wisp_er' => 1000, 'ec_er' => 30],
            ['min_salary' => 30250,    'max_salary' => 30749.99, 'msc' => 30500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 525,  'wisp_er' => 1050, 'ec_er' => 30],
            ['min_salary' => 30750,    'max_salary' => 31249.99, 'msc' => 31000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 550,  'wisp_er' => 1100, 'ec_er' => 30],
            ['min_salary' => 31250,    'max_salary' => 31749.99, 'msc' => 31500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 575,  'wisp_er' => 1150, 'ec_er' => 30],
            ['min_salary' => 31750,    'max_salary' => 32249.99, 'msc' => 32000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 600,  'wisp_er' => 1200, 'ec_er' => 30],
            ['min_salary' => 32250,    'max_salary' => 32749.99, 'msc' => 32500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 625,  'wisp_er' => 1250, 'ec_er' => 30],
            ['min_salary' => 32750,    'max_salary' => 33249.99, 'msc' => 33000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 650,  'wisp_er' => 1300, 'ec_er' => 30],
            ['min_salary' => 33250,    'max_salary' => 33749.99, 'msc' => 33500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 675,  'wisp_er' => 1350, 'ec_er' => 30],
            ['min_salary' => 33750,    'max_salary' => 34249.99, 'msc' => 34000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 700,  'wisp_er' => 1400, 'ec_er' => 30],
            ['min_salary' => 34250,    'max_salary' => 34749.99, 'msc' => 34500, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 725,  'wisp_er' => 1450, 'ec_er' => 30],
            ['min_salary' => 34750,    'max_salary' => 999999.99,'msc' => 35000, 'ee_share' => 1000, 'er_share' => 2000, 'wisp_ee' => 750,  'wisp_er' => 1500, 'ec_er' => 30],
        ];

        foreach ($sss as $row) {
            SssContribution::updateOrCreate(
                ['min_salary' => $row['min_salary']], // Better unique key for ranges
                $row
            );
        }
    }
}

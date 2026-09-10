<?php

namespace Database\Seeders;

use App\Enums\Community;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoReportSeeder extends Seeder
{
    private const TITLES = [
        'Pelatihan Leadership Remaja',
        'Aksi Bersih Pantai',
        'Penyuluhan Kesehatan Reproduksi',
        'Workshop Kewirausahaan Pemuda',
        'Festival Seni Budaya Daerah',
        'Bakti Sosial Panti Asuhan',
        'Seminar Anti Narkoba',
        'Lomba Cerdas Cermat Anak',
        'Kampanye Lingkungan Sekolah',
        'Pelatihan Public Speaking',
        'Donor Darah Bersama',
        'Peringatan Hari Anak Nasional',
    ];

    public function run(): void
    {
        if (Report::query()->exists()) {
            return;
        }

        $plans = [Community::FAD->value => 24, Community::GENRE->value => 8];

        foreach (Community::cases() as $community) {
            $categories = Category::for($community)->pluck('id')->all();
            $users = User::query()
                ->where('community', $community->value)
                ->pluck('id')
                ->all();

            if ($categories === [] || $users === []) {
                continue;
            }

            $titles = array_slice(self::TITLES, 0, $plans[$community->value]);
            $month = 6;
            $year = 2025;

            foreach ($titles as $i => $title) {
                $start = sprintf('%d-%02d-%02d', $year, $month, 5 + ($i % 20));

                Report::create([
                    'title' => $title.' #'.($i + 1),
                    'description' => $title.' dilaksanakan oleh komunitas '.$community->config()['short'].
                        ' Kabupaten Tanah Laut sebagai bagian dari program kerja tahunan.',
                    'start_date' => $start,
                    'end_date' => null,
                    'location' => 'Tanah Laut',
                    'community' => $community,
                    'user_id' => $users[$i % count($users)],
                    'category_id' => $categories[$i % count($categories)],
                ]);

                $month++;
                if ($month > 12) {
                    $month = 1;
                    $year = 2026;
                }
            }
        }
    }
}

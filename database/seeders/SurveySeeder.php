<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user as creator
        $user = User::first();

        if (!$user) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Check if surveys already exist
        if (Survey::count() > 0) {
            $this->command->warn('Surveys already exist. Skipping seeder.');
            return;
        }

        $year = Carbon::now()->year;

        // Survey 1: Kepuasan Layanan Akademik
        $survey1 = Survey::create([
            'survey_code' => "SURV-{$year}-001",
            'title' => 'Kepuasan Layanan Akademik Semester Genap 2024/2025',
            'description' => 'Kuesioner ini bertujuan untuk mengukur tingkat kepuasan mahasiswa terhadap layanan akademik yang diberikan oleh institusi selama semester genap tahun akademik 2024/2025.',
            'created_by' => $user->id,
            'type' => 'internal',
            'status' => 'published',
            'is_anonymous' => false,
            'allow_multiple_responses' => false,
            'start_date' => Carbon::now()->subDays(7),
            'end_date' => Carbon::now()->addDays(30),
        ]);

        // Questions for Survey 1
        $questions1 = [
            [
                'question_text' => 'Bagaimana penilaian Anda terhadap kualitas pengajaran dosen?',
                'question_type' => 'rating',
                'is_required' => true,
                'order' => 1,
                'options' => json_encode([
                    'min' => 1,
                    'max' => 5,
                    'min_label' => 'Sangat Tidak Puas',
                    'max_label' => 'Sangat Puas'
                ]),
            ],
            [
                'question_text' => 'Layanan akademik apa yang paling sering Anda gunakan?',
                'question_type' => 'radio',
                'is_required' => true,
                'order' => 2,
                'options' => json_encode([
                    'Layanan Kemahasiswaan',
                    'Perpustakaan',
                    'Sistem Informasi Akademik',
                    'Bimbingan Akademik',
                    'Laboratorium'
                ]),
            ],
            [
                'question_text' => 'Apakah fasilitas kampus sudah memadai untuk mendukung pembelajaran?',
                'question_type' => 'radio',
                'is_required' => true,
                'order' => 3,
                'options' => json_encode([
                    'Ya',
                    'Tidak'
                ]),
            ],
            [
                'question_text' => 'Berikan saran untuk peningkatan layanan akademik',
                'question_type' => 'textarea',
                'is_required' => false,
                'order' => 4,
                'options' => json_encode([
                    'placeholder' => 'Tuliskan saran Anda di sini...',
                    'max_length' => 500
                ]),
            ],
        ];

        foreach ($questions1 as $question) {
            SurveyQuestion::create(array_merge(['survey_id' => $survey1->id], $question));
        }

        // Survey 2: Evaluasi Kinerja Dosen
        $survey2 = Survey::create([
            'survey_code' => "SURV-{$year}-002",
            'title' => 'Evaluasi Kinerja Dosen Semester Genap 2024/2025',
            'description' => 'Evaluasi ini bertujuan untuk menilai kinerja dosen dalam proses pembelajaran selama semester genap tahun akademik 2024/2025.',
            'created_by' => $user->id,
            'type' => 'internal',
            'status' => 'published',
            'is_anonymous' => true,
            'allow_multiple_responses' => false,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(25),
        ]);

        // Questions for Survey 2
        $questions2 = [
            [
                'question_text' => 'Dosen menjelaskan materi dengan jelas dan mudah dipahami',
                'question_type' => 'rating',
                'is_required' => true,
                'order' => 1,
                'options' => json_encode([
                    'min' => 1,
                    'max' => 5,
                    'labels' => [
                        '1' => 'Sangat Tidak Setuju',
                        '2' => 'Tidak Setuju',
                        '3' => 'Netral',
                        '4' => 'Setuju',
                        '5' => 'Sangat Setuju'
                    ]
                ]),
            ],
            [
                'question_text' => 'Dosen memberikan umpan balik yang konstruktif terhadap tugas mahasiswa',
                'question_type' => 'rating',
                'is_required' => true,
                'order' => 2,
                'options' => json_encode([
                    'min' => 1,
                    'max' => 5,
                    'labels' => [
                        '1' => 'Sangat Tidak Setuju',
                        '2' => 'Tidak Setuju',
                        '3' => 'Netral',
                        '4' => 'Setuju',
                        '5' => 'Sangat Setuju'
                    ]
                ]),
            ],
            [
                'question_text' => 'Dosen hadir tepat waktu dan tidak pernah meninggalkan kelas tanpa pemberitahuan',
                'question_type' => 'rating',
                'is_required' => true,
                'order' => 3,
                'options' => json_encode([
                    'min' => 1,
                    'max' => 5,
                    'labels' => [
                        '1' => 'Sangat Tidak Setuju',
                        '2' => 'Tidak Setuju',
                        '3' => 'Netral',
                        '4' => 'Setuju',
                        '5' => 'Sangat Setuju'
                    ]
                ]),
            ],
            [
                'question_text' => 'Aspek apa yang perlu ditingkatkan dari dosen? (Pilih semua yang sesuai)',
                'question_type' => 'checkbox',
                'is_required' => false,
                'order' => 4,
                'options' => json_encode([
                    'Metode pengajaran',
                    'Penggunaan teknologi dalam pembelajaran',
                    'Ketepatan waktu',
                    'Komunikasi dengan mahasiswa',
                    'Penilaian yang adil'
                ]),
            ],
        ];

        foreach ($questions2 as $question) {
            SurveyQuestion::create(array_merge(['survey_id' => $survey2->id], $question));
        }

        // Survey 3: Kepuasan Fasilitas Kampus (Draft)
        $survey3 = Survey::create([
            'survey_code' => "SURV-{$year}-003",
            'title' => 'Survei Kepuasan Fasilitas Kampus',
            'description' => 'Kuesioner untuk mengevaluasi kepuasan mahasiswa terhadap fasilitas yang tersedia di kampus.',
            'created_by' => $user->id,
            'type' => 'public',
            'status' => 'draft',
            'is_anonymous' => false,
            'allow_multiple_responses' => false,
        ]);

        // Questions for Survey 3
        $questions3 = [
            [
                'question_text' => 'Fasilitas mana yang paling perlu diperbaiki?',
                'question_type' => 'dropdown',
                'is_required' => true,
                'order' => 1,
                'options' => json_encode([
                    'Ruang Kelas',
                    'Perpustakaan',
                    'Laboratorium',
                    'Kantin',
                    'Area Parkir',
                    'Toilet',
                    'Ruang Olahraga',
                    'WiFi/Internet'
                ]),
            ],
            [
                'question_text' => 'Seberapa puas Anda dengan kebersihan kampus?',
                'question_type' => 'rating',
                'is_required' => true,
                'order' => 2,
                'options' => json_encode([
                    'min' => 1,
                    'max' => 5,
                    'min_label' => 'Sangat Tidak Puas',
                    'max_label' => 'Sangat Puas'
                ]),
            ],
            [
                'question_text' => 'Masukan tambahan terkait fasilitas kampus',
                'question_type' => 'textarea',
                'is_required' => false,
                'order' => 3,
            ],
        ];

        foreach ($questions3 as $question) {
            SurveyQuestion::create(array_merge(['survey_id' => $survey3->id], $question));
        }

        $this->command->info('Survey seeder completed successfully!');
        $this->command->info('Created 3 surveys with questions:');
        $this->command->info('- Kepuasan Layanan Akademik (Published)');
        $this->command->info('- Evaluasi Kinerja Dosen (Published, Anonymous)');
        $this->command->info('- Kepuasan Fasilitas Kampus (Draft)');
    }
}

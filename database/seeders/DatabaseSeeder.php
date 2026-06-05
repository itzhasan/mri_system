<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\MriScan;
use App\Models\MriImage;
use App\Models\Report;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    protected const PATIENT_COUNT = 70;

    /** Sample images already present in storage/app/public/mri-images. */
    protected array $pngPool = [];
    protected array $dcmPool = [];

    protected int $scanSeq = 0;
    protected int $reportSeq = 0;

    /** @var User[] */
    protected array $doctors = [];
    /** @var User[] */
    protected array $techs = [];

    public function run(): void
    {
        $this->wipe();
        $this->discoverSampleImages();
        $this->createStaff();

        for ($i = 1; $i <= self::PATIENT_COUNT; $i++) {
            $this->makePatient($i);
        }

        $this->summary();
    }

    // -----------------------------------------------------------------
    // Staff
    // -----------------------------------------------------------------
    protected function createStaff(): void
    {
        User::create([
            'name' => 'Admin User', 'email' => 'admin@mri.com',
            'password' => Hash::make('password'), 'role' => 'admin',
            'phone' => '+964 770 000 0000', 'is_active' => true, 'email_verified_at' => now(),
        ]);

        $this->doctors[] = User::create([
            'name' => 'Dr. Ahmed Ali', 'email' => 'doctor1@mri.com',
            'password' => Hash::make('password'), 'role' => 'doctor',
            'phone' => '+964 770 111 1111', 'specialization' => 'Radiology',
            'license_number' => 'RAD-001', 'is_active' => true, 'email_verified_at' => now(),
        ]);
        $this->doctors[] = User::create([
            'name' => 'Dr. Sara Mohammed', 'email' => 'doctor2@mri.com',
            'password' => Hash::make('password'), 'role' => 'doctor',
            'phone' => '+964 770 222 2222', 'specialization' => 'Neuroradiology',
            'license_number' => 'RAD-002', 'is_active' => true, 'email_verified_at' => now(),
        ]);
        $this->doctors[] = User::create([
            'name' => 'Dr. Layla Hassan', 'email' => 'doctor3@mri.com',
            'password' => Hash::make('password'), 'role' => 'doctor',
            'phone' => '+964 770 333 3333', 'specialization' => 'Musculoskeletal Radiology',
            'license_number' => 'RAD-003', 'is_active' => true, 'email_verified_at' => now(),
        ]);

        $this->techs[] = User::create([
            'name' => 'Omar Hassan', 'email' => 'tech1@mri.com',
            'password' => Hash::make('password'), 'role' => 'mri_technician',
            'phone' => '+964 770 444 4444', 'is_active' => true, 'email_verified_at' => now(),
        ]);
        $this->techs[] = User::create([
            'name' => 'Fatima Zahra', 'email' => 'tech2@mri.com',
            'password' => Hash::make('password'), 'role' => 'mri_technician',
            'phone' => '+964 770 555 5555', 'is_active' => true, 'email_verified_at' => now(),
        ]);
    }

    // -----------------------------------------------------------------
    // Patient + their medical history (scans, images, reports)
    // -----------------------------------------------------------------
    protected function makePatient(int $i): void
    {
        $isMale = (bool) rand(0, 1);
        $first = $isMale ? $this->pick(self::MALE_NAMES) : $this->pick(self::FEMALE_NAMES);
        $last = $this->pick(self::LAST_NAMES);

        $dob = now()->subYears(rand(8, 80))->subDays(rand(0, 364))->format('Y-m-d');

        $patient = Patient::create([
            'patient_number' => 'P2025' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
            'first_name' => $first,
            'last_name' => $last,
            'date_of_birth' => $dob,
            'gender' => $isMale ? 'male' : 'female',
            'phone' => '+964 7' . rand(50, 79) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
            'email' => strtolower($first . '.' . $last . $i) . '@email.com',
            'address' => rand(1, 999) . ' ' . $this->pick(self::DISTRICTS) . ', ' . $this->pick(self::CITIES),
            'emergency_contact_name' => $this->pick(self::MALE_NAMES) . ' ' . $last,
            'emergency_contact_phone' => '+964 7' . rand(50, 79) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
            'medical_history' => $this->medicalHistory(),
            'allergies' => $this->pick(self::ALLERGIES),
        ]);

        // 1–4 scans over the past ~3 years → the patient's imaging history.
        $numScans = $this->weighted([1 => 35, 2 => 35, 3 => 20, 4 => 10]);
        for ($n = 0; $n < $numScans; $n++) {
            $this->makeScanWithHistory($patient, rand(5, 1000));
        }
    }

    protected function makeScanWithHistory(Patient $patient, int $daysAgo): void
    {
        $region = $this->pick(array_keys(self::REGIONS));
        $cfg = self::REGIONS[$region];

        $doctor = $this->pick($this->doctors);
        $tech = $this->pick($this->techs);
        $referrer = $this->pick($this->doctors);
        $scanDate = now()->subDays($daysAgo)->setTime(rand(8, 17), [0, 15, 30, 45][rand(0, 3)]);

        $contrast = (bool) rand(0, 1);
        $willReport = rand(1, 100) <= 78; // most scans are reported

        $this->scanSeq++;
        $scan = MriScan::create([
            'scan_number' => 'MRI2025' . str_pad((string) $this->scanSeq, 4, '0', STR_PAD_LEFT),
            'patient_id' => $patient->id,
            'mri_technician_id' => $tech->id,
            'assigned_doctor_id' => $doctor->id,
            'referring_doctor_id' => $referrer->id,
            'body_part' => $region,
            'scan_type' => $this->pick($cfg['types']),
            'clinical_indication' => $this->pick($cfg['indications']),
            'scan_date' => $scanDate,
            'status' => $willReport ? 'reported' : $this->pick(['pending', 'in_progress', 'completed']),
            'technician_notes' => $this->pick(self::TECH_NOTES),
            'priority' => $this->weighted(['routine' => 70, 'urgent' => 22, 'stat' => 8]),
            'contrast_used' => $contrast,
            'contrast_agent' => $contrast ? $this->pick(['Gadolinium', 'Gadobutrol', 'Gadoxetate']) : null,
            'number_of_images' => 0,
        ]);

        // Always attach a few images, including at least one DICOM.
        foreach ($this->imagePattern() as $slice => $kind) {
            $this->attachImage($scan, $kind, $scan->scan_type, $slice + 1);
        }
        $scan->update(['number_of_images' => $scan->images()->count()]);

        if ($willReport) {
            $this->report($scan, $doctor, $cfg, $scanDate);
        }
    }

    protected function attachImage(MriScan $scan, string $kind, string $seqLabel, int $slice): void
    {
        $pool = $kind === 'dcm' ? $this->dcmPool : $this->pngPool;
        if (empty($pool)) {
            return;
        }

        // Reference an existing sample file directly (no per-scan copy → no disk bloat).
        $source = $pool[array_rand($pool)];
        $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));

        MriImage::create([
            'mri_scan_id' => $scan->id,
            'file_name' => "{$scan->scan_number}_{$seqLabel}_{$slice}.{$ext}",
            'file_path' => $source,
            'file_type' => $ext,
            'file_size' => Storage::disk('public')->size($source),
            'sequence_name' => $seqLabel,
            'slice_number' => $slice,
        ]);
    }

    protected function report(MriScan $scan, User $doctor, array $cfg, $scanDate): void
    {
        $this->reportSeq++;
        $reportedAt = (clone $scanDate)->addHours(rand(2, 48));

        $report = Report::create([
            'report_number' => 'REP2025' . str_pad((string) $this->reportSeq, 4, '0', STR_PAD_LEFT),
            'mri_scan_id' => $scan->id,
            'doctor_id' => $doctor->id,
            'findings' => $this->pick($cfg['findings']),
            'impression' => $this->pick($cfg['impressions']),
            'recommendations' => $this->pick($cfg['recommendations']),
            'status' => $this->weighted(['final' => 80, 'draft' => 12, 'amended' => 8]),
            'reported_at' => $reportedAt,
            'finalized_at' => $reportedAt,
        ]);

        Notification::create([
            'user_id' => $scan->mri_technician_id,
            'mri_scan_id' => $scan->id,
            'report_id' => $report->id,
            'title' => 'Report completed',
            'message' => "Report {$report->report_number} for scan {$scan->scan_number} is ready.",
            'type' => 'report_ready',
        ]);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------
    protected function wipe(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['notifications', 'reports', 'mri_images', 'mri_scans', 'patients', 'messages', 'users'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();

        Storage::disk('public')->deleteDirectory('mri-images/seed');
    }

    protected function discoverSampleImages(): void
    {
        foreach (Storage::disk('public')->files('mri-images') as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $this->pngPool[] = $file;
            } elseif (in_array($ext, ['dcm', 'dicom'])) {
                $this->dcmPool[] = $file;
            }
        }
    }

    /** Image layouts — every scan gets at least one DICOM. */
    protected function imagePattern(): array
    {
        return $this->pick([
            ['png', 'dcm'],
            ['png', 'png', 'dcm'],
            ['dcm', 'png'],
            ['png', 'dcm', 'dcm'],
            ['dcm'],
        ]);
    }

    protected function medicalHistory(): string
    {
        $lines = [];
        $chronic = (array) array_rand(array_flip(self::CONDITIONS), rand(1, 2));
        foreach ($chronic as $c) {
            $lines[] = $c;
        }
        $lines[] = 'Presenting complaint: ' . $this->pick(self::COMPLAINTS) . '.';
        if (rand(0, 1)) {
            $lines[] = 'Past surgical history: ' . $this->pick(self::SURGERIES) . '.';
        }
        return implode("\n", $lines);
    }

    protected function pick(array $arr)
    {
        return $arr[array_rand($arr)];
    }

    /** Pick a key from [value => weight]. */
    protected function weighted(array $weights)
    {
        $total = array_sum($weights);
        $r = rand(1, $total);
        foreach ($weights as $key => $w) {
            if (($r -= $w) <= 0) {
                return $key;
            }
        }
        return array_key_first($weights);
    }

    protected function summary(): void
    {
        $this->command->info('✅ Demo data seeded.');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Users', User::count()],
                ['Patients', Patient::count()],
                ['MRI Scans', MriScan::count()],
                ['Images', MriImage::count()],
                ['  └ DICOM', MriImage::where('file_type', 'dcm')->count()],
                ['Reports', Report::count()],
            ]
        );
        $this->command->info('Login (all passwords: "password"):');
        $this->command->info('  Admin       → admin@mri.com');
        $this->command->info('  Radiologist → doctor1@mri.com / doctor2@mri.com / doctor3@mri.com');
        $this->command->info('  Technician  → tech1@mri.com / tech2@mri.com');
    }

    // -----------------------------------------------------------------
    // Content pools
    // -----------------------------------------------------------------
    protected const MALE_NAMES = ['Ali', 'Hassan', 'Omar', 'Yusuf', 'Mustafa', 'Karim', 'Ahmed', 'Ibrahim', 'Mohammed', 'Tariq', 'Saad', 'Hayder', 'Mahmoud', 'Bilal', 'Ziad', 'Anas', 'Khalid', 'Salah', 'Waleed', 'Nabil'];
    protected const FEMALE_NAMES = ['Noor', 'Sara', 'Zainab', 'Fatima', 'Mariam', 'Layla', 'Hadeel', 'Rana', 'Dina', 'Aya', 'Huda', 'Shaimaa', 'Israa', 'Ruqaya', 'Bushra', 'Maha', 'Nada', 'Reem', 'Suha', 'Yasmin'];
    protected const LAST_NAMES = ['Karim', 'Ahmed', 'Ibrahim', 'Salim', 'Abdullah', 'Tariq', 'Hassan', 'Ali', 'Mohammed', 'Jabbar', 'Saleh', 'Khalil', 'Hamdani', 'Obeidi', 'Janabi', 'Dulaimi', 'Azzawi', 'Rikabi', 'Maliki', 'Shammari'];
    protected const CITIES = ['Baghdad', 'Basra', 'Mosul', 'Erbil', 'Najaf', 'Karbala', 'Kirkuk'];
    protected const DISTRICTS = ['Al-Rasheed St', 'Palestine St', 'Karrada', 'Mansour', 'Zayouna', 'Jadriya', 'Al-Adhamiya', 'Al-Saidiya', 'Al-Dora', 'Al-Kadhimiya'];

    protected const ALLERGIES = ['None known', 'None known', 'None known', 'Penicillin', 'Iodinated contrast (mild)', 'Aspirin', 'Sulfa drugs', 'Latex', 'Shellfish'];
    protected const CONDITIONS = ['Hypertension (controlled).', 'Type 2 Diabetes Mellitus.', 'Asthma.', 'Hypothyroidism.', 'Chronic migraine.', 'Osteoarthritis.', 'Hyperlipidemia.', 'No significant past medical history.', 'Previous smoker.', 'Epilepsy (on medication).'];
    protected const COMPLAINTS = ['recurrent headaches', 'lower back pain radiating to the leg', 'knee pain and swelling', 'shoulder pain after injury', 'abdominal pain', 'dizziness and visual disturbance', 'neck pain and stiffness', 'numbness in the extremities', 'chronic joint pain'];
    protected const SURGERIES = ['appendectomy (2015)', 'cholecystectomy (2018)', 'C-section (2019)', 'knee arthroscopy (2020)', 'none'];
    protected const TECH_NOTES = ['Patient cooperative. No motion artifact.', 'Mild motion artifact on early sequences.', 'Patient anxious; sedation not required.', 'Contrast administered without complication.', 'Standard protocol completed.', ''];

    protected const REGIONS = [
        'brain' => [
            'types' => ['T1 Weighted (Axial)', 'T2 / FLAIR (Axial)', 'DWI', 'MRA Circle of Willis'],
            'indications' => ['Recurrent headaches, rule out intracranial pathology.', 'New onset seizures.', 'Dizziness and visual disturbance.', 'Screen for intracranial aneurysm.'],
            'findings' => [
                'The brain parenchyma demonstrates normal signal intensity. No mass lesion, midline shift, or abnormal enhancement. Ventricles are normal in size.',
                'A few scattered T2/FLAIR hyperintensities in the subcortical white matter, nonspecific and likely related to small vessel disease.',
                'No restricted diffusion to suggest acute infarction. No intracranial hemorrhage.',
            ],
            'impressions' => ['Normal brain MRI.', 'Mild chronic small vessel ischemic changes.', 'No acute intracranial abnormality.'],
            'recommendations' => ['Clinical correlation advised.', 'Neurology follow-up if symptoms persist.', 'No further imaging required at this time.'],
        ],
        'spine' => [
            'types' => ['T2 Sagittal', 'T1 Axial', 'STIR Sagittal'],
            'indications' => ['Chronic low back pain with radiculopathy.', 'Neck pain and stiffness.', 'Lower limb numbness.'],
            'findings' => [
                'Left paracentral disc herniation at L4-L5 causing moderate compression of the traversing nerve root. Mild degenerative disc disease at L5-S1.',
                'Multilevel degenerative changes with mild canal stenosis. Vertebral body heights are preserved.',
                'Straightening of the cervical lordosis. No significant disc herniation or cord signal abnormality.',
            ],
            'impressions' => ['L4-L5 disc herniation with nerve root compression.', 'Multilevel degenerative disc disease.', 'Muscular spasm; no acute osseous abnormality.'],
            'recommendations' => ['Neurosurgical consultation.', 'Consider physical therapy and analgesia.', 'Epidural steroid injection if conservative therapy fails.'],
        ],
        'knee' => [
            'types' => ['PD Fat-Sat (Coronal)', 'T2 Sagittal', 'T1 Axial'],
            'indications' => ['Suspected meniscal tear.', 'Knee pain and locking.', 'Post-traumatic evaluation.'],
            'findings' => [
                'Horizontal tear of the posterior horn of the medial meniscus. Moderate joint effusion. Cruciate and collateral ligaments are intact.',
                'Tricompartmental cartilage thinning consistent with osteoarthritis. Small Baker cyst.',
                'Complete tear of the anterior cruciate ligament with associated bone bruising of the lateral femoral condyle.',
            ],
            'impressions' => ['Medial meniscus tear with joint effusion.', 'Moderate osteoarthritis.', 'ACL tear.'],
            'recommendations' => ['Orthopedic referral.', 'Arthroscopy may be considered.', 'Physiotherapy and activity modification.'],
        ],
        'shoulder' => [
            'types' => ['T1 Fat-Sat (Axial)', 'T2 (Coronal Oblique)', 'PD Sagittal'],
            'indications' => ['Recurrent instability after dislocation.', 'Rotator cuff injury.', 'Chronic shoulder pain.'],
            'findings' => [
                'Full-thickness tear of the supraspinatus tendon with mild retraction. Subacromial-subdeltoid bursal fluid.',
                'Anteroinferior labral tear (Bankart lesion) consistent with prior dislocation.',
                'Tendinosis of the rotator cuff without a discrete tear. Mild AC joint osteoarthritis.',
            ],
            'impressions' => ['Supraspinatus full-thickness tear.', 'Bankart lesion.', 'Rotator cuff tendinosis.'],
            'recommendations' => ['Orthopedic / sports medicine referral.', 'Consider surgical repair.', 'Conservative management and physiotherapy.'],
        ],
        'abdomen' => [
            'types' => ['MRCP', 'T2 HASTE (Axial)', 'Dynamic post-contrast'],
            'indications' => ['Evaluate biliary tree for stones.', 'Right upper quadrant pain.', 'Characterize liver lesion.'],
            'findings' => [
                'A calculus in the distal common bile duct with mild upstream biliary dilatation. The gallbladder contains multiple small stones.',
                'A well-defined T2 hyperintense lesion in the right hepatic lobe consistent with a simple cyst.',
                'Liver, spleen, pancreas and kidneys are unremarkable. No free fluid.',
            ],
            'impressions' => ['Choledocholithiasis with cholelithiasis.', 'Simple hepatic cyst.', 'Unremarkable abdominal MRI.'],
            'recommendations' => ['Refer for ERCP.', 'Routine follow-up.', 'Surgical consultation for cholecystectomy.'],
        ],
        'pelvis' => [
            'types' => ['T2 Axial', 'T1 Post-contrast', 'DWI'],
            'indications' => ['Pelvic pain.', 'Characterize adnexal mass.', 'Staging evaluation.'],
            'findings' => [
                'A well-circumscribed uterine fibroid measuring 4 cm. No suspicious adnexal lesion.',
                'Normal pelvic organs. No free fluid or lymphadenopathy.',
                'A complex cystic adnexal lesion warranting further characterization.',
            ],
            'impressions' => ['Uterine fibroid.', 'Normal pelvic MRI.', 'Complex adnexal cyst.'],
            'recommendations' => ['Gynecology referral.', 'Routine follow-up.', 'Short-interval follow-up imaging.'],
        ],
    ];
}

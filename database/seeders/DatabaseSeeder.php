<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\MriScan;
use App\Models\Report;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@mri.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '1234567890',
            'is_active' => true,
        ]);

        // Create Doctors
        $doctor1 = User::create([
            'name' => 'Dr. Ahmed Ali',
            'email' => 'doctor1@mri.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '1234567891',
            'specialization' => 'Radiology',
            'license_number' => 'DOC001',
            'is_active' => true,
        ]);

        $doctor2 = User::create([
            'name' => 'Dr. Sara Mohammed',
            'email' => 'doctor2@mri.com',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'phone' => '1234567892',
            'specialization' => 'Neurology',
            'license_number' => 'DOC002',
            'is_active' => true,
        ]);

        // Create MRI Technicians
        $tech1 = User::create([
            'name' => 'Omar Hassan',
            'email' => 'tech1@mri.com',
            'password' => Hash::make('password'),
            'role' => 'mri_technician',
            'phone' => '1234567893',
            'is_active' => true,
        ]);

        $tech2 = User::create([
            'name' => 'Fatima Zahra',
            'email' => 'tech2@mri.com',
            'password' => Hash::make('password'),
            'role' => 'mri_technician',
            'phone' => '1234567894',
            'is_active' => true,
        ]);

        // Create Patients
        $patient1 = Patient::create([
            'patient_number' => 'P20250001',
            'first_name' => 'Ali',
            'last_name' => 'Karim',
            'date_of_birth' => '1985-05-15',
            'gender' => 'male',
            'phone' => '9876543210',
            'email' => 'ali.karim@email.com',
            'address' => '123 Main Street, Baghdad',
            'medical_history' => 'No significant medical history',
        ]);

        $patient2 = Patient::create([
            'patient_number' => 'P20250002',
            'first_name' => 'Noor',
            'last_name' => 'Ahmed',
            'date_of_birth' => '1990-08-20',
            'gender' => 'female',
            'phone' => '9876543211',
            'email' => 'noor.ahmed@email.com',
            'address' => '456 Oak Avenue, Baghdad',
            'medical_history' => 'Migraine history',
            'allergies' => 'Penicillin',
        ]);

        $patient3 = Patient::create([
            'patient_number' => 'P20250003',
            'first_name' => 'Hassan',
            'last_name' => 'Ibrahim',
            'date_of_birth' => '1978-03-10',
            'gender' => 'male',
            'phone' => '9876543212',
            'email' => 'hassan.ibrahim@email.com',
            'address' => '789 Pine Road, Baghdad',
            'medical_history' => 'Diabetes Type 2',
        ]);

        // Create MRI Scans
        $scan1 = MriScan::create([
            'scan_number' => 'MRI20250001',
            'patient_id' => $patient1->id,
            'mri_technician_id' => $tech1->id,
            'assigned_doctor_id' => $doctor1->id,
            'body_part' => 'brain',
            'scan_type' => 'T1 Weighted',
            'clinical_indication' => 'Headaches and dizziness',
            'scan_date' => now()->subDays(5),
            'status' => 'completed',
            'priority' => 'routine',
            'contrast_used' => false,
            'number_of_images' => 0,
        ]);

        $scan2 = MriScan::create([
            'scan_number' => 'MRI20250002',
            'patient_id' => $patient2->id,
            'mri_technician_id' => $tech1->id,
            'assigned_doctor_id' => $doctor2->id,
            'body_part' => 'spine',
            'scan_type' => 'T2 Weighted',
            'clinical_indication' => 'Lower back pain',
            'scan_date' => now()->subDays(3),
            'status' => 'reported',
            'priority' => 'urgent',
            'contrast_used' => true,
            'contrast_agent' => 'Gadolinium',
            'number_of_images' => 0,
        ]);

        $scan3 = MriScan::create([
            'scan_number' => 'MRI20250003',
            'patient_id' => $patient3->id,
            'mri_technician_id' => $tech2->id,
            'assigned_doctor_id' => $doctor1->id,
            'body_part' => 'knee',
            'scan_type' => 'T1 and T2',
            'clinical_indication' => 'Knee injury evaluation',
            'scan_date' => now()->subDays(1),
            'status' => 'pending',
            'priority' => 'routine',
            'contrast_used' => false,
            'number_of_images' => 0,
        ]);

        // Create Reports
        Report::create([
            'report_number' => 'REP20250001',
            'mri_scan_id' => $scan2->id,
            'doctor_id' => $doctor2->id,
            'findings' => 'The MRI scan of the lumbar spine shows mild degenerative disc disease at L4-L5 level. No significant nerve compression is observed. The vertebral bodies maintain normal height and alignment.',
            'impression' => 'Mild degenerative disc disease at L4-L5. No acute findings requiring immediate intervention.',
            'recommendations' => 'Conservative management with physical therapy. Follow-up in 3 months if symptoms persist.',
            'status' => 'final',
            'reported_at' => now()->subDays(2),
            'finalized_at' => now()->subDays(2),
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@mri.com / password');
        $this->command->info('Doctor 1: doctor1@mri.com / password');
        $this->command->info('Doctor 2: doctor2@mri.com / password');
        $this->command->info('Technician 1: tech1@mri.com / password');
        $this->command->info('Technician 2: tech2@mri.com / password');
    }
}
//blood type
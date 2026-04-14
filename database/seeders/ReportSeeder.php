<?php
// © Atia Hegazy — atiaeno.com

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Report;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $links = Link::all();
        $reasons = ['spam', 'phishing', 'malware', 'violence', 'other'];

        // Create 15 reports on random links
        for ($i = 0; $i < 15; $i++) {
            $link = $links->random();
            $reason = $reasons[array_rand($reasons)];

            Report::create([
                'link_id' => $link->id,
                'reporter_ip_hash' => hash('sha256', '192.168.1.' . rand(1, 255)),
                'reason' => $reason,
                'details' => 'This link appears to contain ' . $reason . ' content. Please review.',
                'status' => 'pending',
            ]);

            $link->increment('report_count');
        }

        // Create a heavily reported link (auto-suspended candidate)
        $problemLink = $links->first();
        for ($i = 0; $i < 5; $i++) {
            Report::create([
                'link_id' => $problemLink->id,
                'reporter_ip_hash' => hash('sha256', '10.0.0.' . ($i + 1)),
                'reason' => 'phishing',
                'details' => 'Confirmed phishing attempt - fake login page',
                'status' => 'pending',
            ]);
        }
        $problemLink->update([
            'report_count' => 5,
            'auto_suspended_at' => now(),
            'is_active' => false,
        ]);

        // Create some reviewed reports
        for ($i = 0; $i < 5; $i++) {
            $link = $links->random();
            $report = Report::create([
                'link_id' => $link->id,
                'reporter_ip_hash' => hash('sha256', '172.16.0.' . rand(1, 255)),
                'reason' => 'spam',
                'details' => 'Spam content',
                'status' => ['dismissed', 'actioned'][array_rand(['dismissed', 'actioned'])],
                'reviewed_by' => 1, // Admin
                'reviewed_at' => now()->subDays(rand(1, 7)),
                'admin_notes' => 'Reviewed and ' . ($report->status ?? 'resolved') . ' by admin.',
            ]);
        }

        // Add activity log entries
        ActivityLog::create([
            'actor_id' => 1,
            'action' => 'report_reviewed',
            'target_type' => 'report',
            'target_id' => 1,
            'metadata' => ['link_id' => $problemLink->id, 'action_taken' => 'deactivate'],
        ]);
    }
}

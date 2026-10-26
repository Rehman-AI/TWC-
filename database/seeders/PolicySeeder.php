<?php

namespace Database\Seeders;

use App\Models\PolicyRule;
use Illuminate\Database\Seeder;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'code'        => 'LICENSED_FOOTAGE',
                'category'    => 'legal',
                'title'       => 'All footage must be properly licensed',
                'description' => 'Every stock footage clip must have a valid license (Pexels, Pixabay, or purchased). No unlicensed, ripped, or stolen footage.',
                'severity'    => 'block',
                'sort_order'  => 1,
            ],
            [
                'code'        => 'LICENSED_MUSIC',
                'category'    => 'legal',
                'title'       => 'All music must be royalty-free or licensed',
                'description' => 'Background music must come from our licensed library or be royalty-free. No copyrighted music.',
                'severity'    => 'block',
                'sort_order'  => 2,
            ],
            [
                'code'        => 'NO_COPIED_VIDEO',
                'category'    => 'legal',
                'title'       => 'No copied or re-uploaded videos',
                'description' => 'Content must be original. No re-uploads, mirror copies, or screen recordings of other creators.',
                'severity'    => 'block',
                'sort_order'  => 3,
            ],
            [
                'code'        => 'AI_DISCLOSURE',
                'category'    => 'disclosure',
                'title'       => 'AI disclosure where required',
                'description' => 'If AI-generated clips, voiceover, or substantially altered content is used, apply the AI disclosure label automatically.',
                'severity'    => 'warn',
                'sort_order'  => 4,
            ],
            [
                'code'        => 'BANNED_WORDS',
                'category'    => 'brand',
                'title'       => 'No banned words in script',
                'description' => 'The script must not contain any words from the channel banned words list.',
                'severity'    => 'block',
                'sort_order'  => 5,
            ],
            [
                'code'        => 'SCRIPT_APPROVED',
                'category'    => 'content',
                'title'       => 'Script must be approved',
                'description' => 'At least one script version must be approved by a Manager or Owner before proceeding.',
                'severity'    => 'block',
                'sort_order'  => 6,
            ],
            [
                'code'        => 'NO_MEDICAL_CLAIMS',
                'category'    => 'content',
                'title'       => 'No medical claims',
                'description' => 'Content must not make medical claims, offer diagnoses, or recommend treatments without disclaimers.',
                'severity'    => 'warn',
                'sort_order'  => 7,
            ],
            [
                'code'        => 'NO_FAKE_ENGAGEMENT',
                'category'    => 'platform',
                'title'       => 'No fake engagement',
                'description' => 'Never automate fake engagement, view buying, mass account creation, or spam.',
                'severity'    => 'block',
                'sort_order'  => 8,
            ],
            [
                'code'        => 'NO_PROFIT_PROMISE',
                'category'    => 'content',
                'title'       => 'No guaranteed profit or virality promises',
                'description' => 'Never claim guaranteed views, virality, profit, or medical results in content.',
                'severity'    => 'block',
                'sort_order'  => 9,
            ],
            [
                'code'        => 'DURATION_LIMIT',
                'category'    => 'platform',
                'title'       => 'Duration within Shorts limit',
                'description' => 'Total video duration must not exceed 60 seconds for YouTube Shorts.',
                'severity'    => 'warn',
                'sort_order'  => 10,
            ],
        ];

        foreach ($rules as $rule) {
            PolicyRule::updateOrCreate(
                ['code' => $rule['code']],
                array_merge($rule, [
                    'is_active'        => true,
                    'last_verified_at' => now(),
                    'verified_by'      => 'System',
                ])
            );
        }
    }
}
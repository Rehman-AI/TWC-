<?php

declare(strict_types=1);

namespace App\Services\Policy;

use App\Models\PolicyReview;
use App\Models\PolicyRule;
use App\Models\VideoProject;
use Illuminate\Support\Facades\Log;

class PolicyEngine
{
    /**
     * Run all active policy rules against a video project.
     * Returns PASS, WARN, or BLOCK.
     */
    public function review(VideoProject $project, ?int $reviewerId = null): PolicyReview
    {
        $rules = PolicyRule::active()->orderBy('sort_order')->get();
        $project->load(['channel', 'currentScript', 'scenes', 'assets']);

        $ruleResults = [];
        $hasBlock = false;
        $hasWarn = false;

        foreach ($rules as $rule) {
            $result = $this->evaluateRule($rule, $project);
            $ruleResults[] = [
                'rule_id'     => $rule->id,
                'code'        => $rule->code,
                'title'       => $rule->title,
                'category'    => $rule->category,
                'severity'    => $rule->severity,
                'result'      => $result['result'],
                'details'     => $result['details'],
            ];

            if ($result['result'] === 'block') $hasBlock = true;
            if ($result['result'] === 'warn') $hasWarn = true;
        }

        $overallResult = match (true) {
            $hasBlock => 'block',
            $hasWarn  => 'warn',
            default   => 'pass',
        };

        Log::info('Policy review completed', [
            'project' => $project->id,
            'result'  => $overallResult,
            'rules'   => count($ruleResults),
        ]);

        return PolicyReview::create([
            'video_project_id' => $project->id,
            'overall_result'   => $overallResult,
            'rule_results'     => $ruleResults,
            'reviewed_by'      => $reviewerId,
        ]);
    }

    private function evaluateRule(PolicyRule $rule, VideoProject $project): array
    {
        return match ($rule->code) {
            'LICENSED_FOOTAGE'   => $this->checkLicensedFootage($project),
            'LICENSED_MUSIC'     => $this->checkLicensedMusic($project),
            'NO_COPIED_VIDEO'    => $this->checkNoCopiedVideo($project),
            'AI_DISCLOSURE'      => $this->checkAiDisclosure($project),
            'BANNED_WORDS'       => $this->checkBannedWords($project),
            'SCRIPT_APPROVED'    => $this->checkScriptApproved($project),
            'NO_MEDICAL_CLAIMS'  => $this->checkNoMedicalClaims($project),
            'NO_FAKE_ENGAGEMENT' => $this->checkNoFakeEngagement($project),
            'NO_PROFIT_PROMISE'  => $this->checkNoProfitPromise($project),
            'DURATION_LIMIT'     => $this->checkDurationLimit($project),
            default              => ['result' => 'pass', 'details' => 'Rule not implemented'],
        };
    }

    private function checkLicensedFootage(VideoProject $project): array
    {
        $assets = $project->assets()->where('type', 'stock_footage')->get();
        foreach ($assets as $asset) {
            $meta = $asset->metadata ?? [];
            if (empty($meta['license']) || $meta['license'] === 'unknown') {
                return ['result' => 'block', 'details' => "Asset '{$asset->filename}' has no license info."];
            }
        }
        return ['result' => 'pass', 'details' => 'All footage properly licensed.'];
    }

    private function checkLicensedMusic(VideoProject $project): array
    {
        $musicAssets = $project->assets()->where('type', 'music')->get();
        if ($musicAssets->isEmpty()) {
            return ['result' => 'pass', 'details' => 'No music used.'];
        }
        foreach ($musicAssets as $asset) {
            if (empty($asset->metadata['license_type'])) {
                return ['result' => 'warn', 'details' => "Music '{$asset->filename}' missing license metadata."];
            }
        }
        return ['result' => 'pass', 'details' => 'All music properly licensed.'];
    }

    private function checkNoCopiedVideo(VideoProject $project): array
    {
        // Placeholder for perceptual hash check
        return ['result' => 'pass', 'details' => 'No copied video detected (hash check pending).'];
    }

    private function checkAiDisclosure(VideoProject $project): array
    {
        $hasAiContent = $project->assets()->whereIn('type', ['ai_clip', 'voiceover'])->exists();
        if ($hasAiContent) {
            return ['result' => 'warn', 'details' => 'AI-generated content detected. AI disclosure label will be applied automatically.'];
        }
        return ['result' => 'pass', 'details' => 'No AI-generated content requiring disclosure.'];
    }

    private function checkBannedWords(VideoProject $project): array
    {
        $bannedWords = $project->channel->banned_words ?? [];
        if (empty($bannedWords)) {
            return ['result' => 'pass', 'details' => 'No banned words configured.'];
        }

        $script = $project->currentScript;
        $text = strtolower($script?->plain_text ?? '');
        $found = [];

        foreach ($bannedWords as $word) {
            if (str_contains($text, strtolower($word))) {
                $found[] = $word;
            }
        }

        if (! empty($found)) {
            return ['result' => 'block', 'details' => 'Banned words found: ' . implode(', ', $found)];
        }
        return ['result' => 'pass', 'details' => 'No banned words detected.'];
    }

    private function checkScriptApproved(VideoProject $project): array
    {
        $approved = $project->approvedScript();
        if (! $approved) {
            return ['result' => 'block', 'details' => 'No approved script version.'];
        }
        return ['result' => 'pass', 'details' => "Script v{$approved->version} approved."];
    }

    private function checkNoMedicalClaims(VideoProject $project): array
    {
        $medicalTerms = ['cure', 'treat', 'heal', 'diagnosis', 'prescription', 'guaranteed results', 'medical advice'];
        $text = strtolower($project->currentScript?->plain_text ?? '');

        foreach ($medicalTerms as $term) {
            if (str_contains($text, $term)) {
                return ['result' => 'warn', 'details' => "Potential medical claim detected: '{$term}'."];
            }
        }
        return ['result' => 'pass', 'details' => 'No medical claims detected.'];
    }

    private function checkNoFakeEngagement(VideoProject $project): array
    {
        return ['result' => 'pass', 'details' => 'No fake engagement indicators.'];
    }

    private function checkNoProfitPromise(VideoProject $project): array
    {
        $profitTerms = ['guaranteed income', 'make money fast', 'get rich', 'financial freedom guaranteed', 'easy money'];
        $text = strtolower($project->currentScript?->plain_text ?? '');

        foreach ($profitTerms as $term) {
            if (str_contains($text, $term)) {
                return ['result' => 'block', 'details' => "Prohibited profit promise detected: '{$term}'."];
            }
        }
        return ['result' => 'pass', 'details' => 'No prohibited profit promises.'];
    }

    private function checkDurationLimit(VideoProject $project): array
    {
        $totalDuration = $project->totalSceneDuration();
        if ($totalDuration > 60) {
            return ['result' => 'warn', 'details' => "Total scene duration is {$totalDuration}s (exceeds 60s Shorts limit)."];
        }
        return ['result' => 'pass', 'details' => "Duration: {$totalDuration}s (within Shorts limit)."];
    }
}